@php
  $map = ['pendiente' => 'warning', 'recibida' => 'success', 'cancelada' => 'secondary'];
@endphp
<span class="badge badge-{{ $map[$status] ?? 'light' }} p-2 text-capitalize">{{ $status }}</span>
