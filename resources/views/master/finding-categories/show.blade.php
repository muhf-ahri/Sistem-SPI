@extends('layouts.app')

@section('title', 'Detail Kategori Temuan')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-0">Detail Kategori Temuan</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.finding-categories.index') }}" class="text-decoration-none">Kategori Temuan</a></li>
            <li class="breadcrumb-item active">{{ $findingCategory->name }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Kategori Temuan</h5>
        <a href="{{ route('master.finding-categories.edit', $findingCategory) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="text-muted small">NAMA</div>
                <div class="fw-bold">{{ $findingCategory->name }}</div>
            </div>
            <div class="col-sm-6">
                <div class="text-muted small">STATUS</div>
                <span class="badge bg-{{ $findingCategory->is_active ? 'success' : 'danger' }}">{{ $findingCategory->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
            </div>
            <div class="col-sm-12">
                <div class="text-muted small">DESKRIPSI</div>
                <p class="mb-0">{{ $findingCategory->description ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection