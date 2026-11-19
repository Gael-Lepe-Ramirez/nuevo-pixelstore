{{-- Aviso al agregar un producto al carrito (catálogo y detalle) --}}
@if(session('cart_success'))
  <div class="alert alert-success d-flex justify-content-between align-items-center" style="border-radius: 8px;">
    <span><i class="fa fa-check-circle mr-2"></i>{{ session('cart_success') }}</span>
    <a href="{{ route('cart.index') }}" class="btn btn-sm btn-success" style="border-radius: 6px;">Ver carrito</a>
  </div>
@endif
@if(session('cart_error'))
  <div class="alert alert-warning" style="border-radius: 8px;"><i class="fa fa-exclamation-triangle mr-2"></i>{{ session('cart_error') }}</div>
@endif
