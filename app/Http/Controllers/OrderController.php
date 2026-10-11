<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderReceipt;

class OrderController extends Controller
{
    // Muestra la vista del carrito
    public function cart()
    {
        $cart = session()->get('cart', []);
        return view('orders.cart', compact('cart'));
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

    // Procesa la compra y la guarda en la Base de Datos (Relación M:N)
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('products.index');
        }

        // Calcular el total
        $total = 0;
        foreach ($cart as $details) {
            $total += $details['price'] * $details['quantity'];
        }

        // 1. Crear la Orden (Relación 1:M con el Usuario)
        $order = auth()->user()->orders()->create([
            'total' => $total,
            'status' => 'Completada'
        ]);

        // 2. Asociar los productos a la orden (Relación M:N)
        foreach ($cart as $id => $details) {
            $order->products()->attach($id, [
                'quantity' => $details['quantity'],
                'price' => $details['price'],
                'unit_price' => $details['price']
            ]);
            
            // Opcional: Descontar del stock real
            $product = Product::find($id);
            $product->decrement('stock', $details['quantity']);
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