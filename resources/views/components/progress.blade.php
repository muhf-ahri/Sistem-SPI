<!-- Progress Component -->
@props([
    'label',
    'value' => 0,
    'max' => 100,
    'tone' => '#3f7fd4',
])

@php
    $percent = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;
@endphp

<div {{ $attributes }}>
    <div class="sdx-progress-meta">
        <span class="sdx-progress-label">{{ $label }}</span>
        <span class="sdx-progress-value">{{ $value }} / {{ $max }} &middot; {{ $percent }}%</span>
    </div>
    <div class="sdx-progress" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="{{ $max }}" aria-label="{{ $label }}">
        <div class="sdx-progress-bar" style="width: {{ $percent }}%; --p-tone: {{ $tone }};"></div>
    </div>
</div>
