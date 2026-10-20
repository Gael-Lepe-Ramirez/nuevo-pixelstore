@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Pedido</h4>
      <h2>#{{ $order->folio() }}</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="bg-white shadow-sm p-4 mb-4" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="row">
      <div class="col-md-4 mb-3">
        <p class="mb-1 text-muted">Cliente</p>
        <h6 class="font-weight-bold mb-0">{{ $order->user->name }} {{ $order->user->last_name }}</h6>
        <small class="text-muted">{{ $order->user->email }}@if($order->user->phone) · {{ $order->user->phone }}@endif</small>
      </div>
      <div class="col-md-4 mb-3">
        <p class="mb-1 text-muted">Entrega en</p>
        <h6 class="font-weight-bold mb-0">{{ $order->shipping_address }}</h6>
        <small class="text-muted">{{ $order->shippingMethod->name }} (${{ number_format($order->shippingMethod->cost, 2) }})</small>
      </div>
      <div class="col-md-4 mb-3">
        <p class="mb-1 text-muted">Fecha del pedido</p>
        <h6 class="font-weight-bold mb-0">{{ $order->created_at->format('d/m/Y H:i') }}</h6>
        <small class="text-muted">Método de pago: {{ $order->paymentMethod->name }}</small>
      </div>
    </div>
    <hr>
    <div class="row">
      <div class="col-md-6">
        <span class="text-muted mr-2">Estado del pedido:</span> @include('orders._badge', ['value' => $order->status])
      </div>
      <div class="col-md-6">
        <span class="text-muted mr-2">Estado de pago:</span> @include('orders._badge', ['value' => $order->payment_status])
      </div>
    </div>
  </div>

  <div class="bg-white shadow-sm p-3 mb-4" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Precio unitario</th><th class="text-right">Subtotal</th></tr>
        </thead>
        <tbody>
          @foreach($order->products as $product)
            <tr>
              <td class="font-weight-bold">{{ $product->name }}<br><small class="text-muted font-weight-normal">{{ $product->brand?->name }}</small></td>
              <td class="text-center">{{ $product->pivot->quantity }}</td>
              <td class="text-right">${{ number_format($product->pivot->unit_price, 2) }}</td>
              <td class="text-right">${{ number_format($product->pivot->quantity * $product->pivot->unit_price, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr><td colspan="3" class="text-right">Subtotal</td><td class="text-right">${{ number_format($order->subtotal(), 2) }}</td></tr>
          <tr><td colspan="3" class="text-right">Envío</td><td class="text-right">${{ number_format($order->total_amount - $order->subtotal(), 2) }}</td></tr>
          <tr><th colspan="3" class="text-right">Total</th><th class="text-right text-danger">${{ number_format($order->total_amount, 2) }}</th></tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="bg-white shadow-sm p-4 mb-4" style="border-radius: 12px;">
    <h5 class="font-weight-bold mb-3">Acciones</h5>

    @php
      $actions = [
        ['pay', 'Confirmar pago', 'btn-success', $order->canMarkPaid(), '¿Confirmas que el pago ya se recibió?'],
        ['reject-payment', 'Rechazar pago', 'btn-outline-danger', $order->canRejectPayment(), 'El pedido se cancelará y las unidades regresarán al inventario. ¿Continuar?'],
        ['ship', 'Marcar como enviado', 'btn-dark', $order->canShip(), '¿Marcar el pedido como enviado?'],
        ['deliver', 'Marcar como entregado', 'btn-dark', $order->canDeliver(), '¿Confirmas que el pedido fue entregado?'],
        ['cancel', 'Cancelar pedido', 'btn-outline-danger', $order->canCancel(), 'Las unidades regresarán al inventario. ¿Cancelar el pedido?'],
      ];
      $any = collect($actions)->contains(fn ($a) => $a[3]);
    @endphp

    @foreach($actions as [$action, $label, $class, $enabled, $confirm])
      @if($enabled)
        <form action="{{ route('admin.orders.transition', [$order, $action]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $confirm }}');">
          @csrf
          <button type="submit" class="btn {{ $class }} mr-2 mb-2">{{ $label }}</button>
        </form>
      @endif
    @endforeach

    @if(! $any)
      <p class="text-muted mb-0">Este pedido ya no admite cambios de estado.</p>
    @endif

    @if($order->status === \App\Models\Order::PENDING && $order->payment_status !== \App\Models\Order::PAID)
      <p class="text-muted small mt-2 mb-0">El pedido no puede enviarse hasta que el pago esté confirmado.</p>
    @endif
  </div>

  <div class="d-flex justify-content-between">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">← Volver a pedidos</a>
    <a href="{{ route('orders.pdf', $order) }}" class="btn btn-outline-danger">Descargar recibo (PDF)</a>
  </div>
</div>
@endsection
