<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

// Administración de ORDEN_COMPRA y DETALLE_ORDEN_COMPRA (solo admin).
// RN1: las existencias solo aumentan cuando una orden se marca como «recibida».
class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'products'])->latest('id')->get();

        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        return view('purchase_orders.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::with('brand')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'ordered_at' => ['required', 'date', 'before_or_equal:today'],
            'lines' => ['required', 'array', 'min:1'],              // RN5: al menos un renglón
            'lines.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ], [
            'lines.required' => 'Agrega al menos un producto a la orden.',
            'lines.min' => 'Agrega al menos un producto a la orden.',
            'lines.*.product_id.required' => 'Selecciona un producto en cada renglón.',
            'lines.*.product_id.distinct' => 'No repitas el mismo producto en dos renglones.',
            'lines.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'lines.*.unit_cost.min' => 'El costo unitario debe ser mayor que cero.',
        ]);

        $order = DB::transaction(function () use ($data) {
            $order = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'ordered_at' => $data['ordered_at'],
                'status' => PurchaseOrder::PENDING,
            ]);

            foreach ($data['lines'] as $line) {
                $order->products()->attach($line['product_id'], [
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],   // costo congelado al momento de pedir
                ]);
            }

            return $order;
        });

        return redirect()->route('purchase-orders.show', $order)
            ->with('success', 'Orden de compra registrada. El inventario aumentará cuando la marques como recibida.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'products.brand']);

        return view('purchase_orders.show', ['order' => $purchaseOrder]);
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->receive();
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Orden recibida: las existencias de los productos ya aumentaron.');
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->cancel();
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Orden de compra cancelada. El inventario no cambió.');
    }
}
