@php
  $colors = [
    'pendiente' => 'warning', 'pagado' => 'success', 'rechazado' => 'danger',
    'enviado' => 'info', 'entregado' => 'success', 'cancelado' => 'secondary',
  ];
@endphp
<span class="badge badge-{{ $colors[$value] ?? 'light' }} p-2 text-capitalize">{{ $value }}</span>
