@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Administración</h4>
      <h2>Órdenes de compra</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-dark">Ver proveedores</a>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-danger font-weight-bold"><i class="fa fa-plus mr-1"></i> Nueva orden de compra</a>
  </div>

  <div class="bg-white shadow-sm p-3" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Folio</th><th>Proveedor</th><th>Fecha de pedido</th><th>Recibida</th><th class="text-right">Total</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            <tr>
              <td class="font-weight-bold">OC-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</td>
              <td>{{ $order->supplier->name }}</td>
              <td>{{ $order->ordered_at->format('d/m/Y') }}</td>
              <td>{{ $order->received_at?->format('d/m/Y') ?? '—' }}</td>
              <td class="text-right">${{ number_format($order->total(), 2) }}</td>
              <td>@include('purchase_orders._badge', ['status' => $order->status])</td>
              <td class="text-right"><a href="{{ route('purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-dark">Ver</a></td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay órdenes de compra.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
