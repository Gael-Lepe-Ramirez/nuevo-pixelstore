@extends('layouts.store')

@section('content')
<style>
  .rep-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 22px; height: 100%; }
  .rep-card h5 { font-weight: 700; margin-bottom: 16px; }
  .kpi-value { font-size: 1.8rem; font-weight: 700; color: #1e1e1e; line-height: 1.1; }
  .kpi-label { color: #7a7a7a; font-size: 0.9rem; }
  .bars { display: flex; align-items: flex-end; gap: 14px; height: 220px; border-bottom: 2px solid #eee; padding-top: 24px; }
  .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
  .bar { width: 100%; max-width: 64px; background: #f33f3f; border-radius: 6px 6px 0 0; min-height: 2px; }
  .bar-value { font-size: 0.75rem; color: #555; margin-bottom: 4px; white-space: nowrap; }
  .bar-labels { display: flex; gap: 14px; }
  .bar-labels div { flex: 1; text-align: center; font-size: 0.8rem; color: #7a7a7a; padding-top: 6px; }
  details.sql { margin-top: 14px; }
  details.sql summary { cursor: pointer; color: #f33f3f; font-size: 0.85rem; }
  details.sql pre { background: #1e1e1e; color: #f5f0ea; font-size: 0.8rem; padding: 12px; border-radius: 8px; margin-top: 8px; white-space: pre-wrap; }
</style>

<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Administración</h4>
      <h2>Reportes</h2>
    </div></div></div>
  </div>
</div>

<div class="container mt-4 mb-5">

  {{-- 1. Indicadores --}}
  <div class="row">
    <div class="col-lg-3 col-6 mb-4"><div class="rep-card">
      <div class="kpi-label">Ventas cobradas</div>
      <div class="kpi-value">${{ number_format($kpis['ventas'], 2) }}</div>
    </div></div>
    <div class="col-lg-3 col-6 mb-4"><div class="rep-card">
      <div class="kpi-label">Pedidos pagados</div>
      <div class="kpi-value">{{ $kpis['pedidos_pagados'] }}</div>
    </div></div>
    <div class="col-lg-3 col-6 mb-4"><div class="rep-card">
      <div class="kpi-label">Ticket promedio</div>
      <div class="kpi-value">${{ number_format($kpis['ticket_promedio'], 2) }}</div>
    </div></div>
    <div class="col-lg-3 col-6 mb-4"><div class="rep-card">
      <div class="kpi-label">Pedidos por cobrar</div>
      <div class="kpi-value">{{ $kpis['por_cobrar'] }}</div>
      <a href="{{ route('admin.orders.index', ['payment' => 'pendiente']) }}" class="small">Ver pedidos</a>
    </div></div>
  </div>

  <div class="row">
    {{-- 2. Ventas por mes --}}
    <div class="col-lg-7 mb-4"><div class="rep-card">
      <h5>Ventas por mes (pagadas)</h5>
      <div class="bars">
        @foreach($meses as $m)
          <div class="bar-col" title="{{ $m['pedidos'] }} pedidos">
            <div class="bar-value">${{ number_format($m['total'], 0) }}</div>
            <div class="bar" style="height: {{ round($m['total'] / $maxMes * 100) }}%;"></div>
          </div>
        @endforeach
      </div>
      <div class="bar-labels">
        @foreach($meses as $m) <div>{{ $m['etiqueta'] }}</div> @endforeach
      </div>
      <details class="sql"><summary>Ver consulta SQL</summary>
<pre>SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes,
       SUM(total_amount) AS ventas, COUNT(*) AS pedidos
FROM orders
WHERE payment_status = 'pagado'
  AND created_at >= (inicio del mes, hace 5 meses)
GROUP BY mes
ORDER BY mes;</pre>
      </details>
    </div></div>

    {{-- 3. Más vendidos --}}
    <div class="col-lg-5 mb-4"><div class="rep-card">
      <h5>Los 5 productos más vendidos</h5>
      <table class="table table-sm mb-0">
        <thead><tr><th>Producto</th><th class="text-right">Piezas</th><th class="text-right">Importe</th></tr></thead>
        <tbody>
          @forelse($masVendidos as $p)
            <tr><td>{{ $p->name }}</td><td class="text-right">{{ $p->piezas }}</td><td class="text-right">${{ number_format($p->importe, 2) }}</td></tr>
          @empty
            <tr><td colspan="3" class="text-muted text-center py-3">Todavía no hay ventas.</td></tr>
          @endforelse
        </tbody>
      </table>
      <details class="sql"><summary>Ver consulta SQL</summary>
<pre>SELECT p.name, SUM(op.quantity) AS piezas,
       SUM(op.quantity * op.unit_price) AS importe
FROM order_product op
JOIN orders o   ON o.id = op.order_id
JOIN products p ON p.id = op.product_id
WHERE o.status &lt;&gt; 'cancelado'
GROUP BY p.id, p.name
ORDER BY piezas DESC
LIMIT 5;</pre>
      </details>
    </div></div>
  </div>

  <div class="row">
    {{-- 4. Stock bajo --}}
    <div class="col-lg-6 mb-4"><div class="rep-card">
      <h5>Stock bajo (≤ {{ $lowStock }} piezas)</h5>
      <table class="table table-sm mb-0">
        <thead><tr><th>Producto</th><th class="text-right">Stock</th><th></th></tr></thead>
        <tbody>
          @forelse($stockBajo as $p)
            <tr>
              <td>{{ $p->name }} <small class="text-muted">{{ $p->brand?->name }}</small></td>
              <td class="text-right"><span class="badge {{ $p->stock == 0 ? 'badge-danger' : 'badge-warning' }}">{{ $p->stock }}</span></td>
              <td class="text-right"><a href="{{ route('purchase-orders.create', ['producto' => $p->id]) }}" class="btn btn-sm btn-outline-danger">Pedir</a></td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-muted text-center py-3">Todos los productos tienen existencias suficientes.</td></tr>
          @endforelse
        </tbody>
      </table>
      <details class="sql"><summary>Ver consulta SQL</summary>
<pre>SELECT p.name, b.name AS marca, p.stock
FROM products p
JOIN brands b ON b.id = p.brand_id
WHERE p.is_active = 1 AND p.stock &lt;= {{ $lowStock }}
ORDER BY p.stock;</pre>
      </details>
    </div></div>

    {{-- 5. Compras por proveedor --}}
    <div class="col-lg-6 mb-4"><div class="rep-card">
      <h5>Compras recibidas por proveedor</h5>
      <table class="table table-sm mb-0">
        <thead><tr><th>Proveedor</th><th class="text-right">Órdenes</th><th class="text-right">Piezas</th><th class="text-right">Importe</th></tr></thead>
        <tbody>
          @forelse($porProveedor as $s)
            <tr><td>{{ $s->name }}</td><td class="text-right">{{ $s->ordenes }}</td><td class="text-right">{{ $s->piezas }}</td><td class="text-right">${{ number_format($s->importe, 2) }}</td></tr>
          @empty
            <tr><td colspan="4" class="text-muted text-center py-3">Todavía no hay compras recibidas.</td></tr>
          @endforelse
        </tbody>
      </table>
      <details class="sql"><summary>Ver consulta SQL</summary>
<pre>SELECT s.name, COUNT(DISTINCT po.id) AS ordenes,
       SUM(pop.quantity) AS piezas,
       SUM(pop.quantity * pop.unit_cost) AS importe
FROM purchase_orders po
JOIN suppliers s ON s.id = po.supplier_id
JOIN purchase_order_product pop ON pop.purchase_order_id = po.id
WHERE po.status = 'recibida'
GROUP BY s.id, s.name
ORDER BY importe DESC;</pre>
      </details>
    </div></div>
  </div>
</div>
@endsection
