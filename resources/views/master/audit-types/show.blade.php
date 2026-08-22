@extends('layouts.app')

@section('title', 'Detail Jenis Pengawasan')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-0">Detail Jenis Pengawasan</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.audit-types.index') }}" class="text-decoration-none">Jenis Pengawasan</a></li>
            <li class="breadcrumb-item active">{{ $auditType->name }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Jenis Pengawasan</h5>
        <a href="{{ route('master.audit-types.edit', $auditType) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="text-muted small">NAMA</div>
                <div class="fw-bold">{{ $auditType->name }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">STATUS</div>
                <span class="badge bg-{{ $auditType->is_active ? 'success' : 'danger' }}">{{ $auditType->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
            </div>
            <div class="col-sm-12">
                <div class="text-muted small">DESKRIPSI</div>
                <p class="mb-0">{{ $auditType->description ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection