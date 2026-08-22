@extends('layouts.app')

@section('title', 'Detail Divisi')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-0">Detail Divisi</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.divisions.index') }}" class="text-decoration-none">Divisi</a></li>
            <li class="breadcrumb-item active">{{ $division->code }}</li>
        </ol>
    </nav>
</div>

<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Divisi</h5>
        <a href="{{ route('master.divisions.edit', $division) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="text-muted small">KODE</div>
                <div class="fw-bold">{{ $division->code }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">NAMA DIVISI</div>
                <div class="fw-bold">{{ $division->name }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">STATUS</div>
                <span class="badge bg-{{ $division->is_active ? 'success' : 'danger' }}">{{ $division->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
            </div>
            <div class="col-sm-12">
                <div class="text-muted small">DESKRIPSI</div>
                <p class="mb-0">{{ $division->description ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Pengguna dalam Divisi ({{ $division->users->count() }})</h5>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($division->users as $user)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $user->name }} <small class="text-muted">({{ $user->email }})</small></span>
                    <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
                </li>
            @empty
                <li class="list-group-item text-center text-muted">Belum ada pengguna di divisi ini.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection