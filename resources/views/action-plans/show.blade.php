@extends('layouts.app')

@section('title', 'Detail Tindak Lanjut')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Detail Tindak Lanjut</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('action-plans.index') }}" class="text-decoration-none">Tindak Lanjut</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @can('update', $actionPlan)
            <a href="{{ route('action-plans.edit', $actionPlan) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        @endcan
        @can('delete', $actionPlan)
            <form action="{{ route('action-plans.destroy', $actionPlan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rencana tindak lanjut ini?')">
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
    @php
        $canKelolaBukti = auth()->id() === $actionPlan->pic_user_id
            || (auth()->user()->role === 'kepala_divisi'
                && auth()->user()->division_id === $actionPlan->finding->auditPlan->division_id);
    @endphp
    <!-- Action Plan Details -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Rencana Tindakan</h5>
                <x-status-badge status="{{ $actionPlan->status }}" />
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small">PIC TUGAS</div>
                        <div class="fw-bold">{{ $actionPlan->pic->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">TARGET TANGGAL SELESAI</div>
                        <div class="fw-bold text-danger">{{ \Carbon\Carbon::parse($actionPlan->target_date)->format('d M Y') }}</div>
                    </div>
                    <div class="col-sm-12">
                        <div class="text-muted small">TEMUAN TERKAIT</div>
                        <div>
                            <a href="{{ route('findings.show', $actionPlan->finding) }}" class="text-decoration-none fw-bold">
                                {{ $actionPlan->finding->finding_number }} - {{ $actionPlan->finding->title }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3">
                    <h6 class="fw-bold">Rencana Tindakan:</h6>
                    <p class="text-muted mb-0">{{ $actionPlan->action }}</p>
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
                @endphp

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
                                <div class="card h-100 bg-light border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                                <i class="bi bi-file-earmark-check fs-4"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-truncate" title="{{ $evidence->file_name }}">{{ $evidence->file_name }}</div>
                                                <small class="text-muted d-block">Tipe: {{ strtoupper($evidence->file_type) }} | Ukuran: {{ number_format($evidence->file_size / 1024, 2) }} KB</small>
                                                <small class="text-muted d-block">Oleh: {{ $evidence->uploadedBy->name ?? '-' }}</small>
                                                <small class="text-muted d-block">Diupload: {{ \Carbon\Carbon::parse($evidence->created_at)->format('d M Y H:i') }} WIB</small>
                                                <a href="{{ asset('storage/' . $evidence->file_path) }}" target="_blank" class="btn btn-sm btn-link p-0 mt-1">
                                                    <i class="bi bi-download me-1"></i>Unduh Bukti
                                                </a>
                                            </div>
                                        </div>
                                        @if($evidence->keterangan)
                                            <div class="border-top pt-2 mt-2">
                                                <small class="text-muted d-block fw-bold"><i class="bi bi-card-text me-1"></i>Keterangan Perbaikan:</small>
                                                <p class="small text-muted mb-0">{{ $evidence->keterangan }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
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

        <!-- Verification Area (SPI saja, SISTEM.md §4) -->
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
                                <strong>Catatan:</strong> {{ $verification->notes ?: 'Tidak ada catatan.' }}
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
@endsection