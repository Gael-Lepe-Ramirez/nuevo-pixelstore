@extends('layouts.store')

@section('content')
<style>
    .custom-input { border-radius: 8px; border: 1px solid #ced4da; transition: all 0.3s ease; padding: 10px 15px; height: auto; }
    .custom-input:focus { border-color: #f33f3f; box-shadow: 0 0 0 0.2rem rgba(243, 63, 63, 0.25); outline: none; }
    .form-wrapper { border-top: 4px solid #f33f3f; border-radius: 12px; }
</style>

<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Proveedores</h4>
      <h2>Editar: {{ $supplier->name }}</h2>
    </div></div></div>
  </div>
</div>

<div class="send-message mb-5">
  <div class="container">
    <div class="row">
      <div class="col-md-8 mx-auto">
        <div class="contact-form bg-white p-5 shadow-sm form-wrapper">
          <div class="section-heading mb-4 text-center">
            <h2 style="font-size: 1.8rem;">Datos del Proveedor</h2>
          </div>

          <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')
            @include('suppliers._form')

            <div class="d-flex justify-content-between">
              <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancelar</a>
              <button type="submit" class="btn btn-danger font-weight-bold px-4">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
