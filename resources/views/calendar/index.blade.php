@extends('layouts.app')

@section('title', 'Kalender Penjadwalan')

@section('content')
@php
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$month-1];
    $hari = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
@endphp

<x-page-header title="Kalender Penjadwalan Audit &amp; Temuan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Kalender</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>
        <form method="GET" action="{{ route('calendar.index') }}" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Tahun</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-slot:actions>
</x-page-header>

<!-- Navigasi Bulan -->
<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
        <a href="{{ route('calendar.index', ['month' => $prev->month, 'year' => $prev->year]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-left me-1"></i>{{ $prev->translatedFormat('F Y') }}
        </a>
        <h3 class="mb-0 text-center" style="font-family: var(--font-display, 'Chakra Petch', sans-serif); font-weight: 700; text-transform: uppercase; color: var(--tinta, #10263f);">
            {{ $bulan }} {{ $year }}
        </h3>
        <a href="{{ route('calendar.index', ['month' => $next->month, 'year' => $next->year]) }}" class="btn btn-outline-secondary btn-sm">
            {{ $next->translatedFormat('F Y') }}<i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>

    <style>
        .cld-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
        .cld-head { padding: .6rem .4rem; text-align: center; font-family: var(--font-mono, monospace); font-size: .66rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--baja, #51677e); border-bottom: 1.5px solid var(--tinta, #10263f); }
        .cld-day { min-height: 108px; border-right: 1px solid var(--garis-halus, #dde5ec); border-bottom: 1px solid var(--garis-halus, #dde5ec); padding: .35rem .4rem; position: relative; background: var(--lembar, #fff); }
        .cld-day:nth-child(7n) { border-right: none; }
        .cld-day.out { background: #f4f7fa; color: #a7b4c2; }
        .cld-day.off { background: #fdf6e6; }
        .cld-day.today .cld-num { background: var(--tinta, #10263f); color: var(--kuning, #ffc72c); }
        .cld-num { width: 24px; height: 24px; display: grid; place-items: center; border-radius: 2px; font-family: var(--font-mono, monospace); font-size: .78rem; font-weight: 600; }
        .cld-hol { font-size: .62rem; color: var(--merah, #c6362b); font-style: italic; line-height: 1.1; margin-top: 2px; }
        .cld-events { margin-top: 4px; display: flex; flex-direction: column; gap: 3px; }
        .cld-ev { display: block; font-size: .6rem; font-weight: 600; letter-spacing: .03em; padding: 1px 5px; border-radius: 2px; color: #fff; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cld-ev:hover { text-decoration: underline; opacity: .9; }
        .cld-ev.biru   { background: #3f7fd4; }
        .cld-ev.kuning { background: #e0a800; }
        .cld-ev.hijau  { background: #2ea866; }
        .cld-ev.abu    { background: #7d8b99; }
        .cld-ev.merah  { background: #d8493c; }
        .cld-ev.selesai { background: #6c7a89; }
        .cld-legend { display: flex; flex-wrap: wrap; gap: .8rem; margin: 0; padding: .8rem 1.1rem; border-top: 1px solid var(--garis-halus, #dde5ec); font-family: var(--font-mono, monospace); font-size: .66rem; text-transform: uppercase; letter-spacing: .08em; color: var(--baja, #51677e); }
        .cld-legend span { display: inline-flex; align-items: center; gap: .35rem; }
        .cld-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
    </style>

    <div class="table-responsive">
        <div class="cld-grid">
            @foreach($hari as $h)
                <div class="cld-head">{{ $h }}</div>
            @endforeach
            @foreach($weeks as $week)
                @foreach($week as $day)
                    <div class="cld-day {{ $day['inMonth'] ? '' : 'out' }} {{ $day['isHoliday'] ? 'off' : '' }} {{ $day['isToday'] ? 'today' : '' }}">
                        <span class="cld-num">{{ $day['date']->format('j') }}</span>
                        @if($day['holidayName'])
                            <div class="cld-hol" title="{{ $day['holidayName'] }}">{{ \Illuminate\Support\Str::limit($day['holidayName'], 14) }}</div>
                        @endif
                        <div class="cld-events">
                            @foreach($day['events'] as $ev)
                                <a class="cld-ev {{ $ev['color'] }}" href="{{ $ev['url'] }}" title="{{ $ev['title'] }}">
                                    <i class="bi {{ $ev['type'] === 'audit' ? 'bi-clipboard-check' : ($ev['type'] === 'audit_end' ? 'bi-flag-fill' : 'bi-exclamation-triangle-fill') }}"></i>
                                    {{ $ev['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <ul class="cld-legend no-sort">
        <span><i class="cld-dot biru"></i> Mulai Audit</span>
        <span><i class="cld-dot selesai"></i> Selesai Audit</span>
        <span><i class="cld-dot merah"></i> Deadline Temuan</span>
        <span><i class="cld-dot" style="background:#fdf6e6;border:1px solid #ecd98a;"></i> Libur/Minggu</span>
    </ul>
</div>

<!-- Detail per tanggal: penjadwalan audit & temuan -->
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Rincian Penjadwalan {{ $bulan }} {{ $year }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 no-sort">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Hari</th>
                        <th>Libur</th>
                        <th class="text-end pe-4">Kegiatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasRows = false; @endphp
                    @foreach($weeks as $week)
                        @foreach($week as $day)
                            @if($day['inMonth'] && ($day['events'] || $day['holidayName']))
                                @php $hasRows = true; @endphp
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $day['date']->format('d F Y') }}</td>
                                    <td>{{ $day['date']->isoFormat('dddd') }}</td>
                                    <td>
                                        @if($day['holidayName'])
                                            <span class="sdx-badge sdx-badge--red"><i class="bi bi-calendar-x"></i>{{ $day['holidayName'] }}</span>
                                        @elseif($day['isWeekend'])
                                            <span class="text-muted">Sabtu/Minggu</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @forelse($day['events'] as $ev)
                                            <a href="{{ $ev['url'] }}" class="btn btn-sm me-1 mb-1" style="background:{{ $ev['type']==='finding' ? '#d8493c' : ($ev['type']==='audit_end' ? '#6c7a89' : '#3f7fd4') }};color:#fff;">
                                                <i class="bi {{ $ev['type']==='audit' ? 'bi-clipboard-check' : ($ev['type']==='audit_end' ? 'bi-flag-fill' : 'bi-exclamation-triangle-fill') }}"></i>
                                                {{ $ev['type']==='finding' ? $ev['label'] : ($ev['type']==='audit_end' ? ($ev['label']==='Selesai' ? 'Audit selesai' : $ev['label']) : 'Audit '.$ev['label']) }}
                                            </a>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach
                    @if(!$hasRows)
                        <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada kegiatan atau hari libur pada bulan ini.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
