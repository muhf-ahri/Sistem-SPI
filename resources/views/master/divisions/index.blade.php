@extends('layouts.app')

@section('title', 'Manajemen Divisi')

@section('content')
<x-page-header title="Manajemen Divisi">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Divisi</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\Division::class)
    <a href="{{ route('master.divisions.create') }}" class="btn btn-primary">
        <i class="bi bi-building-add me-2"></i>Tambah Divisi
    </a>
    @endcan</x-slot:actions>
</x-page-header>

<!-- Filter & Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('master.divisions.index') }}" class="row g-3">
            <div class="col-md-8">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari kode, nama, deskripsi divisi...">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a href="{{ route('master.divisions.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
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
                        <th class="ps-4">Kode</th>
                        <th>Nama Divisi</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisions as $division)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $division->code }}</td>
                            <td>{{ $division->name }}</td>
                            <td>{{ Str::limit($division->description, 70) }}</td>
                            <td>
                                <span class="badge bg-{{ $division->is_active ? 'success' : 'danger' }}">
                                    {{ $division->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @can('update', $division)
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master.divisions.edit', $division) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @can('delete', $division)
                                    <button type="button" class="btn btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#hapus{{ $division->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <x-confirm-modal id="hapus{{ $division->id }}" title="Konfirmasi Hapus" description="Apakah Anda yakin ingin menghapus divisi ini?" :form-action="route('master.divisions.destroy', $division)" />
                                    @endcan
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-building fs-1 d-block mb-3"></i>
                                Belum ada data divisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($divisions->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$divisions" />
        </div>
    @endif
</div>
@endsection