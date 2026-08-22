@extends('layouts.app')

@section('title', 'Daftar Pemeriksaan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Pemeriksaan / Kunjungan Lapangan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Pemeriksaan</li>
            </ol>
        </nav>
    </div>
    @can('create', App\Models\Inspection::class)
        <a href="{{ route('inspections.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Pemeriksaan
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
                        <th>Auditor</th>
                        <th>Tanggal Pemeriksaan</th>
                        <th>Ringkasan</th>
                        <th>Hasil</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspections as $inspection)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('audit-plans.show', $inspection->auditPlan) }}" class="text-decoration-none fw-bold">
                                    {{ $inspection->auditPlan->audit_number }}
                                </a>
                            </td>
                            <td>{{ $inspection->auditor->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d M Y') }}</td>
                            <td>{{ Str::limit($inspection->summary, 60) }}</td>
                            <td>
                                <span class="badge bg-{{ $inspection->result === 'satisfactory' ? 'success' : ($inspection->result === 'needs_improvement' ? 'warning' : 'danger') }}">
                                    {{ ucwords(str_replace('_', ' ', $inspection->result)) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('update', $inspection)
                                        <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $inspection)
                                        <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemeriksaan ini?')">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard2-x fs-1 d-block mb-3"></i>
                                Belum ada kunjungan pemeriksaan dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($inspections->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $inspections->links() }}
        </div>
    @endif
</div>
@endsection