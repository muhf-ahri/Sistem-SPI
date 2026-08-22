<!-- Risk Badge Component -->
@props(['level'])

@php
    $colors = [
        'low' => 'glaucous-2',
        'medium' => 'gold',
        'high' => 'carrot-orange',
        'critical' => 'racing-red',
    ];
    $color = $colors[$level] ?? 'secondary';
    $label = ucfirst($level);
@endphp

<span class="badge risk-badge risk-{{ $level }}">{{ $label }}</span>