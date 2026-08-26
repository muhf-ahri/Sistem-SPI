@extends('layouts.app')

@section('title', 'Detail Pengawasan')

@section('content')
<x-page-header title="Detail Pengawasan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('audit-plans.index') }}" class="text-decoration-none">Pengawasan</a></li>
                <li class="breadcrumb-item active">{{ $auditPlan->audit_number }}</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>
        <div class="d-flex justify-content-end gap-2">
            @can('update', $auditPlan)
                <a href="{{ route('audit-plans.edit', $auditPlan) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
            @endcan
            @if(in_array($auditPlan->status, ['draft', 'scheduled']))
                @can('startInspection', $auditPlan)
                    <form action="{{ route('audit-plans.start-inspection', $auditPlan) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bi bi-play-fill me-2"></i>Mulai Pemeriksaan
                        </button>
                    </form>
                @endcan
            @endif
            @if($auditPlan->status === 'in_progress')
                @can('complete', $auditPlan)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#selesaikanAuditPlan">
                        <i class="bi bi-check-circle me-2"></i>Selesaikan Pengawasan
                    </button>
                @endcan
            @endif
        </div>
    </x-slot:actions>
</x-page-header>

<x-confirm-modal
    id="selesaikanAuditPlan"
    title="Selesaikan Pengawasan?"
    description="Selesaikan pengawasan ini? Pastikan seluruh pemeriksaan dan temuan sudah terekap sebelum menutup pengawasan."
    confirm-text="Ya, Selesaikan"
    method="POST"
    :form-action="route('audit-plans.complete', $auditPlan)"
/>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Informasi Pengawasan</h5>
                <x-status-badge status="{{ $auditPlan->status }}" />
            </div>
            <div class="card-body">
                <x-detail-list class="mb-4">
                    <x-detail-item label="No. Pengawasan">{{ $auditPlan->audit_number }}</x-detail-item>
                    <x-detail-item label="Judul">{{ $auditPlan->title }}</x-detail-item>
                    <x-detail-item label="Divisi Terperiksa">{{ $auditPlan->division->name ?? '-' }}</x-detail-item>
                    <x-detail-item label="Jenis Pengawasan">{{ $auditPlan->auditType->name ?? '-' }}</x-detail-item>
                    <x-detail-item label="Tanggal Mulai">{{ \Carbon\Carbon::parse($auditPlan->start_date)->format('d M Y') }}</x-detail-item>
                    <x-detail-item label="Tanggal Selesai">{{ \Carbon\Carbon::parse($auditPlan->end_date)->format('d M Y') }}</x-detail-item>
                </x-detail-list>

                <div class="border-top pt-3">
                    <h6 class="fw-bold">Deskripsi / Ruang Lingkup:</h6>
                    <p class="mb-0 text-muted">{{ $auditPlan->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>
            </div>
        </div>

        <!-- Inspections -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Pemeriksaan / Kunjungan Lapangan</h5>
                @if(in_array($auditPlan->status, ['in_progress', 'scheduled']))
                    @can('create', App\Models\Inspection::class)
                        <a href="{{ route('inspections.create', ['audit_plan_id' => $auditPlan->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Pemeriksaan
                        </a>
                    @endcan
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Tanggal</th>
                                <th>Auditor</th>
                                <th>Ringkasan</th>
                                <th>Hasil</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditPlan->inspections as $inspection)
                                <tr>
                                    <td class="ps-4">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d M Y') }}</td>
                                    <td>{{ $inspection->auditor->name ?? '-' }}</td>
                                    <td>{{ Str::limit($inspection->summary, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $inspection->result === 'satisfactory' ? 'success' : ($inspection->result === 'needs_improvement' ? 'warning' : 'danger') }}">
                                            {{ ucwords(str_replace('_', ' ', $inspection->result)) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada pemeriksaan dilakukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Findings -->
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">Daftar Temuan</h5>
                @if(in_array($auditPlan->status, ['in_progress']))
                    @can('create', App\Models\Finding::class)
                        <a href="{{ route('findings.create', ['audit_plan_id' => $auditPlan->id]) }}" class="btn btn-sm btn-danger">
                            <i class="bi bi-plus-lg me-1"></i>Buat Temuan
                        </a>
                    @endcan
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No. Temuan</th>
                                <th>Judul</th>
                                <th>Risiko</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditPlan->findings as $finding)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $finding->finding_number }}</td>
                                    <td>{{ $finding->title }}</td>
                                    <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                                    <td><x-status-badge status="{{ $finding->status }}" /></td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('findings.show', $finding) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada temuan dicatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info / Auditor -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Auditor Ditugaskan</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($auditPlan->assignments as $assignment)
                        <li class="list-group-item d-flex align-items-center gap-3 px-0">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $assignment->user->name ?? '-' }}</div>
                                <small class="text-muted text-uppercase">{{ str_replace('_', ' ', $assignment->role) }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center px-0">Belum ada auditor ditugaskan.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Metadata</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">DIBUAT OLEH</span>
                    <strong>{{ $auditPlan->createdBy->name ?? '-' }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">TANGGAL DIBUAT</span>
                    <strong>{{ $auditPlan->created_at->format('d M Y H:i') }}</strong>
                </div>
                <div>
                    <span class="text-muted small d-block">TERAKHIR DIPERBARUI</span>
                    <strong>{{ $auditPlan->updated_at->format('d M Y H:i') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection