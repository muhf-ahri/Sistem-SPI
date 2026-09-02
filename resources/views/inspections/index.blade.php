@extends('layouts.app')

@section('title', 'Daftar Pemeriksaan')

@section('content')
<x-page-header title="Pemeriksaan / Kunjungan Lapangan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Pemeriksaan</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\Inspection::class)
        <a href="{{ route('inspections.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Tambah Pemeriksaan
        </a>
    @endcan</x-slot:actions>
</x-page-header>

<!-- Filter & Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('inspections.index') }}" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari no. Audit, ringkasan, auditor...">
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="auditor" class="form-label small text-muted">Auditor</label>
                <select name="auditor" id="auditor" class="form-select form-select-sm">
                    <option value="">-- Semua Auditor --</option>
                    @foreach($auditors as $id => $name)
                        <option value="{{ $id }}" {{ request('auditor') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="result" class="form-label small text-muted">Hasil</label>
                <select name="result" id="result" class="form-select form-select-sm">
                    <option value="">-- Semua Hasil --</option>
                    @foreach(['satisfactory' => 'Satisfactory', 'needs_improvement' => 'Needs Improvement', 'unsatisfactory' => 'Unsatisfactory'] as $val => $label)
                        <option value="{{ $val }}" {{ request('result') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="year" class="form-label small text-muted">Tahun</label>
                <select name="year" id="year" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-4 d-flex flex-wrap align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a href="{{ route('inspections.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1">Reset</a>
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
                        <th class="ps-4">No. Audit</th>
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
                                        <button type="button" class="btn btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#hapus{{ $inspection->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <x-confirm-modal id="hapus{{ $inspection->id }}" title="Konfirmasi Hapus" description="Apakah Anda yakin ingin menghapus pemeriksaan ini?" :form-action="route('inspections.destroy', $inspection)" />
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
            <x-pagination :paginator="$inspections" />
        </div>
    @endif
</div>
@endsection