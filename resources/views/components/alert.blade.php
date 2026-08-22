<!-- Alert Component -->
@props(['type' => 'info', 'dismissible' => false])

@php
    $classes = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'danger' => 'alert-danger',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert ' . ($classes[$type] ?? 'alert-info') . ($dismissible ? ' alert-dismissible' : '') . ' fade show']) }} role="alert">
    {{ $slot }}
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>