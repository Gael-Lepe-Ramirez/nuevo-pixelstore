@extends('layouts.store')

@section('content')
<style>
    .cart-wrapper {
        border-top: 4px solid #f33f3f;
        border-radius: 12px;
        overflow: hidden;
    }
    .table th {
        border-top: none;
        text-transform: uppercase;
        font-size: 0.9rem;
        color: #5a5a5a;
    }
    .table td {
        vertical-align: middle;
    }
    .cart-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: #f33f3f;
    }
</style>

<div class="page-heading header-text" style="background-image: linear-gradient(rgba(15, 15, 15, 0.75), rgba(15, 15, 15, 0.75)), url('{{ asset('assets/images/banner-carrito.jpg') }}'); background-size: cover; background-position: center; padding: 160px 0px;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="text-content text-center">
                    <h4 style="color: #f33f3f; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 15px;">Tu Orden</h4>
                    <h2 style="color: #fff; font-size: 46px; font-weight: 700; letter-spacing: 5px; text-transform: uppercase;">Carrito de Compras</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="send-message mb-5">
  <div class="container">
    <div class="row">
      <div class="col-md-10 mx-auto">
        @if(session('cart') && count(session('cart')) > 0)
        <div class="bg-white p-4 shadow-sm cart-wrapper mb-4">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0">Producto</th>
                            <th class="border-bottom-0 text-center">Precio</th>
                            <th class="border-bottom-0 text-center">Cantidad</th>
                            <th class="border-bottom-0 text-center">Subtotal</th>
                            <th class="border-bottom-0 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach(session('cart') as $id => $details)
                            @php $total += $details['price'] * $details['quantity']; @endphp
                            <tr>
                                <td class="font-weight-bold">
                                    @if(isset($details['image']) && $details['image'])
                                        <img src="{{ asset('storage/' . $details['image']) }}" alt="{{ $details['name'] }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; margin-right: 10px; border: 1px solid #eee;">
                                    @else
                                        <img src="{{ asset('assets/images/product_01.jpg') }}" alt="Sin imagen" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; margin-right: 10px; border: 1px solid #eee;">
                                    @endif
                                    {{ $details['name'] }}
                                </td>
                                <td class="text-center text-muted">${{ number_format($details['price'], 2) }}</td>
                                <td class="text-center">{{ $details['quantity'] }}</td>
                                <td class="text-center font-weight-bold">${{ number_format($details['price'] * $details['quantity'], 2) }}</td>
                                <td class="text-center">
                                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Quitar del carrito" style="border-radius: 6px;">
                                            <i class="fa fa-trash"></i> Quitar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center bg-white p-4 shadow-sm" style="border-radius: 12px;">
            <a href="{{ route('products.index') }}" class="btn btn-light border px-4 py-2 mb-3 mb-md-0" style="border-radius: 8px; font-weight: 500;">
                <i class="fa fa-arrow-left mr-2"></i> Seguir Comprando
            </a>
            
            <div class="d-flex align-items-center">
                <span class="mr-4 text-uppercase font-weight-bold text-muted">Total a pagar:</span>
                <span class="cart-total mr-4">${{ number_format($total, 2) }}</span>
                <form action="{{ route('cart.checkout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-dark font-weight-bold px-4 py-2 shadow-sm text-uppercase" style="border-radius: 8px; letter-spacing: 1px; cursor: pointer;">
                        Confirmar Compra
                    </button>
                </form>
            </div>
        </div>
        @else
            <div class="text-center py-5 bg-white shadow-sm cart-wrapper">
                <div class="mb-4">
                    <i class="fa fa-shopping-cart text-muted" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-dark font-weight-bold mb-3">Tu carrito está vacío</h4>
                <p class="text-muted mb-4">Aún no has agregado ningún producto a tu orden.</p>
                <a href="{{ route('products.index') }}" class="filled-button" style="border-radius: 8px;">Ir al Catálogo</a>
            </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection