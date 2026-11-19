@extends('layouts.store')

@section('content')
<style>
    .detail-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #f33f3f; }
    .detail-img { width: 100%; height: 420px; object-fit: contain; background: #f8f9fa; border-radius: 10px; }
    .detail-brand { letter-spacing: 1px; font-size: 0.85rem; }
    .detail-title { font-size: 1.9rem; font-weight: 700; color: #1e1e1e; line-height: 1.25; }
    .detail-price { font-size: 2rem; font-weight: 700; color: #f33f3f; }
    .detail-desc { color: #555; font-size: 1rem; line-height: 1.7; }
    .detail-meta td { padding: 6px 0; border: none; }
    .detail-meta td:first-child { color: #7a7a7a; width: 130px; }
    .btn-cart { background-color: #f33f3f; color: #fff; border: none; border-radius: 8px; padding: 12px 20px; font-weight: 600; }
    .btn-cart:hover { background-color: #d32f2f; color: #fff; }
    .btn-cart:disabled { background-color: #c9c9c9; cursor: not-allowed; }
    .related-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.3s ease; height: 100%; }
    .related-card:hover { transform: translateY(-4px); }
    .related-card img { width: 100%; height: 170px; object-fit: cover; }
    .related-card a { color: inherit; text-decoration: none; }
</style>

<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>{{ $product->category?->name }}</h4>
      <h2>Detalle del producto</h2>
    </div></div></div>
  </div>
</div>

<div class="container mt-4 mb-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent px-0">
      <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Productos</a></li>
      <li class="breadcrumb-item">{{ $product->category?->name }}</li>
      <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
    </ol>
  </nav>

  @include('products._cart_flash')

  @if(! $product->is_active)
    <div class="alert alert-warning">Este producto está desactivado y no se puede comprar.</div>
  @endif

  <div class="detail-card p-4">
    <div class="row">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <img src="{{ asset('storage/' . ($product->image->url ?? 'productos/generico.jpg')) }}" alt="{{ $product->name }}" class="detail-img">
      </div>

      <div class="col-lg-6 d-flex flex-column">
        <span class="text-muted text-uppercase detail-brand">{{ $product->brand?->name }}</span>
        <h1 class="detail-title mt-1 mb-3">{{ $product->name }}</h1>
        <div class="detail-price mb-3">${{ number_format($product->price, 2) }}</div>

        @if($product->stock > 5)
          <span class="badge badge-success p-2 align-self-start mb-3" style="border-radius: 6px;">Disponible · {{ $product->stock }} piezas</span>
        @elseif($product->stock > 0)
          <span class="badge badge-warning p-2 align-self-start mb-3" style="border-radius: 6px;">Últimas {{ $product->stock }} piezas</span>
        @else
          <span class="badge badge-danger p-2 align-self-start mb-3" style="border-radius: 6px;">Agotado</span>
        @endif

        <p class="detail-desc">{{ $product->description }}</p>

        <table class="table detail-meta mb-4">
          <tr><td>Marca</td><td>{{ $product->brand?->name ?? '—' }}</td></tr>
          <tr><td>Categoría</td><td>{{ $product->category?->name ?? '—' }}</td></tr>
          <tr><td>Existencias</td><td>{{ $product->stock }}</td></tr>
        </table>

        <div class="mt-auto">
          <form action="{{ route('cart.add', $product->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-cart btn-block" @disabled($product->stock < 1 || ! $product->is_active)>
              <i class="fa fa-shopping-cart mr-2"></i>
              {{ $product->stock < 1 ? 'Sin existencias' : 'Agregar al Carrito' }}
            </button>
          </form>

          <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
              <i class="fa fa-arrow-left mr-1"></i> Seguir comprando
            </a>
            @can('admin')
              <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-dark" style="border-radius: 8px;">
                <i class="fa fa-edit mr-1"></i> Editar producto
              </a>
            @endcan
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($relacionados->isNotEmpty())
    <h4 class="mt-5 mb-3" style="font-weight: 700;">También en {{ $product->category?->name }}</h4>
    <div class="row">
      @foreach($relacionados as $rel)
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="related-card">
            <a href="{{ route('products.show', $rel) }}">
              <img src="{{ asset('storage/' . ($rel->image->url ?? 'productos/generico.jpg')) }}" alt="{{ $rel->name }}">
              <div class="p-3">
                <small class="text-muted text-uppercase" style="letter-spacing: 1px;">{{ $rel->brand?->name }}</small>
                <h6 class="mt-1 mb-2" style="font-weight: 700;">{{ $rel->name }}</h6>
                <span style="color: #f33f3f; font-weight: 700;">${{ number_format($rel->price, 2) }}</span>
              </div>
            </a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
