<!-- Risk Badge Component -->
@props(['level'])

@php
    $tones = [
        'low'      => ['tone' => 'blue',   'label' => 'Low'],
        'medium'   => ['tone' => 'gold',   'label' => 'Medium'],
        'high'     => ['tone' => 'orange', 'label' => 'High'],
        'critical' => ['tone' => 'red',    'label' => 'Critical'],
    ];
    $risk = $tones[$level] ?? ['tone' => 'neutral', 'label' => ucfirst($level)];
@endphp

<span class="sdx-badge sdx-badge--{{ $risk['tone'] }}">{{ $risk['label'] }}</span>
