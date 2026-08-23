@extends('layouts.app')

@section('title', 'Dashboard - Lembar Kontrol SPI')

@section('content')
    <div class="mb-4 pb-2 border-bottom border-2" style="border-color: #c9d4de !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; letter-spacing: 0.22em; color: #51677e; text-transform: uppercase;">
                    <span style="display:inline-block; width:10px; height:10px; background:#ffc72c; margin-right:6px;"></span>
                    PANEL PENGAWASAN & CONTROL ROOM
                </div>
                <h1 style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; text-transform: uppercase; color: #10263f; margin: 0.2rem 0 0;">
                    RINGKASAN STATUS KENDALI
                </h1>
            </div>
            <div class="text-end" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #51677e;">
                <div>AKSES: <strong style="color: #10263f;">{{ strtoupper(auth()->user()->name) }}</strong></div>
                <div>TANGGAL: {{ now()->format('d.m.Y / H:i') }} WIB</div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">01 / TOTAL AUDIT</span>
                        <i class="bi bi-clipboard-check text-primary" style="font-size: 1.2rem;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #10263f; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $total_audits ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #51677e; margin-top: 0.3rem;">Rencana & Pelaksanaan</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">02 / AKTIF</span>
                        <i class="bi bi-play-circle text-success" style="font-size: 1.2rem;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #1e8e52; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $active_audits ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #51677e; margin-top: 0.3rem;">Sedang Berlangsung</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">03 / TEMUAN</span>
                        <i class="bi bi-exclamation-triangle" style="font-size: 1.2rem; color: #b3640f;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #b3640f; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $total_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #51677e; margin-top: 0.3rem;">Total Kasus Tercatat</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #c6362b; text-transform: uppercase;">04 / OVERDUE</span>
                        <i class="bi bi-alarm text-danger" style="font-size: 1.2rem;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #c6362b; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $overdue_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #c6362b; margin-top: 0.3rem;">Melewati Tenggat</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-header py-2 px-3 bg-light border-bottom" style="border-color: #c9d4de !important;">
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                        [GRAFIK] DISTRIBUSI STATUS TEMUAN
                    </span>
                </div>
                <div class="card-body p-3">
                    <canvas id="statusChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-header py-2 px-3 bg-light border-bottom" style="border-color: #c9d4de !important;">
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                        [GRAFIK] KLASIFIKASI TINGKAT RISIKO
                    </span>
                </div>
                <div class="card-body p-3">
                    <canvas id="riskChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Penyelesaian + Temuan Terbaru -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-header py-2 px-3 bg-light border-bottom" style="border-color: #c9d4de !important;">
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                        PENYELESAIAN TEMUAN
                    </span>
                </div>
                <div class="card-body p-3">
                    @isset($total_findings)
                        <div class="d-flex flex-column gap-3">
                            <x-progress label="Temuan selesai" :value="$closed_findings" :max="$total_findings" tone="#1e8e52" />
                            <x-progress label="Berisiko tinggi" :value="$high_risk_findings" :max="$total_findings" tone="#c6362b" />
                            <x-progress label="Masih terbuka" :value="$open_findings" :max="$total_findings" tone="#f2913b" />
                        </div>
                    @else
                        <p class="text-muted text-center my-3 mb-0" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.8rem;">Data belum tersedia untuk peran Anda.</p>
                    @endisset
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-header py-2 px-3 bg-light border-bottom" style="border-color: #c9d4de !important;">
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                        TEMUAN TERBARU
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">Judul Temuan</th>
                                    <th>Divisi</th>
                                    <th>Risiko</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_findings ?? [] as $finding)
                                    <tr>
                                        <td class="ps-3 fw-bold">
                                            <a href="{{ route('findings.show', $finding) }}" class="text-decoration-none" style="color: #10263f;">{{ $finding->title }}</a>
                                        </td>
                                        <td><span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.78rem;">{{ $finding->auditPlan->division->name ?? '-' }}</span></td>
                                        <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                                        <td><x-status-badge status="{{ $finding->status }}" /></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.8rem;">Belum ada temuan yang dicatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = @json($status_chart_data ?? []);
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData).map(s => s.toUpperCase()),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#f2913b', '#3f7fd4', '#27a35f', '#c6362b', '#10263f'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { font: { family: 'IBM Plex Mono', size: 11 } } }
                    }
                }
            });

            // Risk Chart
            const riskCtx = document.getElementById('riskChart').getContext('2d');
            const riskData = @json($risk_chart_data ?? []);
            new Chart(riskCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(riskData),
                    datasets: [{
                        label: 'Jumlah Temuan',
                        data: Object.values(riskData),
                        backgroundColor: '#10263f',
                        borderColor: '#ffc72c',
                        borderWidth: 1.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { family: 'IBM Plex Mono', size: 10 } } },
                        x: { ticks: { font: { family: 'IBM Plex Mono', size: 10 } } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
@endsection
