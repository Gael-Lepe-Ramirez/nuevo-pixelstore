@extends('layouts.store')

@section('content')
<div class="page-heading products-heading header-text">
  <div class="container">
    <div class="row"><div class="col-md-12"><div class="text-content">
      <h4>Administración · Catálogos</h4>
      <h2>{{ $cfg['title'] }}</h2>
    </div></div></div>
  </div>
</div>

<div class="container mb-5 mt-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  @include('catalogs._tabs')

  <div class="d-flex justify-content-end mb-3">
    <a href="{{ route('catalogs.create', $catalog) }}" class="btn btn-danger font-weight-bold"><i class="fa fa-plus mr-1"></i> Nueva {{ $cfg['singular'] }}</a>
  </div>

  <div class="bg-white shadow-sm p-3" style="border-top: 4px solid #f33f3f; border-radius: 12px;">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            @foreach($cfg['fields'] as $f) <th>{{ $f['label'] }}</th> @endforeach
            <th class="text-center">En uso</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $row)
            <tr>
              @foreach($cfg['fields'] as $f)
                <td>
                  @if($f['type'] === 'money') ${{ number_format($row->{$f['name']}, 2) }}
                  @elseif($f['type'] === 'days') {{ $row->{$f['name']} }} {{ $row->{$f['name']} == 1 ? 'día' : 'días' }}
                  @else {{ $row->{$f['name']} ?: '—' }}
                  @endif
                </td>
              @endforeach
              <td class="text-center">{!! $usage[$row->id] ? '<span class="badge badge-secondary">Sí</span>' : '<span class="text-muted">No</span>' !!}</td>
              <td class="text-right text-nowrap">
                <a href="{{ route('catalogs.edit', [$catalog, $row->id]) }}" class="btn btn-sm btn-outline-dark">Editar</a>
                @if(! $usage[$row->id])
                  <form action="{{ route('catalogs.destroy', [$catalog, $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="{{ count($cfg['fields']) + 2 }}" class="text-center text-muted py-4">Todavía no hay registros.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <p class="text-muted small mt-3 mb-0">Los registros «en uso» (ligados a productos o pedidos) no se pueden eliminar, para no perder el historial (RN6).</p>
  </div>
</div>
@endsection
