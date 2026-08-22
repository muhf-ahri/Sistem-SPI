@extends('layouts.app')

@section('title', 'Detail Pemeriksaan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Detail Pemeriksaan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}" class="text-decoration-none">Pemeriksaan</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @can('update', $inspection)
            <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        @endcan
        @can('delete', $inspection)
            <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemeriksaan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-2"></i>Hapus
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="row g-4">
    <!-- Main Info & Findings -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Kunjungan / Pemeriksaan Lapangan</h5>
                <span class="badge bg-{{ $inspection->result === 'satisfactory' ? 'success' : ($inspection->result === 'needs_improvement' ? 'warning' : 'danger') }}">
                    {{ ucwords(str_replace('_', ' ', $inspection->result)) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small">TANGGAL PEMERIKSAAN</div>
                        <div class="fw-bold">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d M Y') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">AUDITOR PENANGGUNG JAWAB</div>
                        <div class="fw-bold">{{ $inspection->auditor->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-12">
                        <div class="text-muted small">PENGAWASAN TERKAIT</div>
                        <div>
                            <a href="{{ route('audit-plans.show', $inspection->auditPlan) }}" class="text-decoration-none fw-bold">
                                {{ $inspection->auditPlan->audit_number }} - {{ $inspection->auditPlan->title }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mb-3">
                    <h6 class="fw-bold">Ringkasan Hasil Pemeriksaan:</h6>
                    <p class="text-muted mb-0">{{ $inspection->summary }}</p>
                </div>

                <div class="border-top pt-3">
                    <h6 class="fw-bold">Catatan Auditor:</h6>
                    <p class="text-muted mb-0">{{ $inspection->notes ?: 'Tidak ada catatan internal.' }}</p>
                </div>
            </div>
        </div>

        <!-- Evidences (Bukti Pemeriksaan) -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Bukti Pemeriksaan (Evidence)</h5>
            </div>
            <div class="card-body">
                <!-- Evidences List -->
                <div class="row g-3 mb-4">
                    @forelse($inspection->evidences as $evidence)
                        <div class="col-md-6">
                            <div class="card h-100 bg-light border-0 shadow-sm">
                                <div class="card-body p-3 d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                        <i class="bi bi-file-earmark-arrow-up fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="fw-bold text-truncate" title="{{ $evidence->file_name }}">{{ $evidence->file_name }}</div>
                                        <small class="text-muted d-block">Tipe: {{ strtoupper($evidence->file_type) }} | Ukuran: {{ number_format($evidence->file_size / 1024, 2) }} KB</small>
                                        <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn btn-sm btn-link p-0 mt-1">
                                            <i class="bi bi-download me-1"></i>Unduh Bukti
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-3">
                            Belum ada bukti pemeriksaan fisik diupload.
                        </div>
                    @endforelse
                </div>

                <!-- Upload Form for SPI -->
                @can('update', $inspection)
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-primary mb-3">Upload Bukti Pemeriksaan Baru</h6>
                        <form action="{{ route('inspections.upload-evidence', $inspection) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                            @csrf
                            <div class="col-sm-8">
                                <input type="file" name="evidence_file" class="form-control" required>
                                <small class="text-muted d-block mt-1">Upload gambar, dokumen PDF, xlsx, atau docx. Maksimal 10MB.</small>
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-cloud-arrow-up me-1"></i>Upload Bukti
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Findings from this Inspection -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Temuan dari Kunjungan Ini</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($inspection->findings as $finding)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between mb-1">
                                <a href="{{ route('findings.show', $finding) }}" class="fw-bold text-decoration-none">{{ $finding->finding_number }}</a>
                                <x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" />
                            </div>
                            <div class="small text-muted">{{ $finding->title }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Tidak ada temuan yang dikaitkan dengan kunjungan ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection