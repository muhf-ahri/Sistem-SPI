@extends('layouts.app')

@section('title', 'Daftar Pengawasan')

@section('content')
<x-page-header title="Daftar Pengawasan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengawasan</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\AuditPlan::class)
        <a href="{{ route('audit-plans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Pengawasan
        </a>
    @endcan</x-slot:actions>
</x-page-header>

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
                                        <button type="button" class="btn btn-outline-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#hapus{{ $plan->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <x-confirm-modal id="hapus{{ $plan->id }}" title="Konfirmasi Hapus" description="Apakah Anda yakin ingin menghapus pengawasan ini?" :form-action="route('audit-plans.destroy', $plan)" />
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
            <x-pagination :paginator="$auditPlans" />
        </div>
    @endif
</div>
@endsection