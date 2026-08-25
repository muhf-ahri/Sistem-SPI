<!-- Stat Card Component -->
@props(['icon', 'label', 'value', 'color' => 'primary', 'subtext' => null])

@php
    $palettes = [
        'primary'   => ['bg' => '#e9eef5', 'fg' => '#16304f'],
        'info'      => ['bg' => '#e9f1fb', 'fg' => '#2c62b8'],
        'success'   => ['bg' => '#e5f4ec', 'fg' => '#177244'],
        'warning'   => ['bg' => '#fdefdd', 'fg' => '#a85710'],
        'danger'    => ['bg' => '#fceeee', 'fg' => '#b02a25'],
        'secondary' => ['bg' => '#eef1f5', 'fg' => '#51677e'],
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
