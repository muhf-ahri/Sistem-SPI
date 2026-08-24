@extends('layouts.app')

@section('title', 'Manajemen Jenis Pengawasan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Manajemen Jenis Pengawasan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Jenis Pengawasan</li>
            </ol>
        </nav>
    </div>
    @can('create', App\Models\AuditType::class)
    <a href="{{ route('master.audit-types.create') }}" class="btn btn-primary">
        <i class="bi bi-clipboard-plus me-2"></i>Tambah Jenis
    </a>
    @endcan
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Jenis Pengawasan</th>
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
                                    <form action="{{ route('master.audit-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis pengawasan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard fs-1 d-block mb-3"></i>
                                Belum ada data jenis pengawasan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($auditTypes->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $auditTypes->links() }}
        </div>
    @endif
</div>
@endsection