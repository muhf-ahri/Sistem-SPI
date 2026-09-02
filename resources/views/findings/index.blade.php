@extends('layouts.app')

@section('title', 'Daftar Temuan')

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

<x-page-header title="Daftar Temuan Audit">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Temuan</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>@can('create', App\Models\Finding::class)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTemuanModal" {{ $auditPlans->isEmpty() ? 'disabled' : '' }}>
            <i class="bi bi-plus-lg me-2"></i>Tambah Temuan
        </button>
    @endcan</x-slot:actions>
</x-page-header>

@can('create', App\Models\Finding::class)
<!-- Modal: Pilih Audit untuk Tambah Temuan -->
<div class="modal fade" id="tambahTemuanModal" tabindex="-1" aria-labelledby="tambahTemuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="GET" action="{{ route('findings.create') }}">
                <div class="modal-body p-4">
                    <h5 class="mb-2" id="tambahTemuanModalLabel" style="font-family: var(--font-display, 'Chakra Petch', sans-serif); font-weight: 700; text-transform: uppercase; letter-spacing: .01em; color: var(--tinta, #10263f);">Tambah Temuan Baru</h5>
                    <p class="text-muted mb-3" style="font-size: .87rem;">Temuan selalu terikat pada suatu Audit. Pilih Audit untuk mulai mencatat temuan.</p>
                    <label for="audit_plan_id" class="form-label small text-muted">Audit</label>
                    <select name="audit_plan_id" id="audit_plan_id" class="form-select" required>
                        <option value="">-- Pilih Audit --</option>
                        @foreach($auditPlans as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Lanjut</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Filter & Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('findings.index') }}" class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari no. temuan, judul, no. Audit, divisi...">
            </div>
            <div class="col-lg-2 col-md-6">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="risk" class="form-label small text-muted">Tingkat Risiko</label>
                <select name="risk" id="risk" class="form-select form-select-sm">
                    <option value="">-- Semua Risiko --</option>
                    @foreach($risks as $risk)
                        <option value="{{ $risk }}" {{ request('risk') == $risk ? 'selected' : '' }}>{{ ucfirst($risk) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="division" class="form-label small text-muted">Divisi Terperiksa</label>
                <select name="division" id="division" class="form-select form-select-sm">
                    <option value="">-- Semua Divisi --</option>
                    @foreach($divisions as $id => $name)
                        <option value="{{ $id }}" {{ request('division') == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                <a href="{{ route('findings.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">
                            <a class="sdx-sort {{ $currentSort === 'finding_number' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'finding_number', 'direction' => $toggleDir('finding_number'), 'page' => 1]) }}">
                                No. Temuan <i class="bi {{ $iconFor('finding_number') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'title' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => $toggleDir('title'), 'page' => 1]) }}">
                                Judul <i class="bi {{ $iconFor('title') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'plan' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'plan', 'direction' => $toggleDir('plan'), 'page' => 1]) }}">
                                No. Audit <i class="bi {{ $iconFor('plan') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'division' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'division', 'direction' => $toggleDir('division'), 'page' => 1]) }}">
                                Divisi <i class="bi {{ $iconFor('division') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'deadline' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'deadline', 'direction' => $toggleDir('deadline'), 'page' => 1]) }}">
                                Batas Waktu <i class="bi {{ $iconFor('deadline') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'risk' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'risk', 'direction' => $toggleDir('risk'), 'page' => 1]) }}">
                                Risiko <i class="bi {{ $iconFor('risk') }}"></i>
                            </a>
                        </th>
                        <th>
                            <a class="sdx-sort {{ $currentSort === 'status' ? 'sorted' : '' }}"
                               href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => $toggleDir('status'), 'page' => 1]) }}">
                                Status <i class="bi {{ $iconFor('status') }}"></i>
                            </a>
                        </th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findings as $finding)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $finding->finding_number }}</td>
                            <td>{{ $finding->title }}</td>
                            <td>
                                <a href="{{ route('audit-plans.show', $finding->auditPlan) }}" class="text-decoration-none fw-semibold">
                                    {{ $finding->auditPlan->audit_number }}
                                </a>
                            </td>
                            <td>{{ $finding->auditPlan->division->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}</td>
                            <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                            <td><x-status-badge status="{{ $finding->status }}" /></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('findings.show', $finding) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-exclamation-circle fs-1 d-block mb-3"></i>
                                Belum ada temuan yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($findings->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$findings" />
        </div>
    @endif
</div>
@endsection