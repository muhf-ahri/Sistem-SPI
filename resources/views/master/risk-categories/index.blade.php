@extends('layouts.app')

@section('title', 'Manajemen Kategori Risiko')

@section('content')
<x-page-header title="Manajemen Kategori Risiko">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Kategori Risiko</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\RiskCategory::class)
    <a href="{{ route('master.risk-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-shield-exclamation me-2"></i>Tambah Kategori Risiko
    </a>
    @endcan</x-slot:actions>
</x-page-header>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nama Kategori</th>
                        <th>Level Risiko</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riskCategories as $category)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $category->name }}</td>
                            <td><x-risk-badge level="{{ $category->level }}" /></td>
                            <td>{{ Str::limit($category->description, 70) }}</td>
                            <td>
                                <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }}">
                                    {{ $category->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @can('update', $category)
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('master.risk-categories.edit', $category) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @can('delete', $category)
                                    <button type="button" class="btn btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#hapus{{ $category->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <x-confirm-modal id="hapus{{ $category->id }}" title="Konfirmasi Hapus" description="Apakah Anda yakin ingin menghapus kategori risiko ini?" :form-action="route('master.risk-categories.destroy', $category)" />
                                    @endcan
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-shield fs-1 d-block mb-3"></i>
                                Belum ada data kategori risiko.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($riskCategories->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$riskCategories" />
        </div>
    @endif
</div>
@endsection