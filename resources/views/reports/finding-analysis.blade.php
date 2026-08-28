@extends('layouts.app')

@section('title', 'Laporan Temuan')

@section('content')
<x-page-header title="Laporan Analisis Temuan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Laporan</a></li>
                <li class="breadcrumb-item active">Temuan</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>
        <div class="d-flex gap-2">
            @include('reports._export-buttons', ['type' => 'finding-analysis'])
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-2"></i>Cetak
            </button>
        </div>
    </x-slot:actions>
</x-page-header>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.finding-analysis') }}" class="row g-3">
            <div class="col-md-5">
                <label for="division" class="form-label small text-muted">Divisi</label>
                <select name="division" id="division" class="form-select form-select-sm">
                    <option value="">-- Semua Divisi --</option>
                    @foreach(\App\Models\Division::where('is_active', true)->pluck('name', 'id') as $id => $name)
                        <option value="{{ $id }}" {{ request('division') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label for="risk" class="form-label small text-muted">Tingkat Risiko</label>
                <select name="risk" id="risk" class="form-select form-select-sm">
                    <option value="">-- Semua Risiko --</option>
                    <option value="low" {{ request('risk') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('risk') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('risk') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('risk') == 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Ringkasan per risiko -->
@php
    $riskCounts = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
    foreach ($findings as $f) {
        if (isset($riskCounts[$f->riskCategory->level ?? ''])) {
            $riskCounts[$f->riskCategory->level]++;
        }
    }
@endphp

<div class="row g-3 mb-4">
    @foreach($riskCounts as $level => $count)
        <div class="col-md-3 col-sm-6">
            <x-stat-card icon="shield-exclamation" label="Risiko {{ ucfirst($level) }}" value="{{ $count }}" color="{{ $level == 'critical' ? 'danger' : ($level == 'high' ? 'warning' : 'secondary') }}" />
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Total: {{ $findings->count() }} Temuan</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Temuan</th>
                        <th>Judul</th>
                        <th>Divisi</th>
                        <th>Kategori</th>
                        <th>Risiko</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findings as $finding)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $finding->finding_number }}</td>
                            <td>{{ $finding->title }}</td>
                            <td>{{ $finding->auditPlan->division->name ?? '-' }}</td>
                            <td>{{ $finding->category->name ?? '-' }}</td>
                            <td><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></td>
                            <td>{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }}</td>
                            <td><x-status-badge status="{{ $finding->status }}" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                                Tidak ada data temuan sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection