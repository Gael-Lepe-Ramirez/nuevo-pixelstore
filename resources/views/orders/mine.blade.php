@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Mi cuenta</h4>
      <h2>Mis pedidos</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  <div class="bg-white shadow-sm p-3" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Folio</th><th>Fecha</th><th>Envío</th><th>Pago</th><th class="text-right">Total</th><th>Pedido</th><th>Estado de pago</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            <tr>
              <td class="font-weight-bold">#{{ $order->folio() }}</td>
              <td>{{ $order->created_at->format('d/m/Y') }}</td>
              <td>{{ $order->shippingMethod->name }}</td>
              <td>{{ $order->paymentMethod->name }}</td>
              <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
              <td>@include('orders._badge', ['value' => $order->status])</td>
              <td>@include('orders._badge', ['value' => $order->payment_status])</td>
              <td class="text-right text-nowrap">
                <a href="{{ route('orders.success', $order) }}" class="btn btn-sm btn-outline-dark">Ver</a>
                <a href="{{ route('orders.pdf', $order) }}" class="btn btn-sm btn-outline-danger">PDF</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Todavía no has hecho pedidos. <a href="{{ route('products.index') }}">Ir a la tienda</a></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
