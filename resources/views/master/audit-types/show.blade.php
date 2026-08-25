@extends('layouts.app')

@section('title', 'Detail Jenis Pengawasan')

@section('content')
<x-page-header title="Detail Jenis Pengawasan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.audit-types.index') }}" class="text-decoration-none">Jenis Pengawasan</a></li>
            <li class="breadcrumb-item active">{{ $auditType->name }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Jenis Pengawasan</h5>
        <a href="{{ route('master.audit-types.edit', $auditType) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <x-detail-list>
            <x-detail-item label="Nama">{{ $auditType->name }}</x-detail-item>
            <x-detail-item label="Status"><span class="badge bg-{{ $auditType->is_active ? 'success' : 'danger' }}">{{ $auditType->is_active ? 'Aktif' : 'Non-Aktif' }}</span></x-detail-item>
            <x-detail-item label="Deskripsi">{{ $auditType->description ?: '-' }}</x-detail-item>
        </x-detail-list>
    </div>
</div>
@endsection