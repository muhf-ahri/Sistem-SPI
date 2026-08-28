@extends('layouts.app')

@section('title', 'Laporan LHA')

@section('content')
<x-page-header title="Laporan Hasil Audit (LHA)">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Laporan</a></li>
            <li class="breadcrumb-item active">LHA</li>
        </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>
        <div class="d-flex gap-2">
            @include('reports._export-buttons', ['type' => 'lha'])
            <button onclick="window.print()" class="btn btn-outline-primary sdx-export-btn">
                <i class="bi bi-printer me-1"></i>Cetak
            </button>
        </div>
    </x-slot:actions>
</x-page-header>

<!-- Filter & Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.lha') }}" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="form-control form-control-sm"
                       placeholder="Cari no. laporan, judul, no. pengawasan...">
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
                <label for="year" class="form-label small text-muted">Tahun</label>
                <select name="year" id="year" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-12 d-flex justify-content-end align-items-end gap-2 ms-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
                <a href="{{ route('reports.lha') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Daftar Laporan LHA ({{ $reports->count() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Laporan</th>
                        <th>Judul</th>
                        <th>Pengawasan</th>
                        <th>Divisi</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th>Ukuran</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $report->report_number }}</td>
                            <td>{{ $report->title }}</td>
                            <td>
                                <a href="{{ route('audit-plans.show', $report->auditPlan) }}" class="text-decoration-none">
                                    {{ $report->auditPlan->audit_number }}
                                </a>
                            </td>
                            <td>{{ $report->auditPlan->division->name ?? '-' }}</td>
                            <td>{{ $report->createdBy->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->created_at)->format('d M Y') }}</td>
                            <td>{{ $report->file_size ? number_format($report->file_size / 1024, 1) . ' KB' : '-' }}</td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('audit-plans.reports.download', $report) }}"
                                       class="btn btn-sm btn-outline-primary" title="Unduh {{ $report->file_name }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @if(in_array(auth()->user()->role, ['spi', 'super_admin']))
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                title="Hapus laporan"
                                                data-bs-toggle="modal"
                                                data-bs-target="#hapusReport{{ $report->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <x-confirm-modal
                                            :id="'hapusReport' . $report->id"
                                            title="Hapus Laporan LHA?"
                                            :description="'Apakah Anda yakin ingin menghapus laporan ' . $report->report_number . '? File laporan juga akan terhapus dan tidak dapat dibatalkan.'"
                                            :form-action="route('reports.lha.destroy', $report)"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark-x fs-3 d-block mb-2"></i>
                                Belum ada laporan LHA.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
