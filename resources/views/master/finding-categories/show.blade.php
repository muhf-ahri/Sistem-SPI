@extends('layouts.app')

@section('title', 'Detail Kategori Temuan')

@section('content')
<x-page-header title="Detail Kategori Temuan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.finding-categories.index') }}" class="text-decoration-none">Kategori Temuan</a></li>
            <li class="breadcrumb-item active">{{ $findingCategory->name }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Kategori Temuan</h5>
        <a href="{{ route('master.finding-categories.edit', $findingCategory) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <x-detail-list>
            <x-detail-item label="Nama">{{ $findingCategory->name }}</x-detail-item>
            <x-detail-item label="Status"><span class="badge bg-{{ $findingCategory->is_active ? 'success' : 'danger' }}">{{ $findingCategory->is_active ? 'Aktif' : 'Non-Aktif' }}</span></x-detail-item>
            <x-detail-item label="Deskripsi">{{ $findingCategory->description ?: '-' }}</x-detail-item>
        </x-detail-list>
    </div>
</div>
@endsection