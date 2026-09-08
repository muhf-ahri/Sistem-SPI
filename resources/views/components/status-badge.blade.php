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

    // Warna seragam untuk semua status [bg, text, border]
    $badgeColors = ['#D9E2EA', '#18324D', '#405A73'];
    $allStatus = ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled', 'open',
                  'waiting_verification', 'closed', 'rejected', 'pending', 'submitted',
                  'verified', 'approved'];
    $inlineColors = array_fill_keys($allStatus, $badgeColors);
    $key = strtolower($status);
    $inline = $inlineColors[$key] ?? null;

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

@if($inline)
    <span class="sdx-badge" style="background: {{ $inline[0] }}; color: {{ $inline[1] }}; border-color: {{ $inline[2] }};">{{ $labels[$key] ?? ucwords($label) }}</span>
@else
    <span class="sdx-badge sdx-badge--{{ $tone }}">{{ $labels[strtolower($status)] ?? ucwords($label) }}</span>
@endif
