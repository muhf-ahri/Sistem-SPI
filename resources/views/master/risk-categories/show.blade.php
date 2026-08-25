@extends('layouts.app')

@section('title', 'Detail Kategori Risiko')

@section('content')
<x-page-header title="Detail Kategori Risiko">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.risk-categories.index') }}" class="text-decoration-none">Kategori Risiko</a></li>
            <li class="breadcrumb-item active">{{ $riskCategory->name }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Kategori Risiko</h5>
        <a href="{{ route('master.risk-categories.edit', $riskCategory) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <x-detail-list>
            <x-detail-item label="Nama">{{ $riskCategory->name }}</x-detail-item>
            <x-detail-item label="Level Risiko"><x-risk-badge level="{{ $riskCategory->level }}" /></x-detail-item>
            <x-detail-item label="Status"><span class="badge bg-{{ $riskCategory->is_active ? 'success' : 'danger' }}">{{ $riskCategory->is_active ? 'Aktif' : 'Non-Aktif' }}</span></x-detail-item>
            <x-detail-item label="Deskripsi">{{ $riskCategory->description ?: '-' }}</x-detail-item>
        </x-detail-list>
    </div>
</div>
@endsection