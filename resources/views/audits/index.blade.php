@extends('layouts.app')

@section('title', 'Daftar Pengawasan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Daftar Pengawasan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengawasan</li>
            </ol>
        </nav>
    </div>
    @can('create', App\Models\AuditPlan::class)
        <a href="{{ route('audit-plans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Pengawasan
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Pengawasan</th>
                        <th>Judul</th>
                        <th>Divisi</th>
                        <th>Jenis</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditPlans as $plan)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $plan->audit_number }}</td>
                            <td>{{ $plan->title }}</td>
                            <td>{{ $plan->division->name ?? '-' }}</td>
                            <td>{{ $plan->auditType->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($plan->start_date)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($plan->end_date)->format('d M Y') }}</td>
                            <td>
                                <x-status-badge status="{{ $plan->status }}" />
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('audit-plans.show', $plan) }}" class="btn btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $plan)
                                        <a href="{{ route('audit-plans.edit', $plan) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $plan)
                                        <form action="{{ route('audit-plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengawasan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                                Belum ada data pengawasan yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($auditPlans->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $auditPlans->links() }}
        </div>
    @endif
</div>
@endsection