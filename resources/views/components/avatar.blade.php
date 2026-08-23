<!-- Avatar Component: inisial dalam squircle -->
@props([
    'name',
    'size' => null,
])

@php
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->map(fn ($w) => mb_substr($w, 0, 1))
        ->take(2)
        ->implode('');
@endphp

<span {{ $attributes->merge(['class' => 'sdx-avatar' . ($size ? ' sdx-avatar--' . $size : '')]) }} aria-hidden="true">
    {{ strtoupper($initials) }}
</span>
