@extends('layouts.app')

@section('title', 'Manajemen Hari Libur')

@section('content')
<x-page-header title="Manajemen Hari Libur &amp; Cuti">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.audit-types.index') }}" class="text-decoration-none">Master Data</a></li>
                <li class="breadcrumb-item active">Hari Libur</li>
            </ol>
    </x-slot:breadcrumb>
    <x-slot:actions>
        <a href="{{ route('master.holidays.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-2"></i>Tambah Libur
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Ringkasan jumlah --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="sdx-stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-globe"></i></div>
                <div><div class="sdx-stat-label">Libur Nasional</div><div class="sdx-stat-value">{{ $totalNational }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="sdx-stat-icon bg-warning bg-opacity-10" style="color:#8a6d00"><i class="bi bi-calendar-x"></i></div>
                <div><div class="sdx-stat-label">Cuti / Custom</div><div class="sdx-stat-value">{{ $totalCustom }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="sdx-stat-label mb-2">Sinkronisasi Libur Nasional dari API</div>
                <form method="POST" action="{{ route('master.holidays.sync') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-4">
                        <select name="year" class="form-select form-select-sm">
                            @foreach(range(now()->year, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <button type="submit" class="btn btn-sm btn-outline-info w-100">
                            <i class="bi bi-cloud-download me-1"></i>Ambil Libur Nasional
                        </button>
                    </div>
                </form>
                <small class="text-muted">Sumber: Nager.Date (gratis). Hari libur internasional dapat ditambahkan manual oleh admin.</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('master.holidays.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama libur...">
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label small text-muted">Tahun</label>
                <select name="year" id="year" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label small text-muted">Jenis</label>
                <select name="type" id="type" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($types as $val => $label)
                        <option value="{{ $val }}" {{ request('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a href="{{ route('master.holidays.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 no-sort">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Hari</th>
                        <th>Nama Libur</th>
                        <th>Jenis</th>
                        <th>Catatan</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $holiday)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $holiday->date->format('d M Y') }}</td>
                            <td>{{ $holiday->date->isoFormat('dddd') }}</td>
                            <td>{{ $holiday->name }}</td>
                            <td>
                                @if($holiday->type === 'national')
                                    <span class="sdx-badge sdx-badge--blue">Nasional</span>
                                @elseif($holiday->type === 'international')
                                    <span class="sdx-badge sdx-badge--neutral">Internasional</span>
                                @else
                                    <span class="sdx-badge sdx-badge--gold">Custom</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $holiday->note ?: '-' }}</td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapus{{ $holiday->id }}" {{ $holiday->type !== 'custom' ? 'disabled title="Libur nasional dari API tidak dapat dihapus (dikelola via sinkronisasi)"' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                                @if($holiday->type === 'custom')
                                    <x-confirm-modal id="hapus{{ $holiday->id }}" title="Hapus Hari Libur?" description="Hapus libur '{{ $holiday->name }}'? Durasi pengerjaan akan dihitung ulang realtime." :form-action="route('master.holidays.destroy', $holiday)" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                Belum ada data hari libur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($holidays->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$holidays" />
        </div>
    @endif
</div>
@endsection
