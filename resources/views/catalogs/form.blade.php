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
      <h4>{{ $cfg['title'] }}</h4>
      <h2>{{ $row ? 'Editar '.$cfg['singular'] : 'Nueva '.$cfg['singular'] }}</h2>
    </div></div></div>
  </div>
</div>

<div class="send-message mb-5">
  <div class="container">
    <div class="row">
      <div class="col-md-6 mx-auto">
        <div class="contact-form bg-white p-5 shadow-sm form-wrapper">
          <form action="{{ $row ? route('catalogs.update', [$catalog, $row->id]) : route('catalogs.store', $catalog) }}" method="POST">
            @csrf
            @if($row) @method('PUT') @endif

            @foreach($cfg['fields'] as $f)
              <div class="form-group mb-4">
                <label for="{{ $f['name'] }}" class="font-weight-bold text-dark mb-2">{{ $f['label'] }}@if(! $f['required']) <small class="text-muted font-weight-normal">(opcional)</small>@endif</label>
                <input
                  id="{{ $f['name'] }}" name="{{ $f['name'] }}"
                  type="{{ in_array($f['type'], ['money', 'days']) ? 'number' : ($f['type'] === 'url' ? 'url' : 'text') }}"
                  @if($f['type'] === 'money') step="0.01" min="0" @endif
                  @if($f['type'] === 'days') step="1" min="0" max="365" @endif
                  @if(isset($f['max'])) maxlength="{{ $f['max'] }}" @endif
                  class="form-control custom-input @error($f['name']) is-invalid @enderror"
                  value="{{ old($f['name'], $row?->{$f['name']}) }}"
                  @if($f['required']) required @endif>
                @error($f['name']) <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            @endforeach

            <div class="d-flex justify-content-between">
              <a href="{{ route('catalogs.index', $catalog) }}" class="btn btn-outline-secondary">Cancelar</a>
              <button type="submit" class="btn btn-danger font-weight-bold px-4">{{ $row ? 'Guardar cambios' : 'Guardar' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
