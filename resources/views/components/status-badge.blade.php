<!-- Status Badge Component -->
@props(['status'])

@php
    $colors = [
        'draft' => 'secondary',
        'scheduled' => 'primary',
        'in_progress' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
        'open' => 'danger',
        'waiting_verification' => 'primary',
        'closed' => 'success',
        'rejected' => 'danger',
        'pending' => 'secondary',
        'submitted' => 'info',
        'verified' => 'success',
        'approved' => 'success',
        'rejected' => 'danger',
    ];
    $label = str_replace('_', ' ', $status);
    $color = $colors[$status] ?? 'secondary';
@endphp

<span class="badge bg-{{ $color }}">{{ ucwords($label) }}</span>