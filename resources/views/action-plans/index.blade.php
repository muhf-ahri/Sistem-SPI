@extends('layouts.app')

@section('title', 'Daftar Tindak Lanjut')

@section('content')
<x-page-header title="Rencana & Realisasi Tindak Lanjut">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active">Tindak Lanjut</li>
            </ol>
    </x-slot:breadcrumb>
</x-page-header>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('action-plans.index') }}" class="row g-3">
            <div class="col-md-6">
                <label for="status" class="form-label small text-muted">Status Tindak Lanjut</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('action-plans.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
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
                        <th>Rencana Aksi</th>
                        <th>PIC</th>
                        <th>Divisi</th>
                        <th>Target Selesai</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actionPlans as $plan)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('findings.show', $plan->finding) }}" class="text-decoration-none fw-bold">
                                    {{ $plan->finding->finding_number }}
                                </a>
                            </td>
                            <td>{{ Str::limit($plan->action, 60) }}</td>
                            <td>{{ $plan->pic->name ?? '-' }}</td>
                            <td>{{ $plan->finding->auditPlan->division->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($plan->target_date)->format('d M Y') }}</td>
                            <td>
                                <x-status-badge status="{{ $plan->status }}" />
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('action-plans.show', $plan) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-repeat fs-1 d-block mb-3"></i>
                                Belum ada rencana tindak lanjut yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($actionPlans->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$actionPlans" />
        </div>
    @endif
</div>
@endsection