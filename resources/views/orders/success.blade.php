@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="text-content">
          <h4>Confirmación</h4>
          <h2>Pedido Completado</h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mb-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="bg-white p-5 shadow-sm text-center" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
                
                <div class="mb-4">
                    <i class="fa fa-check-circle" style="color: #28a745; font-size: 6rem;"></i>
                </div>

                <h3 class="font-weight-bold text-dark mb-3">¡Compra Exitosa!</h3>
                <p class="text-muted mb-2" style="font-size: 1.2rem;">Gracias por tu preferencia, {{ auth()->user()->name }}.</p>
                <p class="text-muted mb-5">
                    Tu orden <strong class="text-dark">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong> ha sido procesada correctamente y tus componentes ya se están preparando para el envío. Hemos enviado una copia del recibo a tu correo.
                </p>

                <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center">
                    <a href="{{ route('orders.pdf', $order->id) }}" class="btn btn-danger px-4 py-3 font-weight-bold mb-3 mb-sm-0" style="border-radius: 8px; margin-right: 15px;">
                        <i class="fa fa-file-pdf-o mr-2"></i> Descargar Recibo (PDF)
                    </a>
                    
                    <a href="{{ route('products.index') }}" class="btn btn-dark px-4 py-3 font-weight-bold" style="border-radius: 8px;">
                        Volver a la Tienda
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection