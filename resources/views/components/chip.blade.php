<!-- Chip Component: metadata kecil -->
@props(['icon' => null, 'tone' => null])

<span {{ $attributes->merge(['class' => 'sdx-chip' . ($tone ? ' sdx-chip--' . $tone : '')]) }}>
    @if($icon)<i class="bi bi-{{ $icon }}"></i>@endif
    {{ $slot }}
</span>
