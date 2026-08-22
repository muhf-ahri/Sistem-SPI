@extends('layouts.app')

@section('title', 'Detail Temuan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Detail Temuan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('findings.index') }}" class="text-decoration-none">Temuan</a></li>
                <li class="breadcrumb-item active">{{ $finding->finding_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @can('update', $finding)
            <a href="{{ route('findings.edit', $finding) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Edit Temuan
            </a>
        @endcan
        @can('delete', $finding)
            <form action="{{ route('findings.destroy', $finding) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus temuan ini?')">
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
    <!-- Finding Info & Recommendation -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Informasi Temuan</h5>
                <x-status-badge status="{{ $finding->status }}" />
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small">NO. TEMUAN</div>
                        <div class="fw-bold">{{ $finding->finding_number }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">JUDUL TEMUAN</div>
                        <div class="fw-bold">{{ $finding->title }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">KATEGORI</div>
                        <div class="fw-bold">{{ $finding->category->name ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">TINGKAT RISIKO</div>
                        <div><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">BATAS WAKTU</div>
                        <div class="fw-bold text-danger">{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">PENGAWASAN ASAL</div>
                        <div>
                            <a href="{{ route('audit-plans.show', $finding->auditPlan) }}" class="text-decoration-none fw-bold">
                                {{ $finding->auditPlan->audit_number }} - {{ $finding->auditPlan->title }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mb-3">
                    <h6 class="fw-bold">Deskripsi Temuan:</h6>
                    <p class="text-muted mb-0">{{ $finding->description }}</p>
                </div>

                <div class="border-top pt-3">
                    <h6 class="fw-bold">Rekomendasi Perbaikan:</h6>
                    <p class="text-muted mb-0">{{ $finding->recommendation ?: 'Tidak ada rekomendasi.' }}</p>
                </div>
            </div>
        </div>

        <!-- Action Plans (Tindak Lanjut) -->
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Rencana Tindak Lanjut</h5>
                @can('create', App\Models\ActionPlan::class)
                    <a href="{{ route('action-plans.create', ['finding_id' => $finding->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Buat Tindak Lanjut
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">PIC</th>
                                <th>Rencana Aksi</th>
                                <th>Target Selesai</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($finding->actionPlans as $actionPlan)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $actionPlan->pic->name ?? '-' }}</td>
                                    <td>{{ Str::limit($actionPlan->action, 60) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($actionPlan->target_date)->format('d M Y') }}</td>
                                    <td>
                                        <x-status-badge status="{{ $actionPlan->status }}" />
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('action-plans.show', $actionPlan) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada rencana tindak lanjut dibuat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Metadata & Statistics Sidebar -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Informasi Penugasan</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">DIVISI TERPERIKSA</span>
                    <strong>{{ $finding->auditPlan->division->name ?? '-' }} ({{ $finding->auditPlan->division->code ?? '-' }})</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">DICATAT OLEH</span>
                    <strong>{{ $finding->createdBy->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-muted small d-block">TANGGAL DICATAT</span>
                    <strong>{{ $finding->created_at->format('d M Y H:i') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection