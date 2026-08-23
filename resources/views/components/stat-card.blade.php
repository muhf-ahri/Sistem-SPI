<!-- Stat Card Component -->
@props(['icon', 'label', 'value', 'color' => 'primary', 'subtext' => null])

@php
    $palettes = [
        'primary'   => ['bg' => '#e8f0fc', 'fg' => '#1d4f9c'],
        'info'      => ['bg' => '#e7f1fd', 'fg' => '#2160b4'],
        'success'   => ['bg' => '#e4f5ec', 'fg' => '#1c7a46'],
        'warning'   => ['bg' => '#fdf1de', 'fg' => '#b3640f'],
        'danger'    => ['bg' => '#fcebeb', 'fg' => '#bf2b2b'],
        'secondary' => ['bg' => '#edf1f7', 'fg' => '#51617a'],
    ];
    $tone = $palettes[$color] ?? $palettes['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'card stat-card h-100']) }}>
    <div class="card-body d-flex align-items-start justify-content-between gap-3">
        <div>
            <div class="sdx-stat-label">{{ $label }}</div>
            <div class="sdx-stat-value">{{ $value }}</div>
            @if($subtext)
                <div class="small text-muted mt-1">{{ $subtext }}</div>
            @endif
        </div>
        <div class="sdx-stat-icon" style="background: {{ $tone['bg'] }}; color: {{ $tone['fg'] }};">
            <i class="bi bi-{{ $icon }}"></i>
        </div>
    </div>
</div>
