<ul class="nav nav-pills mb-3">
  @foreach(\App\Http\Controllers\CatalogController::CATALOGS as $key => $c)
    <li class="nav-item">
      <a class="nav-link {{ $key === $catalog ? 'active bg-danger' : 'text-dark' }}" href="{{ route('catalogs.index', $key) }}">{{ $c['title'] }}</a>
    </li>
  @endforeach
</ul>
