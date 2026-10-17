@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Administración</h4>
      <h2>Proveedores</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-dark">Ver órdenes de compra</a>
    <a href="{{ route('suppliers.create') }}" class="btn btn-danger font-weight-bold"><i class="fa fa-plus mr-1"></i> Nuevo proveedor</a>
  </div>

  <div class="bg-white shadow-sm p-3" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Razón social</th><th>RFC</th><th>Correo</th><th>Teléfono</th><th class="text-center">Órdenes</th><th class="text-right">Acciones</th></tr>
        </thead>
        <tbody>
          @forelse($suppliers as $supplier)
            <tr>
              <td class="font-weight-bold">{{ $supplier->name }}<br><small class="text-muted font-weight-normal">{{ $supplier->address }}</small></td>
              <td>{{ $supplier->rfc }}</td>
              <td>{{ $supplier->email }}</td>
              <td>{{ $supplier->phone }}</td>
              <td class="text-center">{{ $supplier->purchase_orders_count }}</td>
              <td class="text-right text-nowrap">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-dark">Editar</a>
                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar a este proveedor?');">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aún no hay proveedores registrados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
