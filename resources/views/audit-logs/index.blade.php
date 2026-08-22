@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-0">Audit Log Aktivitas</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Audit Log</li>
        </ol>
    </nav>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="user" class="form-label small text-muted">Pengguna</label>
                <select name="user" id="user" class="form-select form-select-sm">
                    <option value="">-- Semua Pengguna --</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" {{ request('user') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="action" class="form-label small text-muted">Aksi</label>
                <select name="action" id="action" class="form-select form-select-sm">
                    <option value="">-- Semua Aksi --</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
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
                    @forelse($logs as $log)
                        <tr>
                            <td class="ps-4">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>
                                @php
                                    $actionColors = ['create' => 'success', 'update' => 'warning', 'delete' => 'danger', 'login' => 'info', 'logout' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $actionColors[$log->action] ?? 'secondary' }}">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td>{{ ucwords(str_replace('_', ' ', $log->entity_type)) }}</td>
                            <td>{{ $log->entity_id ?? '-' }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
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
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection