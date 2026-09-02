@extends('layouts.app')

@section('title', 'Manajemen Jenis Audit')

@section('content')
<x-page-header title="Manajemen Jenis Audit">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Jenis Audit</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\AuditType::class)
    <a href="{{ route('master.audit-types.create') }}" class="btn btn-primary">
        <i class="bi bi-clipboard-plus me-2"></i>Tambah Jenis
    </a>
    @endcan</x-slot:actions>
</x-page-header>

<!-- Filter & Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('master.audit-types.index') }}" class="row g-3">
            <div class="col-md-8">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari jenis Audit...">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a href="{{ route('master.audit-types.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Jenis Audit</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditTypes as $type)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $type->name }}</td>
                            <td>{{ Str::limit($type->description, 80) }}</td>
                            <td>
                                <span class="badge bg-{{ $type->is_active ? 'success' : 'danger' }}">
                                    {{ $type->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @can('update', $type)
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master.audit-types.edit', $type) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @can('delete', $type)
                                    <button type="button" class="btn btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#hapus{{ $type->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <x-confirm-modal id="hapus{{ $type->id }}" title="Konfirmasi Hapus" description="Apakah Anda yakin ingin menghapus jenis Audit ini?" :form-action="route('master.audit-types.destroy', $type)" />
                                    @endcan
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard fs-1 d-block mb-3"></i>
                                Belum ada data jenis Audit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($auditTypes->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$auditTypes" />
        </div>
    @endif
</div>
@endsection