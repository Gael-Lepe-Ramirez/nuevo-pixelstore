@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Orden de compra</h4>
      <h2>OC-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="bg-white shadow-sm p-4 mb-4" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="row">
      <div class="col-md-6">
        <p class="mb-1 text-muted">Proveedor</p>
        <h5 class="font-weight-bold">{{ $order->supplier->name }}</h5>
        <p class="mb-0 text-muted">RFC {{ $order->supplier->rfc }} · {{ $order->supplier->email }} · {{ $order->supplier->phone }}</p>
      </div>
      <div class="col-md-3">
        <p class="mb-1 text-muted">Fecha de pedido</p>
        <h6 class="font-weight-bold">{{ $order->ordered_at->format('d/m/Y') }}</h6>
        <p class="mb-1 text-muted">Fecha de recepción</p>
        <h6 class="font-weight-bold">{{ $order->received_at?->format('d/m/Y') ?? '—' }}</h6>
      </div>
      <div class="col-md-3">
        <p class="mb-1 text-muted">Estado</p>
        @include('purchase_orders._badge', ['status' => $order->status])
      </div>
    </div>
  </div>

  <div class="bg-white shadow-sm p-3 mb-4" style="border-radius: 12px;">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Costo unitario</th><th class="text-right">Subtotal</th></tr>
        </thead>
        <tbody>
          @foreach($order->products as $product)
            <tr>
              <td class="font-weight-bold">{{ $product->name }}<br><small class="text-muted font-weight-normal">{{ $product->brand?->name }} · existencias actuales: {{ $product->stock }}</small></td>
              <td class="text-center">{{ $product->pivot->quantity }}</td>
              <td class="text-right">${{ number_format($product->pivot->unit_cost, 2) }}</td>
              <td class="text-right">${{ number_format($product->pivot->quantity * $product->pivot->unit_cost, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr><th colspan="3" class="text-right">Total de la orden</th><th class="text-right text-danger">${{ number_format($order->total(), 2) }}</th></tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="d-flex justify-content-between flex-wrap">
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary mb-2">← Volver a órdenes</a>

    @if($order->status === \App\Models\PurchaseOrder::PENDING)
      <div>
        <form action="{{ route('purchase-orders.cancel', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta orden de compra?');">
          @csrf
          <button type="submit" class="btn btn-outline-danger mb-2">Cancelar orden</button>
        </form>
        <form action="{{ route('purchase-orders.receive', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Confirmas que llegó la mercancía? Las existencias aumentarán.');">
          @csrf
          <button type="submit" class="btn btn-success font-weight-bold mb-2"><i class="fa fa-check mr-1"></i> Marcar como recibida</button>
        </form>
      </div>
    @endif
  </div>
</div>
@endsection
