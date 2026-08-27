@extends('layouts.app')

@section('title', 'Detail Pemeriksaan')

@section('content')
<x-page-header title="Detail Pemeriksaan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}" class="text-decoration-none">Pemeriksaan</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('update', $inspection)
            <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        @endcan
        @can('delete', $inspection)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusInspection">
                <i class="bi bi-trash me-2"></i>Hapus
            </button>
        @endcan</x-slot:actions>
</x-page-header>

<x-confirm-modal
    id="hapusInspection"
    title="Hapus Pemeriksaan?"
    description="Apakah Anda yakin ingin menghapus pemeriksaan ini? Seluruh bukti pemeriksaan juga akan terhapus dan tidak dapat dibatalkan."
    :form-action="route('inspections.destroy', $inspection)"
/>

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
                <x-detail-list class="mb-4">
                    <x-detail-item label="Tanggal Pemeriksaan">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d M Y') }}</x-detail-item>
                    <x-detail-item label="Auditor Penanggung Jawab">{{ $inspection->auditor->name ?? '-' }}</x-detail-item>
                    <x-detail-item label="Pengawasan Terkait">
                        <a href="{{ route('audit-plans.show', $inspection->auditPlan) }}">
                            {{ $inspection->auditPlan->audit_number }} - {{ $inspection->auditPlan->title }}
                        </a>
                    </x-detail-item>
                </x-detail-list>

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
                            <x-evidence-card
                                :file="$evidence->file_name"
                                :type="strtoupper($evidence->file_type)"
                                :size="$evidence->file_size"
                                :url="asset('storage/' . $evidence->file_path)"
                                :download-url="asset('storage/' . $evidence->file_path)"
                                icon="bi-file-earmark-arrow-up"
                                modalId="evidencePreviewModal"
                            />
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
                        <form action="{{ route('inspections.upload-evidence', $inspection) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex align-items-center gap-2">
                                <input type="file" name="evidence_file" class="form-control" required>
                                <button type="submit" class="btn btn-primary text-nowrap flex-shrink-0">
                                    <i class="bi bi-cloud-arrow-up me-1"></i>Upload Bukti
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Upload gambar, dokumen PDF, xlsx, atau docx. Maksimal 10MB.</small>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Findings from this Inspection -->
        @php
            // Hasil pemeriksaan yang memerlukan perbaikan → dorong pembuatan temuan
            $perluPerbaikan = in_array($inspection->result, ['needs_improvement', 'unsatisfactory']);
            $bisaBuatTemuan = $perluPerbaikan
                && $inspection->auditPlan->status === 'in_progress'
                && auth()->user()->can('create', App\Models\Finding::class);
        @endphp
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Temuan dari Kunjungan Ini</h5>
                @if($bisaBuatTemuan)
                    <a href="{{ route('findings.create', ['audit_plan_id' => $inspection->audit_plan_id, 'inspection_id' => $inspection->id]) }}"
                       class="btn btn-sm btn-danger" title="Catat temuan dari pemeriksaan ini">
                        <i class="bi bi-plus-lg me-1"></i>Buat Temuan
                    </a>
                @endif
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
                        <li class="list-group-item text-center py-3">
                            @if($perluPerbaikan)
                                <i class="bi bi-exclamation-triangle text-warning fs-4 d-block mb-2"></i>
                                <p class="text-muted small mb-2">
                                    Hasil pemeriksaan <strong>{{ ucwords(str_replace('_', ' ', $inspection->result)) }}</strong> &mdash;
                                    perlu perbaikan, namun belum ada temuan yang dicatat dari kunjungan ini.
                                </p>
                                @if($bisaBuatTemuan)
                                    <a href="{{ route('findings.create', ['audit_plan_id' => $inspection->audit_plan_id, 'inspection_id' => $inspection->id]) }}" class="btn btn-sm btn-danger">
                                        <i class="bi bi-plus-lg me-1"></i>Buat Temuan Sekarang
                                    </a>
                                @elseif($inspection->auditPlan->status !== 'in_progress')
                                    <p class="small text-muted mb-0">Mulai pemeriksaan pada pengawasan untuk dapat mencatat temuan.</p>
                                @endif
                            @else
                                <span class="text-muted">Tidak ada temuan yang dikaitkan dengan kunjungan ini.</span>
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>
            @if($inspection->findings->isNotEmpty())
                <div class="card-footer bg-white text-center py-2">
                    <a href="{{ route('audit-plans.show', $inspection->audit_plan_id) }}" class="small text-decoration-none">
                        Lihat daftar temuan lengkap di halaman pengawasan <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Preview Bukti -->
<div class="modal fade" id="evidencePreviewModal" tabindex="-1" aria-labelledby="evidencePreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="evidencePreviewLabel" style="font-family: var(--font-mono); font-size: .75rem; letter-spacing: .1em; text-transform: uppercase;">Pratinjau Bukti</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0 text-center" id="evidencePreviewBody">
                <div class="p-4 text-muted">Memuat pratinjau...</div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-sm btn-outline-secondary" id="evidencePreviewDownload" target="_blank"><i class="bi bi-download me-1"></i>Unduh</a>
                <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    #evidencePreviewModal .modal-body img { max-width: 100%; max-height: 70vh; display: block; margin: 0 auto; }
    #evidencePreviewModal .modal-body iframe,
    #evidencePreviewModal .modal-body embed { width: 100%; height: 70vh; border: none; }
    #evidencePreviewModal .modal-body .sdx-unsupported { padding: 3rem 1rem; }
    #evidencePreviewModal .modal-body .sdx-unsupported i { font-size: 2.5rem; color: var(--baja); }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('evidencePreviewModal');
    var body = document.getElementById('evidencePreviewBody');
    var dlBtn = document.getElementById('evidencePreviewDownload');

    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        var url = btn.getAttribute('data-url');
        var type = btn.getAttribute('data-type');
        var file = btn.getAttribute('data-file');

        dlBtn.href = url;

        var imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (imageTypes.indexOf(type) !== -1) {
            body.innerHTML = '<img src="' + url + '" alt="' + file + '">';
        } else if (type === 'pdf') {
            body.innerHTML = '<iframe src="' + url + '#toolbar=1" title="' + file + '"></iframe>';
        } else {
            body.innerHTML = '<div class="sdx-unsupported py-5">' +
                '<i class="bi bi-file-earmark d-block mb-3"></i>' +
                '<p class="mb-2 fw-bold">' + file + '</p>' +
                '<p class="text-muted small mb-3">Format <strong>.' + type.toUpperCase() + '</strong> tidak dapat dipratinjau langsung di browser.</p>' +
                '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Buka di Tab Baru</a>' +
                '</div>';
        }
    });

    modal.addEventListener('hidden.bs.modal', function () {
        body.innerHTML = '<div class="p-4 text-muted">Memuat pratinjau...</div>';
    });
});
</script>
@endsection