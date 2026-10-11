@extends('layouts.store')

@section('content')
<style>
    .custom-input {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
        padding: 10px 15px;
    }
    .custom-input:focus {
        border-color: #f33f3f;
        box-shadow: 0 0 0 0.2rem rgba(243, 63, 63, 0.25);
        outline: none;
    }
    .form-wrapper {
        border-top: 4px solid #f33f3f;
        border-radius: 12px;
    }
</style>

<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="text-content">
          <h4>Inventario</h4>
          <h2>Registrar Nuevo Producto</h2>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="send-message mb-5">
  <div class="container">
    <div class="row">
      <div class="col-md-8 mx-auto">
        <div class="contact-form bg-white p-5 shadow-sm form-wrapper">
          <div class="section-heading mb-4 text-center">
            <h2 style="font-size: 1.8rem;">Detalles del Producto</h2>
          </div>
          
          <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
              <div class="col-lg-12 form-group mb-4">
                <label for="name" class="font-weight-bold text-dark mb-2">Nombre del Producto</label>
                <input name="name" type="text" class="form-control custom-input @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" placeholder="Ej. Tarjeta de Video NVIDIA RTX 4060">
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-12 form-group mb-4">
                <label for="category_id" class="font-weight-bold text-dark mb-2">Categoría</label>
                <select name="category_id" id="category_id" class="form-control custom-input @error('category_id') is-invalid @enderror">
                  <option value="">-- Selecciona una categoría --</option>
                  @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                      {{ $category->name }}
                    </option>
                  @endforeach
                </select>
                @error('category_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-6 form-group mb-4">
                <label for="price" class="font-weight-bold text-dark mb-2">Precio ($)</label>
                <input name="price" type="number" step="0.01" class="form-control custom-input @error('price') is-invalid @enderror" id="price" value="{{ old('price') }}" placeholder="0.00">
                @error('price')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-6 form-group mb-4">
                <label for="stock" class="font-weight-bold text-dark mb-2">Stock Inicial</label>
                <input name="stock" type="number" class="form-control custom-input @error('stock') is-invalid @enderror" id="stock" value="{{ old('stock') }}" placeholder="Cantidad disponible">
                @error('stock')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-12 form-group mb-4">
                <label for="image" class="font-weight-bold text-dark mb-2">Fotografía del Producto</label>
                <div class="p-3 border rounded bg-light">
                    <input name="image" type="file" class="form-control-file @error('image') is-invalid @enderror" id="image" accept="image/*">
                </div>
                @error('image')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-12 form-group mb-4">
                <label for="description" class="font-weight-bold text-dark mb-2">Descripción Técnica</label>
                <textarea name="description" rows="4" class="form-control custom-input @error('description') is-invalid @enderror" id="description" placeholder="Detalles, especificaciones de compatibilidad, etc.">{{ old('description') }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-12 mt-4 d-flex justify-content-between align-items-center border-top pt-4">
                <a href="{{ route('products.index') }}" class="btn btn-light border px-4 py-2" style="border-radius: 8px; font-weight: 500;">
                  Cancelar
                </a>
                <button type="submit" id="form-submit" class="filled-button" style="border-radius: 8px;">
                  Guardar Producto
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection