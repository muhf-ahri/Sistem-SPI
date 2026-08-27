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
                <div>ROLE: <strong style="color: #10263f;">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</strong></div>
                <div>TANGGAL: {{ now()->format('d.m.Y / H:i') }} WIB</div>
            </div>
        </div>
    </div>

    {{-- Filter Divisi (Hanya untuk Admin/SPI) --}}
    @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'spi')
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('dashboard') }}" class="row align-items-center g-3">
                    <div class="col-auto">
                        <label for="divisi_id" class="form-label mb-0" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; color: #51677e; text-transform: uppercase;">Pilih Divisi:</label>
                    </div>
                    <div class="col-md-4">
                        <select name="divisi_id" id="divisi_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Divisi --</option>
                            @foreach($divisions as $d)
                                <option value="{{ $d->id }}" {{ request('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @php $role = auth()->user()->role; @endphp

    {{-- Peringatan: Kepala Divisi belum terikat divisi --}}
    @if($role === 'kepala_divisi' && !auth()->user()->division_id)
        <div class="card mb-4" style="border-left: 4px solid #c6362b; background: #fff5f4; border-color: #c9d4de;">
            <div class="card-body py-3">
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; letter-spacing: 0.15em; color: #c6362b; text-transform: uppercase;">KONFIGURASI BELUM LENGKAP</span>
                <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 600; color: #10263f;">
                    Divisi Anda belum diatur. Hubungi Super Admin melalui menu Master Data &rarr; Users untuk menetapkan divisi Anda,
                    agar temuan dan tindak lanjut divisinya dapat tampil di sini.
                </div>
            </div>
        </div>
    @endif

    {{-- Banner tindakan: antrean verifikasi SPI --}}
    @if($role === 'spi' && ($pending_verifications ?? 0) > 0)
        <div class="card mb-4" style="border-left: 4px solid #f2913b; background: #fffaf2; border-color: #c9d4de;">
            <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; letter-spacing: 0.15em; color: #b3640f; text-transform: uppercase;">PERLU TINDAKAN</span>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 600; color: #10263f;">
                        {{ $pending_verifications }} tindak lanjut menunggu verifikasi Anda
                    </div>
                </div>
                <a href="{{ route('findings.index', ['status' => 'waiting_verification']) }}" class="btn btn-sm" style="background: #10263f; color: #ffc72c; font-family: 'IBM Plex Mono', monospace;">PERIKSA SEKARANG</a>
            </div>
        </div>
    @endif

    {{-- Banner tindakan: tindak lanjuk ditolak / menunggu verifikasi Kepala Divisi --}}
    @if($role === 'kepala_divisi')
        @if(($followup_rejected ?? 0) > 0 || ($followup_submitted ?? 0) > 0)
            <div class="card mb-4" style="border-left: 4px solid {{ ($followup_rejected ?? 0) > 0 ? '#c6362b' : '#3f7fd4' }}; background: #f7fafc; border-color: #c9d4de;">
                <div class="card-body py-3">
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">STATUS TINDAK LANJUT DIVISI</span>
                    <div class="d-flex gap-3 flex-wrap mt-1" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.8rem;">
                        @if(($followup_in_progress ?? 0) > 0)
                            <span><i class="bi bi-arrow-repeat me-1"></i>Dikerjakan: <strong style="color: #10263f;">{{ $followup_in_progress }}</strong></span>
                        @endif
                        @if(($followup_submitted ?? 0) > 0)
                            <span><i class="bi bi-hourglass-split me-1"></i>Menunggu Verifikasi SPI: <strong style="color: #1e8e52;">{{ $followup_submitted }}</strong></span>
                        @endif
                        @if(($followup_rejected ?? 0) > 0)
                            <span><i class="bi bi-x-octagon me-1"></i>Ditolak - Perlu Perbaikan: <strong style="color: #c6362b;">{{ $followup_rejected }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">01 / JUMLAH TEMUAN</span>
                        <i class="bi bi-tag text-primary" style="font-size: 1.2rem;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #10263f; line-height: 1.1; margin-top: 0.4rem;">
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
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">02 / JUMLAH AKTIF</span>
                        <i class="bi bi-arrow-repeat text-success" style="font-size: 1.2rem;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #1e8e52; line-height: 1.1; margin-top: 0.4rem;">
                        {{ ($total_findings ?? 0) - ($closed_findings ?? 0) }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #51677e; margin-top: 0.3rem;">Belum Ditutup</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #51677e; text-transform: uppercase;">03 / JUMLAH TIDAK AKTIF</span>
                        <i class="bi bi-check2-circle" style="font-size: 1.2rem; color: #51677e;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #51677e; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $closed_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #51677e; margin-top: 0.3rem;">Temuan Selesai (Closed)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #c6362b; text-transform: uppercase;">04 / MELEWATI TENGGAT</span>
                        <i class="bi bi-alarm text-danger" style="font-size: 1.2rem;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 2rem; color: #c6362b; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $overdue_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #c6362b; margin-top: 0.3rem;">Tenggat Terlampaui</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Strip statistik sistem & master data (Super Admin) --}}
    @if($role === 'super_admin')
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card h-100" style="background: #10263f; border-radius: 2px;">
                    <div class="card-body p-3">
                        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #ffc72c; text-transform: uppercase;">USERS AKTIF</div>
                        <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.5rem; color: #ffffff; margin-top: 0.3rem;">
                            {{ $active_users ?? 0 }} <small style="font-size: 0.85rem; color: #9fb2c4;">/ {{ $total_users ?? 0 }} total</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100" style="background: #10263f; border-radius: 2px;">
                    <div class="card-body p-3">
                        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #ffc72c; text-transform: uppercase;">DIVISI TERDAFTAR</div>
                        <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.5rem; color: #ffffff; margin-top: 0.3rem;">{{ $total_divisions ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100" style="background: #10263f; border-radius: 2px;">
                    <div class="card-body p-3">
                        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #ffc72c; text-transform: uppercase;">JENIS PENGAWASAN</div>
                        <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.5rem; color: #ffffff; margin-top: 0.3rem;">{{ $total_audit_types ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100" style="background: #10263f; border-radius: 2px;">
                    <div class="card-body p-3">
                        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #ffc72c; text-transform: uppercase;">KATEGORI TEMUAN</div>
                        <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.5rem; color: #ffffff; margin-top: 0.3rem;">{{ $total_finding_categories ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Distribusi per divisi + aktivitas terbaru --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                    <div class="card-header py-2 px-3 bg-light border-bottom" style="border-color: #c9d4de !important;">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                            [REKAP] PENGAWASAN & TEMUAN PER DIVISI
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                    <tr>
                                        <th class="ps-3">Divisi</th>
                                        <th>Total Pengawasan</th>
                                        <th>Temuan Aktif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($division_stats ?? [] as $stat)
                                        <tr>
                                            <td class="ps-3 fw-bold">{{ $stat->name }} <small style="color: #51677e;">({{ $stat->code }})</small></td>
                                            <td>{{ $stat->audit_plans_count }}</td>
                                            <td>
                                                @if($stat->active_findings_count > 0)
                                                    <span style="font-family: 'IBM Plex Mono', monospace; color: #b3640f;">{{ $stat->active_findings_count }}</span>
                                                @else
                                                    <span style="color: #1e8e52;">0</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Belum ada divisi terdaftar.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($division_stats->hasPages())
                            <div class="card-footer bg-white border-top-0 py-2 px-3">
                                <x-pagination :paginator="$division_stats" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
                    <div class="card-header py-2 px-3 bg-light border-bottom" style="border-color: #c9d4de !important;">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                            [LOG] AKTIVITAS SISTEM TERBARU
                        </span>
                    </div>
                    <div class="card-body p-3" style="max-height: 320px; overflow-y: auto;">
                        @forelse($recent_activities ?? [] as $activity)
                            <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-2">
                                <div>
                                    <span class="fw-bold" style="font-size: 0.82rem;">{{ optional($activity->user)->name ?? 'Sistem' }}</span>
                                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #51677e;">{{ str_replace('_', ' ', $activity->action) }} {{ $activity->entity_type }}</span>
                                </div>
                                <small style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; color: #51677e;">{{ $activity->created_at->format('d.m H:i') }}</small>
                            </div>
                        @empty
                            <p class="text-muted text-center my-3 mb-0" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.8rem;">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Pengawasan aktif yang ditugaskan ke SPI ini --}}
    @if($role === 'spi')
        <div class="card mb-4" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
            <div class="card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center" style="border-color: #c9d4de !important;">
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                    PENGAWASAN AKTIF YANG ANDA TUGASKAN
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                            <tr>
                                <th class="ps-3">Nomor</th>
                                <th>Judul</th>
                                <th>Divisi</th>
                                <th>Mulai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($my_active_audits ?? [] as $audit)
                                <tr>
                                    <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $audit->audit_number }}</td>
                                    <td class="fw-bold"><a href="{{ route('audit-plans.show', $audit) }}" class="text-decoration-none" style="color: #10263f;">{{ $audit->title }}</a></td>
                                    <td>{{ $audit->division->name ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($audit->start_date)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Anda tidak ditugaskan pada pengawasan aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

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
                    @if($role === 'management' && isset($risk_levels))
                        <div class="d-flex gap-3 flex-wrap mt-3 pt-2 border-top" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem;">
                            <span><span style="display:inline-block;width:9px;height:9px;background:#7a1f1a;margin-right:5px;"></span>CRITICAL: <strong>{{ $risk_levels['critical'] ?? 0 }}</strong></span>
                            <span><span style="display:inline-block;width:9px;height:9px;background:#c6362b;margin-right:5px;"></span>HIGH: <strong>{{ $risk_levels['high'] ?? 0 }}</strong></span>
                            <span><span style="display:inline-block;width:9px;height:9px;background:#f2913b;margin-right:5px;"></span>MEDIUM: <strong>{{ $risk_levels['medium'] ?? 0 }}</strong></span>
                            <span><span style="display:inline-block;width:9px;height:9px;background:#27a35f;margin-right:5px;"></span>LOW: <strong>{{ $risk_levels['low'] ?? 0 }}</strong></span>
                        </div>
                    @endif
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
