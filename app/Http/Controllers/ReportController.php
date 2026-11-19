<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

/**
 * Panel de reportes del administrador. Cada reporte es una consulta de agregación sobre el modelo
 * de datos (SUM, COUNT, AVG, GROUP BY y JOIN).
 */
class ReportController extends Controller
{
    /** Por debajo de este número de piezas un producto se considera con stock bajo. */
    public const LOW_STOCK = 5;

    public function index()
    {
        // 1. Indicadores generales (solo pedidos pagados cuentan como venta)
        $pagados = Order::where('payment_status', Order::PAID);
        $kpis = [
            'ventas' => (clone $pagados)->sum('total_amount'),
            'pedidos_pagados' => (clone $pagados)->count(),
            'ticket_promedio' => (clone $pagados)->avg('total_amount') ?? 0,
            'por_cobrar' => Order::where('payment_status', Order::PAY_PENDING)
                ->where('status', '!=', Order::CANCELLED)->count(),
        ];

        // 2. Ventas por mes de los últimos 6 meses (se agrupa en PHP para funcionar igual en MySQL y SQLite)
        $desde = now()->startOfMonth()->subMonths(5);
        $porMes = Order::where('payment_status', Order::PAID)
            ->where('created_at', '>=', $desde)
            ->get(['total_amount', 'created_at'])
            ->groupBy(fn ($o) => $o->created_at->format('Y-m'))
            ->map(fn ($grupo) => ['total' => $grupo->sum('total_amount'), 'pedidos' => $grupo->count()]);

        $meses = collect(range(0, 5))->map(function ($i) use ($desde, $porMes) {
            $mes = $desde->copy()->addMonths($i);
            $clave = $mes->format('Y-m');

            return [
                'etiqueta' => ucfirst($mes->locale('es')->translatedFormat('M Y')),
                'total' => (float) ($porMes[$clave]['total'] ?? 0),
                'pedidos' => $porMes[$clave]['pedidos'] ?? 0,
            ];
        });
        $maxMes = max($meses->max('total'), 1);

        // 3. Los 5 productos más vendidos (pedidos no cancelados)
        $masVendidos = DB::table('order_product')
            ->join('orders', 'orders.id', '=', 'order_product.order_id')
            ->join('products', 'products.id', '=', 'order_product.product_id')
            ->where('orders.status', '!=', Order::CANCELLED)
            ->groupBy('products.id', 'products.name')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_product.quantity) AS piezas'),
                DB::raw('SUM(order_product.quantity * order_product.unit_price) AS importe')
            )
            ->orderByDesc('piezas')
            ->limit(5)
            ->get();

        // 4. Productos activos con stock bajo
        $stockBajo = Product::with('brand')
            ->where('is_active', true)
            ->where('stock', '<=', self::LOW_STOCK)
            ->orderBy('stock')
            ->get();

        // 5. Compras recibidas por proveedor
        $porProveedor = DB::table('purchase_orders')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->join('purchase_order_product', 'purchase_order_product.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_orders.status', PurchaseOrder::RECEIVED)
            ->groupBy('suppliers.id', 'suppliers.name')
            ->select(
                'suppliers.name',
                DB::raw('COUNT(DISTINCT purchase_orders.id) AS ordenes'),
                DB::raw('SUM(purchase_order_product.quantity) AS piezas'),
                DB::raw('SUM(purchase_order_product.quantity * purchase_order_product.unit_cost) AS importe')
            )
            ->orderByDesc('importe')
            ->get();

        return view('reports.index', [
            'kpis' => $kpis,
            'meses' => $meses,
            'maxMes' => $maxMes,
            'masVendidos' => $masVendidos,
            'stockBajo' => $stockBajo,
            'porProveedor' => $porProveedor,
            'lowStock' => self::LOW_STOCK,
        ]);
    }
}
