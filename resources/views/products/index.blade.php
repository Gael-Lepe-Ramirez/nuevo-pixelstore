@extends('layouts.store')

@section('content')
<style>
    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .product-img-wrapper {
        position: relative;
        width: 100%;
        height: 220px;
        overflow: hidden;
        background-color: #f8f9fa;
    }
    .product-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .product-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e1e1e;
        margin-bottom: 8px;
        height: 48px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .product-price {
        font-size: 1.25rem;
        color: #f33f3f;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .product-desc {
        font-size: 0.9rem;
        color: #7a7a7a;
        height: 60px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        margin-bottom: 15px;
    }
    .btn-cart {
        background-color: #f33f3f;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-cart:hover {
        background-color: #d32f2f;
        color: #fff;
    }
</style>

<div class="banner header-text mt-0">
  <div class="banner-item" style="background-image: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), url({{ asset('assets/images/main-banner.jpg') }}); background-size: cover; background-position: center; padding: 180px 0px;">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="text-content text-center">
            <h4 class="text-uppercase" style="color: #f33f3f; font-size: 1.2rem; font-weight: 700; letter-spacing: 1px; margin-bottom: 15px;">Bienvenido a PixelStore</h4>
            <h2 style="color: #fff; font-size: 3.5rem; font-weight: 700; text-transform: uppercase;">Componentes y Hardware de Gama Alta</h2>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="latest-products">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="section-heading d-flex justify-content-between align-items-center">
          <h2>Productos Disponibles</h2>
          @if(auth()->check() && auth()->user()->role === 'admin')
          <a href="{{ route('products.create') }}" class="filled-button text-white" style="border-radius: 8px; color: #ffffff !important;">
            + Agregar Nuevo
          </a>
          @endif
        </div>
      </div>
      
      @foreach($products as $product)
      <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
        <div class="product-card">
          
          <div class="product-img-wrapper">
            @if($product->image)
              <img src="{{ asset('storage/' . $product->image->url) }}" alt="{{ $product->name }}" class="product-img">
            @else
              <img src="{{ asset('storage/productos/generico.jpg') }}" alt="Imagen genérica" class="product-img">
            @endif
          </div>
          
          <div class="product-body">
            <h4 class="product-title" title="{{ $product->name }}">{{ $product->name }}</h4>
            <div class="product-price">${{ number_format($product->price, 2) }}</div>
            <p class="product-desc">{{ $product->description }}</p>
            
            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="mt-auto mb-3">
                @csrf
                <button type="submit" class="btn btn-cart btn-block">
                    <i class="fa fa-shopping-cart mr-2"></i> Agregar al Carrito
                </button>
            </form>
            
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
              <span class="badge {{ $product->stock > 0 ? 'badge-light text-dark' : 'badge-danger' }} p-2" style="border-radius: 6px;">
                Stock: {{ $product->stock }}
              </span>
              @if(auth()->check() && auth()->user()->role === 'admin')
              <div class="btn-group">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;">
                  <i class="fa fa-edit"></i> Editar
                </a>
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-top-right-radius: 6px; border-bottom-right-radius: 6px;">
                      <i class="fa fa-trash"></i>
                    </button>
                </form>
              </div>
              @endif
            </div>
          </div>

        </div>
      </div>
      @endforeach

      <div class="col-md-12 mt-5 mb-4 d-flex justify-content-center">
          {{ $products->appends(['search' => request('search')])->links() }}
      </div>

    </div>
  </div>
</div>
@endsection