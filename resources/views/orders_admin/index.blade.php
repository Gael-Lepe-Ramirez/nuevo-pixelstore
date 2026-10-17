@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Administración</h4>
      <h2>Pedidos</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <form method="GET" action="{{ route('admin.orders.index') }}" class="form-inline mb-3">
    <label class="mr-2 font-weight-bold" for="status">Pedido</label>
    <select name="status" id="status" class="form-control mr-3" onchange="this.form.submit()">
      <option value="">Todos</option>
      @foreach(\App\Models\Order::STATUSES as $s)
        <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    <label class="mr-2 font-weight-bold" for="payment">Pago</label>
    <select name="payment" id="payment" class="form-control mr-3" onchange="this.form.submit()">
      <option value="">Todos</option>
      @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
        <option value="{{ $s }}" {{ $payment === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    @if($status || $payment) <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a> @endif
  </form>

  <div class="bg-white shadow-sm p-3" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Folio</th><th>Cliente</th><th>Fecha</th><th>Envío / Pago</th><th class="text-right">Total</th><th>Pedido</th><th>Estado de pago</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            <tr>
              <td class="font-weight-bold">#{{ $order->folio() }}</td>
              <td>{{ $order->user->name }}<br><small class="text-muted">{{ $order->user->email }}</small></td>
              <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $order->shippingMethod->name }}<br><small class="text-muted">{{ $order->paymentMethod->name }}</small></td>
              <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
              <td>@include('orders._badge', ['value' => $order->status])</td>
              <td>@include('orders._badge', ['value' => $order->payment_status])</td>
              <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-dark">Ver</a></td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No hay pedidos con esos filtros.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
