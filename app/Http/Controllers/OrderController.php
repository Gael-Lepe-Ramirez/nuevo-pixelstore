<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderReceipt;

class OrderController extends Controller
{
    // Muestra la vista del carrito
    public function cart()
    {
        $cart = session()->get('cart', []);
        $shippingMethods = ShippingMethod::orderBy('cost')->get();
        $paymentMethods = PaymentMethod::orderBy('name')->get();
        return view('orders.cart', compact('cart', 'shippingMethods', 'paymentMethods'));
    }

    // Agrega un producto al carrito en la sesión
    public function add(Request $request, Product $product)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "image" => $product->image ? $product->image->url : null
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back();
    }

    // Quita un producto del carrito
    public function remove($id)
    {
        $cart = session()->get('cart');
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }
        return redirect()->back();
    }

    // Procesa la compra y la guarda en la Base de Datos (relaciones 1:N y N:M del modelo)
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('products.index');
        }

        $data = $request->validate([
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shipping_address' => 'required|string|max:255',
        ], [
            'shipping_method_id.required' => 'Elige un método de envío.',
            'payment_method_id.required' => 'Elige un método de pago.',
            'shipping_address.required' => 'Escribe la dirección de entrega.',
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $data) {
                // Se bloquean los productos para que dos compras simultáneas no vendan la misma existencia (RN2).
                $products = Product::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');

                $subtotal = 0;
                foreach ($cart as $id => $details) {
                    $product = $products->get($id);
                    $quantity = (int) $details['quantity'];

                    if (! $product || ! $product->is_active) {
                        throw new \RuntimeException('Un producto de tu carrito ya no está disponible.');
                    }
                    if ($quantity < 1 || $quantity > $product->stock) {
                        throw new \RuntimeException("No hay existencias suficientes de «{$product->name}» (disponibles: {$product->stock}).");
                    }

                    // El precio se toma de la base de datos, no de la sesión (RN3).
                    $subtotal += $product->price * $quantity;
                }

                $shipping = ShippingMethod::findOrFail($data['shipping_method_id']);

                // 1. PEDIDO (relaciones «realiza», «usa» y «paga»); la dirección se copia al pedido (RN7).
                $order = auth()->user()->orders()->create([
                    'shipping_method_id' => $shipping->id,
                    'payment_method_id' => $data['payment_method_id'],
                    'status' => 'pendiente',
                    'payment_status' => 'pendiente',
                    'shipping_address' => $data['shipping_address'],
                    'total_amount' => $subtotal + $shipping->cost, // RN4
                ]);

                // 2. DETALLE_PEDIDO (relación «incluye») y salida de inventario (RN2)
                foreach ($cart as $id => $details) {
                    $product = $products->get($id);
                    $order->products()->attach($id, [
                        'quantity' => $details['quantity'],
                        'unit_price' => $product->price,
                    ]);
                    $product->decrement('stock', $details['quantity']);
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->withInput()->with('error', $e->getMessage());
        }

        // Si el cliente aún no tenía dirección guardada en su perfil, se conserva la de esta compra.
        if (! auth()->user()->shipping_address) {
            auth()->user()->update(['shipping_address' => $data['shipping_address']]);
        }

        // 3. Enviar el recibo por correo electrónico
        Mail::to(auth()->user()->email)->send(new OrderReceipt($order));

        // 4. Vaciar el carrito
        session()->forget('cart');

        // Redirigir a la pantalla de agradecimiento
        return redirect()->route('orders.success', $order->id);
    }

    public function downloadPDF(\App\Models\Order $order)
    {
        // Seguridad: Verificar que el usuario sea el dueño de la compra o el admin
        if ($order->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para ver este recibo.');
        }

        // Cargar la vista del PDF con los datos de la orden
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf', compact('order'));
        
        // Retornar el archivo para su descarga directa
        return $pdf->download('recibo_pixelstore_' . $order->id . '.pdf');
    }

    public function success(\App\Models\Order $order)
    {
        if ($order->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Acceso denegado.');
        }

        return view('orders.success', compact('order'));
    }
}