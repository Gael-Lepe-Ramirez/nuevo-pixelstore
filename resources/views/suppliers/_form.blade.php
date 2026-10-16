@php $s = $supplier ?? null; @endphp
<div class="row">
  <div class="col-lg-12 form-group mb-4">
    <label for="name" class="font-weight-bold text-dark mb-2">Razón social</label>
    <input name="name" id="name" type="text" maxlength="100" class="form-control custom-input @error('name') is-invalid @enderror" value="{{ old('name', $s?->name) }}" placeholder="Ej. Distribuidora Tecnológica del Pacífico">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-lg-6 form-group mb-4">
    <label for="rfc" class="font-weight-bold text-dark mb-2">RFC</label>
    <input name="rfc" id="rfc" type="text" maxlength="13" class="form-control custom-input text-uppercase @error('rfc') is-invalid @enderror" value="{{ old('rfc', $s?->rfc) }}" placeholder="ABC010203XY1">
    @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <small class="text-muted">Identifica al proveedor ante el SAT y evita registrarlo dos veces.</small>
  </div>

  <div class="col-lg-6 form-group mb-4">
    <label for="phone" class="font-weight-bold text-dark mb-2">Teléfono</label>
    <input name="phone" id="phone" type="text" maxlength="15" class="form-control custom-input @error('phone') is-invalid @enderror" value="{{ old('phone', $s?->phone) }}" placeholder="3312345678">
    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-lg-12 form-group mb-4">
    <label for="email" class="font-weight-bold text-dark mb-2">Correo electrónico</label>
    <input name="email" id="email" type="email" maxlength="100" class="form-control custom-input @error('email') is-invalid @enderror" value="{{ old('email', $s?->email) }}" placeholder="ventas@proveedor.com.mx">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-lg-12 form-group mb-4">
    <label for="address" class="font-weight-bold text-dark mb-2">Dirección</label>
    <input name="address" id="address" type="text" maxlength="150" class="form-control custom-input @error('address') is-invalid @enderror" value="{{ old('address', $s?->address) }}" placeholder="Calle, número, colonia, ciudad, estado">
    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
</div>
