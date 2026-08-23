<!-- Status Badge Component -->
@props(['status'])

@php
    $tones = [
        'draft'                => 'neutral',
        'scheduled'            => 'blue',
        'in_progress'          => 'amber',
        'completed'            => 'green',
        'cancelled'            => 'red',
        'open'                 => 'red',
        'waiting_verification' => 'blue',
        'closed'               => 'green',
        'rejected'             => 'red',
        'pending'              => 'neutral',
        'submitted'            => 'amber',
        'verified'             => 'green',
        'approved'             => 'green',
    ];
    $label = str_replace('_', ' ', $status);
    $tone = $tones[$status] ?? 'neutral';
@endphp

<span class="sdx-badge sdx-badge--{{ $tone }}">{{ ucwords($label) }}</span>
