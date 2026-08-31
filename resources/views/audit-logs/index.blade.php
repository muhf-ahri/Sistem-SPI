@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<x-page-header title="Audit Log Aktivitas">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Audit Log</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-3">
            <div class="col-lg-4 col-md-6">
                <label for="search" class="form-label small text-muted">Pencarian</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari pengguna, aksi, entitas, atau perubahan data...">
            </div>
            <div class="col-lg-3 col-md-6">
                <label for="user" class="form-label small text-muted">Pengguna</label>
                <select name="user" id="user" class="form-select form-select-sm">
                    <option value="">-- Semua Pengguna --</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ request('user') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label for="action" class="form-label small text-muted">Aksi</label>
                <select name="action" id="action" class="form-select form-select-sm">
                    <option value="">-- Semua Aksi --</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
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
            <div class="col-lg-1 col-md-4 d-flex flex-wrap align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary flex-grow-1">Reset</a>
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
                        <th class="ps-4">Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Entitas</th>
                        <th>ID Entitas</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $actionColors = ['create' => 'success', 'update' => 'warning', 'delete' => 'danger', 'login' => 'info', 'logout' => 'secondary'];
                        $actionIcon = ['create' => 'bi-plus-circle', 'update' => 'bi-pencil-square', 'delete' => 'bi-trash', 'login' => 'bi-box-arrow-in-right', 'logout' => 'bi-box-arrow-right'];
                        $roleLabel = fn ($r) => ucwords(str_replace('_', ' ', $r ?? 'system'));
                    @endphp
                    @forelse($logs as $log)
                        @php $detailId = 'logDetail' . $log->id; @endphp
                        <tr>
                            <td class="ps-4 text-nowrap">{{ $log->created_at->format('d M Y') }}<br><small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small></td>
                            <td>
                                <div class="fw-semibold">{{ $log->user->name ?? 'System' }}</div>
                                <small class="text-muted d-block">
                                    <span class="badge bg-light text-dark border me-1">{{ $roleLabel($log->user->role ?? null) }}</span>
                                    @if($log->user && $log->user->division)
                                        {{ $log->user->division->name }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $actionColors[$log->action] ?? 'secondary' }}">
                                    <i class="bi {{ $actionIcon[$log->action] ?? 'bi-three-dots' }} me-1"></i>{{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ ucwords(str_replace('_', ' ', $log->entity_type)) }}</div>
                                <small class="text-muted">#{{ $log->entity_id ?? '-' }}</small>
                            </td>
                            @php $hasChanges = !empty($log->old_values) || !empty($log->new_values); @endphp
                            <td>
                                @if($hasChanges)
                                    <span class="badge bg-info-subtle text-info-emphasis">ada perubahan</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-secondary toggle-detail" data-target="#{{ $detailId }}">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            </td>
                        </tr>
                        <tr class="d-none" id="{{ $detailId }}">
                            <td colspan="6" class="p-0 border-0">
                                <div class="bg-light px-4 py-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <h6 class="small fw-bold text-muted text-uppercase mb-2"><i class="bi bi-arrow-left-circle me-1"></i>Sebelum (Old)</h6>
                                            @if($log->old_values)
                                                <div class="sdx-log-diff">
                                                    @foreach((array) $log->old_values as $k => $v)
                                                        <div class="row g-1 small"><div class="col-4 text-muted">{{ ucwords(str_replace('_', ' ', $k)) }}</div><div class="col-8">{{ is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE) }}</div></div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="small text-muted mb-0">Tidak ada data lama.</p>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="small fw-bold text-muted text-uppercase mb-2"><i class="bi bi-arrow-right-circle me-1"></i>Sesudah (New)</h6>
                                            @if($log->new_values)
                                                <div class="sdx-log-diff">
                                                    @foreach((array) $log->new_values as $k => $v)
                                                        <div class="row g-1 small"><div class="col-4 text-muted">{{ ucwords(str_replace('_', ' ', $k)) }}</div><div class="col-8 text-success">{{ is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE) }}</div></div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="small text-muted mb-0">Tidak ada data baru.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-link p-0 mt-2">
                                        <i class="bi bi-fullscreen me-1"></i>Lihat serialisasi lengkap
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-3"></i>
                                Belum ada aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            <x-pagination :paginator="$logs" />
        </div>
    @endif
</div>
@endsection

@section('styles')
<style>
    .sdx-log-diff { border: 1px solid #e3e8ee; border-radius: 4px; background: #fff; padding: .5rem .75rem; }
    .sdx-log-diff > div { border-bottom: 1px dashed #e3e8ee; padding: .1rem 0; }
    .sdx-log-diff > div:last-child { border-bottom: none; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = document.querySelector(btn.dataset.target);
            if (!row) return;
            row.classList.toggle('d-none');
            var icon = btn.querySelector('i');
            if (icon) icon.className = row.classList.contains('d-none') ? 'bi bi-eye me-1' : 'bi bi-eye-slash me-1';
        });
    });
});
</script>
@endsection