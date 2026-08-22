@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-0">Detail Pengguna</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.users.index') }}" class="text-decoration-none">Pengguna</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Pengguna</h5>
        <a href="{{ route('master.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="text-muted small">NAMA LENGKAP</div>
                <div class="fw-bold">{{ $user->name }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">EMAIL</div>
                <div class="fw-bold">{{ $user->email }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">ROLE</div>
                <div><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span></div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">DIVISI</div>
                <div class="fw-bold">{{ $user->division->name ?? '-' }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">STATUS AKUN</div>
                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">TERDAFTAR SEJAK</div>
                <div class="fw-bold">{{ $user->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection