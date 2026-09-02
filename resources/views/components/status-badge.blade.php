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

    $labels = [
        'draft'                => 'Draf',
        'scheduled'            => 'Terjadwal',
        'in_progress'          => 'Sedang Berjalan',
        'completed'            => 'Selesai',
        'cancelled'            => 'Dibatalkan',
        'open'                 => 'Terbuka',
        'waiting_verification' => 'Menunggu Verifikasi',
        'closed'               => 'Ditutup',
        'rejected'             => 'Ditolak',
        'pending'              => 'Menunggu',
        'submitted'            => 'Diajukan',
        'verified'             => 'Terverifikasi',
        'approved'             => 'Disetujui',
    ];
@endphp

<span class="sdx-badge sdx-badge--{{ $tone }}">{{ $labels[strtolower($status)] ?? ucwords($label) }}</span>
