@extends('layouts.app')

@section('title', 'Daftar Temuan')

@section('content')
<x-page-header title="Daftar Temuan Pengawasan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Temuan</li>
            </ol>
    </x-slot:breadcrumb>
</x-page-header>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('findings.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label small text-muted">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="risk" class="form-label small text-muted">Tingkat Risiko</label>
                <select name="risk" id="risk" class="form-select form-select-sm">
                    <option value="">-- Semua Risiko --</option>
                    @foreach($risks as $risk)
                        <option value="{{ $risk }}" {{ request('risk') == $risk ? 'selected' : '' }}>{{ ucfirst($risk) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="division" class="form-label small text-muted">Divisi Terperiksa</label>
                <select name="division" id="division" class="form-select form-select-sm">
                    <option value="">-- Semua Divisi --</option>
                    @foreach($divisions as $id => $name)
                        <option value="{{ $id }}" {{ request('division') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('findings.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
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
                        <th class="ps-4">No. Temuan</th>
                        <th>Judul</th>
                        <th>No. Pengawasan</th>
                        <th>Divisi</th>
                        <th>Batas Waktu</th>
                        <th>Risiko</th>
                        <th>Status</th>
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