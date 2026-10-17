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
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('cart.checkout') }}" method="POST" class="bg-white p-4 shadow-sm" style="border-radius: 12px;">
            @csrf

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="shipping_method_id" class="font-weight-bold text-dark">Método de envío</label>
                    <select name="shipping_method_id" id="shipping_method_id" class="form-control @error('shipping_method_id') is-invalid @enderror" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($shippingMethods as $method)
                            <option value="{{ $method->id }}" data-cost="{{ $method->cost }}" {{ old('shipping_method_id') == $method->id ? 'selected' : '' }}>
                                {{ $method->name }} — ${{ number_format($method->cost, 2) }}
                                ({{ $method->estimated_days > 0 ? $method->estimated_days . ' días' : 'inmediato' }})
                            </option>
                        @endforeach
                    </select>
                    @error('shipping_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 form-group">
                    <label for="payment_method_id" class="font-weight-bold text-dark">Método de pago</label>
                    <select name="payment_method_id" id="payment_method_id" class="form-control @error('payment_method_id') is-invalid @enderror" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                        @endforeach
                    </select>
                    @error('payment_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 form-group">
                    <label for="shipping_address" class="font-weight-bold text-dark">Dirección de entrega</label>
                    <textarea name="shipping_address" id="shipping_address" rows="2" maxlength="255" class="form-control @error('shipping_address') is-invalid @enderror" placeholder="Calle, número, colonia, ciudad, estado, C.P. y referencias" required>{{ old('shipping_address', auth()->user()->shipping_address) }}</textarea>
                    @error('shipping_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Esta dirección se guarda en el pedido y no cambia aunque después edites tu perfil.</small>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center border-top pt-4">
                <a href="{{ route('products.index') }}" class="btn btn-light border px-4 py-2 mb-3 mb-md-0" style="border-radius: 8px; font-weight: 500;">
                    <i class="fa fa-arrow-left mr-2"></i> Seguir Comprando
                </a>

                <div class="text-right">
                    <div class="text-muted">Subtotal: ${{ number_format($total, 2) }}</div>
                    <div class="text-muted">Envío: $<span id="shipping-cost">0.00</span></div>
                    <div class="d-flex align-items-center justify-content-end mt-2">
                        <span class="mr-3 text-uppercase font-weight-bold text-muted">Total a pagar:</span>
                        <span class="cart-total mr-4">$<span id="grand-total">{{ number_format($total, 2) }}</span></span>
                        <button type="submit" class="btn btn-dark font-weight-bold px-4 py-2 shadow-sm text-uppercase" style="border-radius: 8px; letter-spacing: 1px; cursor: pointer;">
                            Confirmar Compra
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <script>
            (function () {
                const subtotal = {{ $total }};
                const select = document.getElementById('shipping_method_id');
                const fmt = (n) => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                function update() {
                    const opt = select.options[select.selectedIndex];
                    const cost = opt && opt.dataset.cost ? parseFloat(opt.dataset.cost) : 0;
                    document.getElementById('shipping-cost').textContent = fmt(cost);
                    document.getElementById('grand-total').textContent = fmt(subtotal + cost);
                }
                select.addEventListener('change', update);
                update();
            })();
        </script>
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