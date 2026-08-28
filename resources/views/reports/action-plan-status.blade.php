@extends('layouts.app')

@section('title', 'Laporan Tindak Lanjut')

@section('content')
<x-page-header title="Laporan Status Tindak Lanjut">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Laporan</a></li>
                <li class="breadcrumb-item active">Tindak Lanjut</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>
        <div class="d-flex gap-2">
            @include('reports._export-buttons', ['type' => 'action-plan-status'])
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-2"></i>Cetak
            </button>
        </div>
    </x-slot:actions>
</x-page-header>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.action-plan-status') }}" class="row g-3">
            <div class="col-md-5">
                <label for="status" class="form-label small text-muted">Status Tindak Lanjut</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach(['pending', 'in_progress', 'submitted', 'verified', 'rejected', 'completed'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Total: {{ $actionPlans->count() }} Tindak Lanjut</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Temuan</th>
                        <th>Rencana Aksi</th>
                        <th>PIC</th>
                        <th>Divisi</th>
                        <th>Target Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actionPlans as $plan)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $plan->finding->finding_number ?? '-' }}</td>
                            <td>{{ Str::limit($plan->action, 60) }}</td>
                            <td>{{ $plan->pic->name ?? '-' }}</td>
                            <td>{{ $plan->finding->auditPlan->division->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($plan->target_date)->format('d M Y') }}</td>
                            <td><x-status-badge status="{{ $plan->status }}" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-repeat fs-1 d-block mb-3"></i>
                                Tidak ada data tindak lanjut sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection