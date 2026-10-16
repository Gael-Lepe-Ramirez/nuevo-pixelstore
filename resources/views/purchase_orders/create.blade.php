@extends('layouts.store')

@section('content')
<style>
    .custom-input { border-radius: 8px; border: 1px solid #ced4da; transition: all 0.3s ease; padding: 8px 12px; height: auto; }
    .custom-input:focus { border-color: #f33f3f; box-shadow: 0 0 0 0.2rem rgba(243, 63, 63, 0.25); outline: none; }
    .form-wrapper { border-top: 4px solid #f33f3f; border-radius: 12px; }
</style>

<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Abastecimiento</h4>
      <h2>Nueva orden de compra</h2>
    </div></div></div>
  </div>
</div>

@php
  // Renglones a pintar: los que el usuario ya había capturado (si hubo error) o uno vacío.
  $oldLines = old('lines', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']]);
@endphp

<div class="container mb-5 mt-4">
  <div class="bg-white p-4 p-md-5 shadow-sm form-wrapper">
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    @if($suppliers->isEmpty())
      <div class="alert alert-warning">
        Primero registra un proveedor. <a href="{{ route('suppliers.create') }}">Crear proveedor</a>
      </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST" id="po-form">
      @csrf
      <div class="row">
        <div class="col-md-8 form-group mb-4">
          <label for="supplier_id" class="font-weight-bold text-dark mb-2">Proveedor</label>
          <select name="supplier_id" id="supplier_id" class="form-control custom-input @error('supplier_id') is-invalid @enderror" required>
            <option value="">-- Selecciona un proveedor --</option>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }} ({{ $supplier->rfc }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 form-group mb-4">
          <label for="ordered_at" class="font-weight-bold text-dark mb-2">Fecha de pedido</label>
          <input type="date" name="ordered_at" id="ordered_at" max="{{ now()->toDateString() }}" value="{{ old('ordered_at', now()->toDateString()) }}" class="form-control custom-input @error('ordered_at') is-invalid @enderror" required>
        </div>
      </div>

      <h5 class="font-weight-bold mb-3">Productos a pedir</h5>
      <div class="table-responsive">
        <table class="table" id="lines-table">
          <thead>
            <tr><th style="min-width:280px">Producto</th><th style="width:120px">Cantidad</th><th style="width:160px">Costo unitario ($)</th><th class="text-right" style="width:140px">Subtotal</th><th style="width:50px"></th></tr>
          </thead>
          <tbody id="lines-body"></tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right font-weight-bold">Total de la orden</td>
              <td class="text-right font-weight-bold text-danger" id="grand-total">$0.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <button type="button" class="btn btn-outline-dark mb-4" id="add-line"><i class="fa fa-plus mr-1"></i> Agregar producto</button>

      <p class="text-muted small">La orden se crea como <strong>pendiente</strong>. Las existencias de los productos no cambian hasta que la marques como recibida.</p>

      <div class="d-flex justify-content-between">
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-danger font-weight-bold px-4" {{ $suppliers->isEmpty() ? 'disabled' : '' }}>Guardar orden de compra</button>
      </div>
    </form>
  </div>
</div>

{{-- Plantilla de un renglón (se clona con JavaScript) --}}
<template id="line-template">
  <tr class="line-row">
    <td>
      <select class="form-control custom-input line-product" required>
        <option value="">-- Selecciona un producto --</option>
        @foreach($products as $product)
          <option value="{{ $product->id }}" data-cost="{{ number_format($product->price * 0.70, 2, '.', '') }}">{{ $product->name }}{{ $product->brand ? ' — '.$product->brand->name : '' }}</option>
        @endforeach
      </select>
    </td>
    <td><input type="number" min="1" step="1" class="form-control custom-input line-qty" required></td>
    <td><input type="number" min="0.01" step="0.01" class="form-control custom-input line-cost" required></td>
    <td class="text-right align-middle line-subtotal">$0.00</td>
    <td class="align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Quitar">&times;</button></td>
  </tr>
</template>

<script>
(function () {
  var body = document.getElementById('lines-body');
  var template = document.getElementById('line-template');
  var initial = @json(array_values($oldLines));
  var next = 0;

  function money(n) { return '$' + n.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

  function recalc() {
    var total = 0;
    body.querySelectorAll('.line-row').forEach(function (row) {
      var q = parseFloat(row.querySelector('.line-qty').value) || 0;
      var c = parseFloat(row.querySelector('.line-cost').value) || 0;
      row.querySelector('.line-subtotal').textContent = money(q * c);
      total += q * c;
    });
    document.getElementById('grand-total').textContent = money(total);
  }

  function addLine(data) {
    data = data || {};
    var row = template.content.firstElementChild.cloneNode(true);
    var i = next++;
    var select = row.querySelector('.line-product');
    var qty = row.querySelector('.line-qty');
    var cost = row.querySelector('.line-cost');

    select.name = 'lines[' + i + '][product_id]';
    qty.name = 'lines[' + i + '][quantity]';
    cost.name = 'lines[' + i + '][unit_cost]';
    select.value = data.product_id || '';
    qty.value = data.quantity || 1;
    cost.value = data.unit_cost || '';

    // Al elegir producto, sugiere un costo (70 % del precio de venta) si el campo está vacío.
    select.addEventListener('change', function () {
      var opt = select.options[select.selectedIndex];
      if (!cost.value && opt && opt.dataset.cost) { cost.value = opt.dataset.cost; }
      recalc();
    });
    qty.addEventListener('input', recalc);
    cost.addEventListener('input', recalc);
    row.querySelector('.remove-line').addEventListener('click', function () {
      if (body.querySelectorAll('.line-row').length > 1) { row.remove(); recalc(); }
    });

    body.appendChild(row);
    recalc();
  }

  document.getElementById('add-line').addEventListener('click', function () { addLine(); });
  initial.forEach(addLine);
})();
</script>
@endsection
