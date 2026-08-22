@extends('layouts.app')

@section('title', 'Laporan Pengawasan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0">Laporan Ringkasan Pengawasan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Laporan</a></li>
                <li class="breadcrumb-item active">Pengawasan</li>
            </ol>
        </nav>
    </div>
    <button onclick="window.print()" class="btn btn-outline-primary">
        <i class="bi bi-printer me-2"></i>Cetak / PDF
    </button>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.audit-summary') }}" class="row g-3">
            <div class="col-md-4">
                <label for="division" class="form-label small text-muted">Divisi</label>
                <select name="division" id="division" class="form-select form-select-sm">
                    <option value="">-- Semua Divisi --</option>
                    @foreach(\App\Models\Division::where('is_active', true)->pluck('name', 'id') as $id => $name)
                        <option value="{{ $id }}" {{ request('division') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label small text-muted">Periode Dari</label>
                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label small text-muted">Sampai</label>
                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Total: {{ $audits->count() }} Pengawasan</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Pengawasan</th>
                        <th>Judul</th>
                        <th>Divisi</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Pembuat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $audit->audit_number }}</td>
                            <td>{{ $audit->title }}</td>
                            <td>{{ $audit->division->name ?? '-' }}</td>
                            <td>{{ $audit->auditType->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($audit->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($audit->end_date)->format('d M Y') }}</td>
                            <td>{{ $audit->createdBy->name ?? '-' }}</td>
                            <td><x-status-badge status="{{ $audit->status }}" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text fs-1 d-block mb-3"></i>
                                Tidak ada data pengawasan sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection