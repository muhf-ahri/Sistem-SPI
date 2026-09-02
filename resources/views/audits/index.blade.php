@extends('layouts.app')

@section('title', 'Daftar Audit')

@section('styles')
<style>
    /* Header kolom bisa diklik untuk sortir */
    .sdx-sort {
        color: inherit;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        white-space: nowrap;
    }
    .sdx-sort i {
        font-size: .68rem;
        opacity: .4;
        transition: opacity .15s ease, color .15s ease;
    }
    .sdx-sort:hover { color: var(--tinta); }
    .sdx-sort:hover i { opacity: .85; }
    .sdx-sort.sorted {
        color: var(--tinta);
        box-shadow: inset 0 -2px 0 var(--kuning);
    }
    .sdx-sort.sorted i { opacity: 1; color: var(--tinta); }
</style>
@endsection

@section('content')
@php
    $currentSort = request('sort', 'created_at');
    $currentDir  = request('direction', 'desc');
    $toggleDir   = fn ($col) => ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
    $iconFor     = fn ($col) => $currentSort === $col
        ? ($currentDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill')
        : 'bi-arrow-down-up';
@endphp

<x-page-header title="Daftar Audit">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Audit</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\AuditPlan::class)
        <a href="{{ route('audit-plans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Audit
        </a>
    @endcan</x-slot:actions>
</x-page-header>

<!-- Filter & Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-plans.index') }}" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari no. Audit, judul, divisi, jenis...">
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="division" class="form-label small text-muted">Divisi</label>
                <select name="division" id="division" class="form-select form-select-sm">
                    <option value="">-- Semua Divisi --</option>
                    @foreach($divisions as $id => $name)
                        <option value="{{ $id }}" {{ request('division') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-4">
                <label for="year" class="form-label small text-muted">Tahun</label>
                <select name="year" id="year" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-12 d-flex flex-wrap align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a href="{{ route('audit-plans.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1">Reset</a>
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
                        <th class="ps-4">
                            <a class="sdx-sort {{ $currentSort === 'audit_number' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'audit_number', 'direction' => $toggleDir('audit_number'), 'page' => 1]) }}">
                                No. Audit <i class="bi {{ $iconFor('audit_number') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'title' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => $toggleDir('title'), 'page' => 1]) }}">
                                Judul <i class="bi {{ $iconFor('title') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'division' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'division', 'direction' => $toggleDir('division'), 'page' => 1]) }}">
                                Divisi <i class="bi {{ $iconFor('division') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'type' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'type', 'direction' => $toggleDir('type'), 'page' => 1]) }}">
                                Jenis <i class="bi {{ $iconFor('type') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'start_date' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'start_date', 'direction' => $toggleDir('start_date'), 'page' => 1]) }}">
                                Tanggal Mulai <i class="bi {{ $iconFor('start_date') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'end_date' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'end_date', 'direction' => $toggleDir('end_date'), 'page' => 1]) }}">
                                Tanggal Selesai <i class="bi {{ $iconFor('end_date') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'status' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => $toggleDir('status'), 'page' => 1]) }}">
                                Status <i class="bi {{ $iconFor('status') }}"></i>
                            </a>
                        </th>
                        <th class="text-left pe-4">Aksi</th>
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
                                    <a href="{{ route('audit-plans.show', $plan) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(in_array($plan->status, ['draft', 'scheduled']))
                                        @can('startInspection', $plan)
                                            <button type="button" class="btn btn-outline-warning" title="Mulai Pemeriksaan" data-bs-toggle="modal" data-bs-target="#mulai{{ $plan->id }}">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                            <x-confirm-modal
                                                id="mulai{{ $plan->id }}"
                                                title="Mulai Pemeriksaan?"
                                                description="Status Audit akan diubah menjadi In Progress dan pemeriksaan lapangan dapat dicatat."
                                                confirm-text="Ya, Mulai"
                                                confirm-class="btn-warning"
                                                method="POST"
                                                :form-action="route('audit-plans.start-inspection', $plan)"
                                            />
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                                Belum ada data Audit yang sesuai.
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
