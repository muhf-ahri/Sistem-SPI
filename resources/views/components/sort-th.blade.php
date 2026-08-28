@props(['col', 'label'])
@php
    $sort  = request('sort', 'created_at');
    $dir   = request('direction', 'desc');
    $toggle = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    $icon  = $sort === $col
        ? ($dir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill')
        : 'bi-arrow-down-up';
    $query = array_merge(
        request()->except(['sort', 'direction', 'page']),
        ['sort' => $col, 'direction' => $toggle, 'page' => 1]
    );
@endphp
<th>
    <a class="sdx-sort {{ $sort === $col ? 'sorted' : '' }}"
       href="{{ url()->current() . '?' . http_build_query($query) }}">
        {{ $label }} <i class="bi {{ $icon }}"></i>
    </a>
</th>
