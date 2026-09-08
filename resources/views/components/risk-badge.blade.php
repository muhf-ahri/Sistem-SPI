<!-- Risk Badge Component -->
@props(['level'])

@php
    // Warna seragam untuk semua tingkat risiko [bg, text, border]
    $badgeColors = ['bg' => '#D9E2EA', 'text' => '#18324D', 'border' => '#405A73'];
    $labels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
    $risk = $badgeColors + ['label' => $labels[$level] ?? ucfirst($level)];
@endphp

<span class="sdx-badge" style="background: {{ $risk['bg'] }}; color: {{ $risk['text'] }}; border-color: {{ $risk['border'] }};">{{ $risk['label'] }}</span>
