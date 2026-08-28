@extends('layouts.app')

@section('title', 'Detail Tindak Lanjut')

@section('content')
<x-page-header title="Detail Tindak Lanjut">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('action-plans.index') }}" class="text-decoration-none">Tindak Lanjut</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('update', $actionPlan)
            <a href="{{ route('action-plans.edit', $actionPlan) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        @endcan
        @can('delete', $actionPlan)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusActionPlan">
                <i class="bi bi-trash me-2"></i>Hapus
            </button>
        @endcan</x-slot:actions>
</x-page-header>

<x-confirm-modal
    id="hapusActionPlan"
    title="Hapus Rencana Tindak Lanjut?"
    description="Apakah Anda yakin ingin menghapus rencana tindak lanjut ini? Seluruh bukti terkait juga akan terhapus dan tidak dapat dibatalkan."
    :form-action="route('action-plans.destroy', $actionPlan)"
/>

<div class="row g-4">
    @php
        $canKelolaBukti = auth()->user()->role === 'kepala_divisi'
            && auth()->user()->division_id === $actionPlan->finding->auditPlan->division_id;
    @endphp
    <!-- Action Plan Details -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Rencana Tindakan</h5>
                <x-status-badge status="{{ $actionPlan->status }}" />
            </div>
            <div class="card-body">
                <x-detail-list class="mb-4">
                    <x-detail-item label="Judul Tindakan">{{ $actionPlan->title ?: '-' }}</x-detail-item>
                    <x-detail-item label="Target Tanggal Selesai">
                        <span class="text-danger">{{ \Carbon\Carbon::parse($actionPlan->target_date)->format('d M Y') }}</span>
                    </x-detail-item>
                    <x-detail-item label="Temuan Terkait">
                        <a href="{{ route('findings.show', $actionPlan->finding) }}">
                            {{ $actionPlan->finding->finding_number }} - {{ $actionPlan->finding->title }}
                        </a>
                    </x-detail-item>
                </x-detail-list>

                <div class="border-top pt-3">
                    <h6 class="fw-bold">Rencana Tindakan:</h6>
                    <p class="text-muted mb-0" style="white-space: pre-line">{{ $actionPlan->action }}</p>
                </div>
            </div>
        </div>

        <!-- Evidences (Bukti Tindak Lanjut) -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Bukti Tindak Lanjut (Evidences)</h5>
            </div>
            <div class="card-body">
                <!-- Evidences List, dikelompokkan berdasarkan waktu upload -->
                @php
                    $evidencesByDate = $actionPlan->followUpEvidences
                        ->sortByDesc('created_at')
                        ->groupBy(fn ($e) => \Carbon\Carbon::parse($e->created_at)->format('Y-m-d'));
                    $bisaHapus = $canKelolaBukti && in_array($actionPlan->status, ['pending', 'in_progress', 'rejected']);
                @endphp

                @if($bisaHapus)
                    <form action="{{ route('follow-up-evidences.destroy', ['evidence' => '__id__']) }}"
                          method="POST" id="hapusBuktiForm">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button" class="btn btn-outline-danger btn-sm mb-3" id="hapusBuktiBtn" disabled>
                        <i class="bi bi-trash me-1"></i>Hapus Bukti Terpilih (<span id="hapusBuktiCount">0</span>)
                    </button>
                @endif

                @forelse($evidencesByDate as $date => $dateEvidences)
                    <div class="d-flex align-items-center gap-2 mb-3 mt-2">
                        <i class="bi bi-calendar3 text-primary"></i>
                        <span class="fw-bold text-primary">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                        <small class="text-muted">({{ $dateEvidences->count() }} bukti)</small>
                        <hr class="flex-grow-1 mb-0">
                    </div>
                    <div class="row g-3 mb-4">
                        @foreach($dateEvidences as $evidence)
                            <div class="col-md-6">
                                <x-evidence-card
                                    :file="$evidence->file_name"
                                    :type="strtoupper($evidence->file_type)"
                                    :size="$evidence->file_size"
                                    :url="asset('storage/' . $evidence->file_path)"
                                    :download-url="route('evidence.download', $evidence)"
                                    :keterangan="$evidence->keterangan"
                                    :uploader="$evidence->uploadedBy->name ?? null"
                                    :time="\Carbon\Carbon::parse($evidence->created_at)->format('d M Y H:i') . ' WIB'"
                                    icon="bi-file-earmark-check"
                                    modalId="evidencePreviewModal"
                                    :selectable="$bisaHapus && $evidence->uploaded_by === auth()->id()"
                                    :select-value="$evidence->id"
                                />
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-3">
                        Belum ada bukti tindak lanjut diupload.
                    </div>
                @endforelse

                <!-- Upload Form for PIC / Kepala Divisi -->
                @if($canKelolaBukti && in_array($actionPlan->status, ['pending', 'in_progress', 'rejected']))
                    <div class="border-top pt-4">
                        <h6 class="fw-bold text-primary mb-3">Upload Bukti Penyelesaian</h6>
                        <form action="{{ route('action-plans.upload-evidence', $actionPlan) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                            @csrf
                            <div class="col-sm-8">
                                <label for="evidence_file" class="form-label fw-bold">File Bukti <span class="text-danger">*</span></label>
                                <input type="file" name="evidence_file" id="evidence_file" class="form-control @error('evidence_file') is-invalid @enderror" required>
                                <small class="text-muted d-block mt-1">Format dokumen/gambar diperbolehkan. Maksimal 10MB.</small>
                                @error('evidence_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="keterangan" class="form-label fw-bold">Keterangan Perbaikan <span class="text-danger">*</span></label>
                                <textarea name="keterangan" id="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Jelaskan perbaikan yang telah dilakukan..." required>{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-cloud-arrow-up me-1"></i>Upload Bukti
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- Verification Area (SPI saja, SISTEM.md Â§4) -->
        @if(auth()->user()->role === 'spi')
            @if($actionPlan->status === 'submitted')
                <div class="card border-warning">
                    <div class="card-header bg-warning text-white py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Verifikasi Tindak Lanjut</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('action-plans.verify', $actionPlan) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Keputusan Verifikasi <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="result" id="approve" value="approved" required>
                                        <label class="form-check-label text-success fw-bold" for="approve">
                                            <i class="bi bi-check-circle-fill me-1"></i>Setujui & Tutup Temuan
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="result" id="reject" value="rejected">
                                        <label class="form-check-label text-danger fw-bold" for="reject">
                                            <i class="bi bi-x-circle-fill me-1"></i>Tolak & Kembalikan
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label fw-bold">Catatan Verifikasi</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Tuliskan alasan penolakan atau catatan tambahan..."></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-warning text-white">Kirim Keputusan Verifikasi</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Right Sidebar: Verification History / Submit Control -->
    <div class="col-lg-4">
        <!-- Submit Control for PIC / Kepala Divisi -->
        @if($canKelolaBukti && in_array($actionPlan->status, ['in_progress', 'rejected']))
            @if($actionPlan->followUpEvidences->count() > 0)
                <div class="card mb-4 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold">Kirim untuk Verifikasi</h6>
                        <p class="text-muted small">Jika bukti penyelesaian sudah lengkap, kirim rencana tindak lanjut ini ke tim SPI untuk diverifikasi.</p>
                        <form action="{{ route('action-plans.submit', $actionPlan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-send me-1"></i>Kirim Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card mb-4 bg-light">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-info-circle text-warning fs-3 mb-2 d-block"></i>
                        <h6 class="fw-bold">Unggah Bukti Dahulu</h6>
                        <p class="text-muted small mb-0">Anda harus mengunggah minimal satu file bukti penyelesaian sebelum dapat mengirim untuk verifikasi.</p>
                    </div>
                </div>
            @endif
        @endif

        <!-- Verification History -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Riwayat Verifikasi</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($actionPlan->verifications as $verification)
                        <li class="list-group-item px-0 border-0 mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-{{ $verification->result === 'approved' ? 'success' : 'danger' }}">
                                    {{ $verification->result === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($verification->verified_at)->format('d M Y H:i') }}</small>
                            </div>
                            <div class="small mb-1"><strong>Verifikator:</strong> {{ $verification->user->name ?? '-' }}</div>
                            <div class="text-muted small bg-light p-2 rounded">
                                <strong>Catatan:</strong> <span style="white-space: pre-line">{{ $verification->notes ?: 'Tidak ada catatan.' }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center px-0 border-0">Belum ada riwayat verifikasi.</li>
                    @endforelse
                </ul>
            </div>
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

(function () {
    var btn = document.getElementById('hapusBuktiBtn');
    if (!btn) return;
    var countEl = document.getElementById('hapusBuktiCount');
    var form = document.getElementById('hapusBuktiForm');
    var baseAction = form.getAttribute('action');

    function refresh() {
        var selected = document.querySelectorAll('.sdx-evidence-select:checked');
        countEl.textContent = selected.length;
        btn.disabled = selected.length === 0;
    }
    document.querySelectorAll('.sdx-evidence-select').forEach(function (c) {
        c.addEventListener('change', refresh);
    });
    refresh();

    btn.addEventListener('click', function () {
        var selected = document.querySelectorAll('.sdx-evidence-select:checked');
        if (selected.length === 0) return;
        var ids = Array.prototype.map.call(selected, function (c) { return c.value; });
        if (!window.confirm('Hapus ' + ids.length + ' bukti terpilih? Tindakan ini tidak dapat dibatalkan.')) return;
        form.action = baseAction.replace('__id__', ids.join(','));
        form.submit();
    });
})();
</script>
@endsection