@extends('layouts.app')

@section('title', 'Analisis Perbandingan Temuan')

@section('content')
<x-page-header title="Analisis Perbandingan Temuan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Laporan</a></li>
            <li class="breadcrumb-item active">Perbandingan Temuan</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.comparison') }}" class="row align-items-end g-3">
            <div class="col-md-4">
                <label for="division" class="form-label small text-muted">
                    Divisi
                    @if($divisionId !== null) <span class="badge bg-secondary ms-1">Terkunci</span> @endif
                </label>
                @if($divisionId !== null)
                    <input type="text" class="form-control form-control-sm" value="{{ $divisionLabel }}" disabled>
                    <input type="hidden" name="division" value="{{ $divisionId }}">
                @else
                    <select name="division" id="division" class="form-select form-select-sm">
                        <option value="">-- Semua Divisi --</option>
                        @foreach($divisions as $id => $name)
                            <option value="{{ $id }}" {{ $divisionId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label small text-muted">Tahun Pembanding</label>
                <select name="year" id="year" class="form-select form-select-sm">
                    @foreach($yearOptions as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Bandingkan</button>
                <a href="{{ route('reports.comparison') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan pembanding --}}
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <x-stat-card icon="shield-exclamation" label="Temuan {{ $year }}" value="{{ $currentTotal }}" color="primary" />
    </div>
    <div class="col-lg-3 col-md-6">
        <x-stat-card icon="clock-history" label="Temuan {{ $prevYear }}" value="{{ $prevTotal }}" color="secondary" />
    </div>
    <div class="col-lg-3 col-md-6">
        @php
            $delta = $prevTotal > 0 ? round((($currentTotal - $prevTotal) / $prevTotal) * 100, 1) : ($currentTotal > 0 ? 100 : 0);
            $deltaIcon = $currentTotal > $prevTotal ? 'bi-arrow-up-right text-danger' : ($currentTotal < $prevTotal ? 'bi-arrow-down-right text-success' : 'bi-dash text-muted');
        @endphp
        <x-stat-card icon="arrow-up-right" label="Perubahan" value="{{ ($delta > 0 ? '+' : '') . $delta }}%" color="{{ $currentTotal > $prevTotal ? 'danger' : ($currentTotal < $prevTotal ? 'success' : 'secondary') }}" />
    </div>
    <div class="col-lg-3 col-md-6">
        <x-stat-card icon="diagram-3" label="Kesimpulan" value="{{ $currentTotal == $prevTotal ? 'Stabil' : ($currentTotal > $prevTotal ? 'Bertambah' : 'Berkurang') }}" color="{{ $currentTotal > $prevTotal ? 'danger' : ($currentTotal < $prevTotal ? 'success' : 'secondary') }}" />
    </div>
</div>

<p class="small text-muted mb-4">
    Hasil pembandingan <strong>{{ $divisionLabel }}</strong> antara <strong>{{ $year }}</strong> dan <strong>{{ $prevYear }}</strong>:
    temuan <strong>{{ $currentTotal }}</strong> vs <strong>{{ $prevTotal }}</strong>
    (<span class="{{ $currentTotal > $prevTotal ? 'text-danger' : ($currentTotal < $prevTotal ? 'text-success' : 'text-muted') }}">
        {{ $currentTotal == $prevTotal ? 'tidak berubah' : ($currentTotal > $prevTotal ? 'bertambah ' . ($currentTotal - $prevTotal) . ' temuan' : 'berkurang ' . ($prevTotal - $currentTotal) . ' temuan') }}
    </span>).
</p>

{{-- Grafik pertumbuhan + penjelasan --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">[GRAFIK] PERTUMBUHAN TEMUAN PER TAHUN — {{ strtoupper($divisionLabel) }}</div>
            <div class="card-body">
                <div style="position:relative; height:300px;">
                    <canvas id="trendChart"></canvas>
                </div>
                <p class="small text-muted mt-3 mb-0">
                    <strong>Penjelasan:</strong> grafik ini menunjukkan jumlah temuan yang dicatat per tahun pada
                    <strong>{{ $divisionLabel }}</strong>. Tren naik menandakan semakin banyak temuan terungkap
                    (tingkat pengawasan audit meningkat), sedangkan tren turun menandakan kondisi pengendalian
                    internal divisi membaik.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">[GRAFIK] PERBANDINGAN STATUS TEMUAN</div>
            <div class="card-body">
                <div style="position:relative; height:300px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <p class="small text-muted mt-3 mb-0">
                    <strong>Penjelasan:</strong> membandingkan sebaran status temuan antara {{ $year }} (biru)
                    dan {{ $prevYear }} (abu-abu). Jumlah status "Terbuka" tinggi berarti tindak lanjut belum tuntas.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">[GRAFIK] PERBANDINGAN TINGKAT RISIKO</div>
            <div class="card-body">
                <div style="position:relative; height:300px;">
                    <canvas id="riskChart"></canvas>
                </div>
                <p class="small text-muted mt-3 mb-0">
                    <strong>Penjelasan:</strong> membandingkan jumlah temuan berdasarkan tingkat risiko antara
                    {{ $year }} dan {{ $prevYear }}. Berkurangnya temuan "Critical" dan "High" menunjukkan
                    perbaikan efektif atas risiko utama.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">RINCIAN PERBANDINGAN</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Kategori</th>
                                <th class="text-end">{{ $year }}</th>
                                <th class="text-end">{{ $prevYear }}</th>
                                <th class="text-end pe-3">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_merge($riskData, $statusData) as $row)
                                @php
                                    $diff = $row['current'] - $row['prev'];
                                @endphp
                                <tr>
                                    <td class="ps-3">{{ $row['name'] }}</td>
                                    <td class="text-end fw-semibold">{{ $row['current'] }}</td>
                                    <td class="text-end text-muted">{{ $row['prev'] }}</td>
                                    <td class="text-end pe-3 {{ $diff > 0 ? 'text-danger fw-bold' : ($diff < 0 ? 'text-success fw-bold' : 'text-muted') }}">
                                        {{ $diff > 0 ? '+' . $diff : $diff }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const trendRaw = @json($trend);
        const trendYears = Object.keys(trendRaw);

        new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: trendYears,
                datasets: [{
                    label: 'Jumlah Temuan',
                    data: Object.values(trendRaw),
                    borderColor: '#2D6AC7',
                    backgroundColor: 'rgba(45, 106, 199, 0.12)',
                    pointBackgroundColor: '#2D6AC7',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { font: { family: 'IBM Plex Mono', size: 10 } } },
                    x: { ticks: { font: { family: 'IBM Plex Mono', size: 10 } } }
                },
                plugins: { legend: { display: false } }
            }
        });

        const statusMeta = {
            'open': { color: '#3B82F6', label: 'Terbuka' },
            'in_progress': { color: '#F59E0B', label: 'Sedang Berjalan' },
            'waiting_verification': { color: '#EF4444', label: 'Menunggu Verifikasi' },
            'closed': { color: '#10B981', label: 'Ditutup' },
            'rejected': { color: '#6B7280', label: 'Ditolak' }
        };
        const stKeys = Object.keys(statusMeta);
        const statusData = @json($statusData);
        const stLabels = stKeys.map(k => statusMeta[k].label);
        const stCurrent = stKeys.map(k => statusData[k] ? statusData[k].current : 0);
        const stPrev = stKeys.map(k => statusData[k] ? statusData[k].prev : 0);
        const stColor = stKeys.map(k => statusMeta[k].color);

        new Chart(document.getElementById('statusChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: stLabels,
                datasets: [
                    { label: '{{ $year }}', data: stCurrent, backgroundColor: stColor, borderColor: stColor, borderWidth: 1, borderRadius: 6 },
                    { label: '{{ $prevYear }}', data: stPrev, backgroundColor: 'rgba(154, 168, 181, 0.55)', borderColor: '#9AA8B5', borderWidth: 1, borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { font: { family: 'IBM Plex Mono', size: 10 } } },
                    x: { ticks: { font: { family: 'IBM Plex Mono', size: 9 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'IBM Plex Mono', size: 10 } } }
                }
            }
        });

        const riskMeta = {
            'critical': { color: '#c6362b', label: 'Critical' },
            'high': { color: '#EF4444', label: 'High' },
            'medium': { color: '#F59E0B', label: 'Medium' },
            'low': { color: '#059669', label: 'Low' }
        };
        const rkKeys = Object.keys(riskMeta);
        const riskData = @json($riskData);
        const rkLabels = rkKeys.map(k => riskMeta[k].label);
        const rkCurrent = rkKeys.map(k => riskData[k] ? riskData[k].current : 0);
        const rkPrev = rkKeys.map(k => riskData[k] ? riskData[k].prev : 0);
        const rkColor = rkKeys.map(k => riskMeta[k].color);

        new Chart(document.getElementById('riskChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: rkLabels,
                datasets: [
                    { label: '{{ $year }}', data: rkCurrent, backgroundColor: rkColor, borderColor: rkColor, borderWidth: 1, borderRadius: 6 },
                    { label: '{{ $prevYear }}', data: rkPrev, backgroundColor: 'rgba(154, 168, 181, 0.55)', borderColor: '#9AA8B5', borderWidth: 1, borderRadius: 6 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { font: { family: 'IBM Plex Mono', size: 10 } } },
                    x: { ticks: { font: { family: 'IBM Plex Mono', size: 10 } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'IBM Plex Mono', size: 10 } } }
                }
            }
        });
    });
</script>
@endsection