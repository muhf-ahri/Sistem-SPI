@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('content')
<x-page-header title="Detail Aktivitas">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('audit-logs.index') }}" class="text-decoration-none">Audit Log</a></li>
            <li class="breadcrumb-item active">#{{ $auditLog->id }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Informasi Aktivitas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">WAKTU</span>
                    <strong>{{ $auditLog->created_at->format('d M Y H:i:s') }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">PENGGUNA</span>
                    <strong>{{ $auditLog->user->name ?? 'System' }}</strong>
                    @if($auditLog->user)
                        <small class="text-muted">({{ ucwords(str_replace('_', ' ', $auditLog->user->role)) }})</small>
                    @endif
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">AKSI</span>
                    <strong>{{ ucwords(str_replace('_', ' ', $auditLog->action)) }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">ENTITAS</span>
                    <strong>{{ ucwords(str_replace('_', ' ', $auditLog->entity_type)) }} #{{ $auditLog->entity_id ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Perubahan Data</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6 class="fw-bold text-danger"><i class="bi bi-arrow-left-circle me-1"></i>Data Lama</h6>
                    @if($auditLog->old_values)
                        <pre class="bg-light p-3 rounded border small mb-0">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <p class="text-muted fst-italic mb-0">Tidak ada data lama (entitas baru dibuat).</p>
                    @endif
                </div>

                <div>
                    <h6 class="fw-bold text-success"><i class="bi bi-arrow-right-circle me-1"></i>Data Baru</h6>
                    @if($auditLog->new_values)
                        <pre class="bg-light p-3 rounded border small mb-0">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <p class="text-muted fst-italic mb-0">Tidak ada data baru.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection