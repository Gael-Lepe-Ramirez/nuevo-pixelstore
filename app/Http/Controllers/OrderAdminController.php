<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Administración de PEDIDO: consulta y cambios de estado (solo admin). Las reglas viven en App\Models\Order (RN8).
class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'paymentMethod', 'shippingMethod'])
            ->when(in_array($request->query('status'), Order::STATUSES, true), fn ($q) => $q->where('status', $request->query('status')))
            ->when(in_array($request->query('payment'), Order::PAYMENT_STATUSES, true), fn ($q) => $q->where('payment_status', $request->query('payment')))
            ->latest('id')
            ->get();

        return view('orders_admin.index', [
            'orders' => $orders,
            'status' => $request->query('status'),
            'payment' => $request->query('payment'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'shippingMethod', 'paymentMethod', 'products.brand']);

        return view('orders_admin.show', compact('order'));
    }

    public function transition(Order $order, string $action)
    {
        $messages = [
            'pay' => ['markAsPaid', 'Pago confirmado. El pedido ya puede enviarse.'],
            'reject-payment' => ['rejectPayment', 'Pago rechazado: el pedido se canceló y las unidades regresaron al inventario.'],
            'ship' => ['ship', 'Pedido marcado como enviado.'],
            'deliver' => ['deliver', 'Pedido marcado como entregado.'],
            'cancel' => ['cancel', 'Pedido cancelado y unidades devueltas al inventario.'],
        ];

        abort_unless(isset($messages[$action]), 404);

        [$method, $success] = $messages[$action];

        try {
            $order->{$method}();
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)->with('success', $success);
    }
}
