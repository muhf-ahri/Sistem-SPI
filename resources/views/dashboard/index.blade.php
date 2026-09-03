@extends('layouts.app')

@section('title', 'Dashboard - Lembar Kontrol SPI')

@section('content')
    <div class="mb-4 pb-2 border-bottom border-2" style="border-color: #c9d4de !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.68rem; letter-spacing: 0.22em; color: #51677e; text-transform: uppercase;">
                    <span style="display:inline-block; width:10px; height:10px; background:#ffc72c; margin-right:6px;"></span>
                    PANEL AUDIT & RUANG KENDALI
                </div>
                <h1 style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; text-transform: uppercase; color: #10263f; margin: 0.2rem 0 0;">
                    RINGKASAN STATUS KENDALI
                </h1>
            </div>
            <div class="text-end" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.72rem; color: #51677e;">
                <div>AKSES: <strong style="color: #10263f;">{{ strtoupper(auth()->user()->name) }}</strong></div>
                <div>PERAN: <strong style="color: #10263f;">
                    @php
                        $roleLabel = [
                            'super_admin'   => 'Super Admin',
                            'spi'           => 'SPI',
                            'kepala_divisi' => 'Kepala Divisi',
                            'auditor'       => 'Auditor',
                        ][auth()->user()->role] ?? auth()->user()->role;
                    @endphp
                    {{ strtoupper($roleLabel) }}
                </strong></div>
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
                        <label for="year" class="form-label mb-0" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.75rem; color: #51677e; text-transform: uppercase;">Tahun:</label>
                    </div>
                    <div class="col-auto">
                        <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Tahun --</option>
                            @foreach($years ?? [] as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
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
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'audit_done'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #10B981; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #10B981; text-transform: uppercase;">01 / AUDIT SELESAI</span>
                        <i class="bi bi-check2-circle" style="font-size: 1.2rem; color: #10B981;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #10B981; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $reported_audits ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Pemeriksaan Selesai</div>
                </div>
            </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'audit_ongoing'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #3B82F6; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #3B82F6; text-transform: uppercase;">02 / AUDIT BERLANGSUNG</span>
                        <i class="bi bi-play-circle" style="font-size: 1.2rem; color: #3B82F6;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #3B82F6; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $in_progress_audits ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Dalam Tahap Pemeriksaan</div>
                </div>
            </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'audit_pending'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #6B7280; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #6B7280; text-transform: uppercase;">03 / BELUM DIAUDIT</span>
                        <i class="bi bi-hourglass-split" style="font-size: 1.2rem; color: #6B7280;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #6B7280; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $scheduled_audits ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Pemeriksaan Belum Dimulai</div>
                </div>
            </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'finding_total'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #6366F1; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #6366F1; text-transform: uppercase;">04 / TOTAL TEMUAN</span>
                        <i class="bi bi-tag" style="font-size: 1.2rem; color: #6366F1;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #6366F1; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $total_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Total Masalah</div>
                </div>
            </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'finding_open'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #EF4444; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #EF4444; text-transform: uppercase;">05 / BELUM DITINDAKLANJUTI</span>
                        <i class="bi bi-x-octagon" style="font-size: 1.2rem; color: #EF4444;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #EF4444; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $open_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Masalah Belum Diperbaiki</div>
                </div>
            </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'finding_progress'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #F59E0B; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #F59E0B; text-transform: uppercase;">06 / DITINDAKLANJUTI SBGN</span>
                        <i class="bi bi-arrow-repeat" style="font-size: 1.2rem; color: #F59E0B;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #F59E0B; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $in_progress_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Sedang Dalam Pengerjaan</div>
                </div>
            </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('dashboard', array_merge(request()->except('kpi'), ['kpi' => 'finding_closed'])) }}" class="text-decoration-none d-block h-100">
            <div class="card h-100" style="background: #ffffff; border: 1.5px solid #059669; border-radius: 2px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #059669; text-transform: uppercase;">07 / SELESAI DITINDAKLANJUTI</span>
                        <i class="bi bi-check2-all" style="font-size: 1.2rem; color: #059669;"></i>
                    </div>
                    <div style="font-family: 'Chakra Petch', sans-serif; font-weight: 700; font-size: 1.8rem; color: #059669; line-height: 1.1; margin-top: 0.4rem;">
                        {{ $closed_findings ?? 0 }}
                    </div>
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.63rem; color: #51677e; margin-top: 0.3rem;">Masalah Sudah Diperbaiki</div>
                </div>
            </div>
            </a>
        </div>
    </div>

    {{-- Table detail KPI (muncul saat card KPI diklik) --}}
    @if($selected_kpi)
        <div class="card mb-4" style="background: #ffffff; border: 1.5px solid #c9d4de; border-radius: 2px;">
            <div class="card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center" style="border-color: #c9d4de !important;">
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; font-weight: 600; color: #10263f; letter-spacing: 0.1em; text-transform: uppercase;">
                    DETAIL KPI: {{ $kpi_title ?? '' }}
                </span>
                <a href="{{ route('dashboard', request()->except(['kpi', 'kpi_page'])) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Tutup
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    @if($selected_kpi === 'audit_done')
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">Nomor</th>
                                    <th>Divisi</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Durasi Pengerjaan</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpi_audits as $audit)
                                    @php $dur = $audit->working_days; @endphp
                                    <tr>
                                        <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $audit->audit_number }}</td>
                                        <td>{{ $audit->division->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($audit->start_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($audit->end_date)->format('d M Y') }}</td>
                                        <td>{{ $dur !== null ? $dur . ' hari kerja' : \App\Support\WorkingDayCalculator::countWorkingDays($audit->start_date, $audit->end_date) . ' hari kerja' }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('audit-plans.show', $audit) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data Audit.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif($selected_kpi === 'audit_ongoing')
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">Nomor</th>
                                    <th>Divisi</th>
                                    <th>Mulai</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpi_audits as $audit)
                                    <tr>
                                        <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $audit->audit_number }}</td>
                                        <td>{{ $audit->division->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($audit->start_date)->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('audit-plans.show', $audit) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data Audit.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif($selected_kpi === 'audit_pending')
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">Nomor</th>
                                    <th>Divisi</th>
                                    <th>Tgl Mulai</th>
                                    <th>Tgl Selesai</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpi_audits as $audit)
                                    <tr>
                                        <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $audit->audit_number }}</td>
                                        <td>{{ $audit->division->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($audit->start_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($audit->end_date)->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <form action="{{ route('audit-plans.start-inspection', $audit) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Mulai"><i class="bi bi-play-fill"></i></button>
                                            </form>
                                            <a href="{{ route('audit-plans.show', $audit) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data Audit.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif($selected_kpi === 'finding_total' || $selected_kpi === 'finding_open')
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">No. Temuan</th>
                                    <th>Divisi</th>
                                    <th>Batas Waktu</th>
                                    <th>Risiko</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpi_findings as $finding)
                                    <tr>
                                        <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $finding->finding_number }}</td>
                                        <td>{{ $finding->auditPlan->division->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}</td>
                                        <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('findings.show', $finding) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data Temuan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif($selected_kpi === 'finding_progress')
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">No. Temuan</th>
                                    <th>Divisi</th>
                                    <th>Tgl Pengerjaan</th>
                                    <th>Risiko</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpi_findings as $finding)
                                    <tr>
                                        <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $finding->finding_number }}</td>
                                        <td>{{ $finding->auditPlan->division->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($finding->updated_at)->format('d M Y') }}</td>
                                        <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('findings.show', $finding) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data Temuan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif($selected_kpi === 'finding_closed')
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                <tr>
                                    <th class="ps-3">No. Temuan</th>
                                    <th>Divisi</th>
                                    <th>Tgl Pengerjaan</th>
                                    <th>Batas Waktu</th>
                                    <th class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kpi_findings as $finding)
                                    <tr>
                                        <td class="ps-3" style="font-family: 'IBM Plex Mono', monospace;">{{ $finding->finding_number }}</td>
                                        <td>{{ $finding->auditPlan->division->name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($finding->updated_at)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('findings.show', $finding) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data Temuan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
                @if($kpi_type === 'audit' && $kpi_audits->hasPages())
                    <div class="card-footer bg-white border-top-0 py-2 px-3">
                        <x-pagination :paginator="$kpi_audits" />
                    </div>
                @elseif($kpi_type === 'finding' && $kpi_findings->hasPages())
                    <div class="card-footer bg-white border-top-0 py-2 px-3">
                        <x-pagination :paginator="$kpi_findings" />
                    </div>
                @endif
            </div>
        </div>
    @endif
    {{-- Kalender mini --}}
    @php $isAdmin = ($role === 'super_admin'); $mcal = $mini_calendar ?? null; @endphp
    @if($mcal)
    @php $mSched = $mcal['schedule']; $dayUrl = route('calendar.index', ['month' => $mcal['month'], 'year' => $mcal['year']]); @endphp
    <style>
        .mcal-wrap { display:flex; align-items:stretch; gap:0; }
        .mcal-left { flex:0 0 62%; min-width:0; }
        .mcal-right { flex:1 1 auto; min-width:210px; border-left:1.5px solid #c9d4de; }
        .mcal-grab { flex:0 0 14px; cursor:col-resize; display:grid; place-items:center; user-select:none; position:relative; }
        .mcal-grab::before { content:""; position:absolute; top:8px; bottom:8px; width:3px; border-radius:3px; background:rgba(16,38,63,.15); transition:background .15s ease; }
        .mcal-grab:hover::before, .mcal-grab.grabbing::before { background:#ffc72c; }
        .mcal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
        .mcell { text-align:center; padding:.4rem 0; position:relative; font-family:'Chakra Petch',sans-serif; font-size:.78rem; border-radius:2px; color:#a7b4c2; cursor:pointer; user-select:none; }
        .mcell.in { color:#10263f; font-weight:600; }
        .mcell.off { background:#fdf6e6; color:#b3640f; }
        .mcell.today { background:#10263f !important; color:#ffc72c !important; font-weight:700; }
        .mcell.sel { outline:2px solid #ffc72c; outline-offset:-2px; box-shadow:0 0 0 2px rgba(255,199,44,.3); }
        .mcell.locked { cursor:default; }
        .mcell .mdot { position:absolute; bottom:2px; left:0; right:0; text-align:center; }
        .mcell .mdot i { display:inline-block; width:5px; height:5px; border-radius:2px; background:#3f7fd4; }

        .mdetail { max-height:260px; overflow-y:auto; padding:.5rem .7rem; }
        .mdetail-sel { font-family:'IBM Plex Mono',monospace; font-size:.66rem; letter-spacing:.08em; text-transform:uppercase; color:#51677e; margin-bottom:.4rem; }
        .mdetail-sel strong { color:#10263f; font-family:'Chakra Petch',sans-serif; font-size:.95rem; font-weight:700; }
        .mdetail-item { border-bottom:1px dashed #dde5ec; padding:.45rem 0; }
        .mdetail-item:last-child { border-bottom:none; }
        .mdetail-date { font-family:'Chakra Petch',sans-serif; font-weight:700; font-size:.78rem; color:#10263f; }
        .mdetail-date small { font-family:'IBM Plex Mono',monospace; font-size:.54rem; font-weight:500; letter-spacing:.04em; color:#51677e; text-transform:uppercase; display:block; }
        .mdetail-ev { display:flex; align-items:center; gap:.35rem; margin-top:.2rem; }
        .mchip { font-family:'IBM Plex Mono',monospace; font-size:.52rem; font-weight:600; letter-spacing:.03em; padding:0 5px; border-radius:2px; color:#fff; flex:0 0 auto; }
        .mlink { color:#2c62b8; font-weight:600; font-size:.72rem; text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .mlink:hover { text-decoration:underline; text-decoration-color:#ffc72c; text-decoration-thickness:2px; text-underline-offset:2px; }
        .mdiv { color:#51677e; font-size:.66rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>

    <div class="row g-3 mb-4">
        <div class="col-12">
            {{-- Kalender dalam satu card (kiri: hari&tanggal, kanan: detail jadwal) --}}
            <div class="card">
                <div class="card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center" style="border-color:#c9d4de !important;">
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:0.68rem;font-weight:600;color:#10263f;letter-spacing:0.1em;text-transform:uppercase;">
                        <i class="bi bi-calendar3 me-1"></i>Kalender {{ strtoupper($mcal['monthLabel']) }}
                    </span>
                    <div>
                        <span class="text-muted me-2" style="font-family:'IBM Plex Mono',monospace;font-size:.6rem;text-transform:uppercase;">Seret pemisah</span>
                        @if($isAdmin)<a href="{{ $dayUrl }}" class="btn btn-sm btn-outline-secondary" title="Lihat kalender lengkap"><i class="bi bi-arrows-fullscreen"></i></a>@endif
                    </div>
                </div>
                <div class="card-body p-2 mcal-wrap" id="mcalWrap">
                    <div class="mcal-left" id="mcalLeft">
                        <div class="d-grid" style="grid-template-columns:repeat(7,1fr);gap:2px;font-family:'IBM Plex Mono',monospace;font-size:0.58rem;letter-spacing:.04em;color:#51677e;text-transform:uppercase;text-align:center;margin-bottom:4px;">
                            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                        </div>
                        <div class="mcal-grid" id="mcalGrid">
                            @foreach($mcal['weeks'] as $week)
                                @foreach($week as $cell)
                                    <div class="mcell {{ $cell['inMonth'] ? 'in' : '' }} {{ $cell['isHoliday'] ? 'off' : '' }} {{ $cell['isToday'] ? 'today' : '' }} {{ $cell['inMonth'] ? '' : 'locked' }}"
                                         data-date="{{ $cell['date'] }}" data-inmonth="{{ $cell['inMonth'] ? 1 : 0 }}" title="{{ $cell['inMonth'] ? 'Klik untuk lihat jadwal tanggal ini' : '' }}">
                                        @if($cell['inMonth'] && $cell['marker'])<span class="mdot"><i></i></span>@endif
                                        {{ $cell['day'] }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <div class="mcal-grab" id="mcalGrab" title="Seret untuk mengatur lebar"><i class="bi bi-grip-vertical" style="color:#9fb0bf;font-size:.85rem;"></i></div>

                    <div class="mcal-right" id="mcalRight">
                        <div class="mdetail">
                            <div class="mdetail-sel"><i class="bi bi-calendar-day me-1"></i>Detail <strong id="mcalSelDate">-</strong></div>
                            <div id="mcalSelBody"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
    // Tapis detail jadwal kalender mini: tampil per hari yang dipilih
    (function () {
        var sched = @json($mSched ?? []);
        var map = {};
        sched.forEach(function (row) { map[row.date] = row.events || []; });

        var body = document.getElementById('mcalSelBody');
        var dateEl = document.getElementById('mcalSelDate');
        var grid = document.getElementById('mcalGrid');
        if (!body || !grid) return;

        var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        function fmtDate(yMd) {
            var p = yMd.split('-');
            var d = new Date(+p[0], +p[1] - 1, +p[2]);
            return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ' <small>' + days[d.getDay()] + '</small>';
        }

        function chipColor(ev) {
            if (ev.type === 'finding') return '#d8493c';
            if (ev.type === 'audit_end') return '#6c7a89';
            return '#3f7fd4';
        }
        function chipIcon(ev) {
            if (ev.type === 'audit') return 'bi-clipboard-check';
            if (ev.type === 'audit_end') return 'bi-flag-fill';
            return 'bi-exclamation-triangle-fill';
        }
        function chipTxt(ev) {
            if (ev.type === 'audit') return 'AUDIT';
            if (ev.type === 'audit_end') return 'SELESAI';
            return 'TEMUAN';
        }

        function render(date) {
            dateEl.innerHTML = fmtDate(date);
            var evts = map[date] || [];
            if (!evts.length) {
                body.innerHTML = '<div class="text-center text-muted py-4" style="font-family:\'IBM Plex Mono\',monospace;font-size:.7rem;">Tidak ada jadwal pada tanggal ini.</div>';
                return;
            }
            var html = '';
            evts.forEach(function (ev) {
                html += '<div class="mdetail-item">'
                    + '<div class="mdetail-ev"><span class="mchip" style="background:' + chipColor(ev) + '"><i class="bi ' + chipIcon(ev) + '"></i> ' + chipTxt(ev) + '</span>'
                    + '<a href="' + ev.url + '" class="mlink">' + ev.label + '</a></div>'
                    + '<div class="mdiv ms-1">' + ev.division + '</div></div>';
            });
            body.innerHTML = html;
        }

        // Default: tanggal hari ini bila berjadwal, jika tidak tanggal berjadwal pertama
        var cells = grid.querySelectorAll('.mcell[data-inmonth="1"]');
        var today = null, first = null;
        cells.forEach(function (c) {
            if (!first && map[c.getAttribute('data-date')]) first = c;
            if (c.classList.contains('today')) today = c;
        });
        var target = today && first ? today : (first || (today || cells[0]));
        var selDate = target ? target.getAttribute('data-date') : null;
        if (selDate) render(selDate);

        function select(c) {
            cells.forEach(function (x) { x.classList.remove('sel'); });
            c.classList.add('sel');
            render(c.getAttribute('data-date'));
        }
        cells.forEach(function (c) {
            c.addEventListener('click', function () { select(c); });
        });
        if (target) target.classList.add('sel');
    })();
    </script>



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
                        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 0.65rem; letter-spacing: 0.15em; color: #ffc72c; text-transform: uppercase;">JENIS Audit</div>
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
                            [REKAP] Audit & TEMUAN PER DIVISI
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead class="table-light" style="font-family: 'IBM Plex Mono', monospace; font-size: 0.7rem; text-transform: uppercase;">
                                    <tr>
                                        <th class="ps-3">Divisi</th>
                                        <th>Total Audit</th>
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
            
            const riskColors = {
                'critical': { bg: '#7a1f1a', border: '#571511', label: 'CRITICAL' },
                'high':     { bg: '#c6362b', border: '#96231a', label: 'HIGH' },
                'medium':   { bg: '#f2913b', border: '#b8661b', label: 'MEDIUM' },
                'low':      { bg: '#27a35f', border: '#1b7443', label: 'LOW' }
            };

            const riskKeys = Object.keys(riskData);
            const riskLabels = riskKeys.map(k => (riskColors[k.toLowerCase()] ? riskColors[k.toLowerCase()].label : k.toUpperCase()));
            const riskBgColors = riskKeys.map(k => (riskColors[k.toLowerCase()] ? riskColors[k.toLowerCase()].bg : '#10263f'));
            const riskBorderColors = riskKeys.map(k => (riskColors[k.toLowerCase()] ? riskColors[k.toLowerCase()].border : '#10263f'));

            new Chart(riskCtx, {
                type: 'bar',
                data: {
                    labels: riskLabels,
                    datasets: [{
                        label: 'Jumlah Temuan',
                        data: Object.values(riskData),
                        backgroundColor: riskBgColors,
                        borderColor: riskBorderColors,
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

    <script>
    // Resize manual kalender mini di dashboard (di-grab)
    (function () {
        var wrap = document.getElementById('mcalWrap');
        var left = document.getElementById('mcalLeft');
        var grab = document.getElementById('mcalGrab');
        if (!wrap || !left || !grab) return;

        var minL = 45, maxL = 85;

        function setL(p) {
            if (p < minL) p = minL;
            if (p > maxL) p = maxL;
            left.style.flex = '0 0 ' + p + '%';
            try { localStorage.setItem('mcalLeftPct', p); } catch (e) {}
        }
        try {
            var s = parseFloat(localStorage.getItem('mcalLeftPct'));
            if (!isNaN(s) && s >= minL && s <= maxL) setL(s);
        } catch (e) {}

        var g = false;
        grab.addEventListener('mousedown', function (e) {
            g = true; grab.classList.add('grabbing');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            e.preventDefault();
        });
        document.addEventListener('mousemove', function (e) {
            if (!g) return;
            var r = wrap.getBoundingClientRect();
            setL(((e.clientX - r.left) / r.width) * 100);
        });
        document.addEventListener('mouseup', function () {
            if (!g) return;
            g = false; grab.classList.remove('grabbing');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        });
    })();
    </script>
@endsection
