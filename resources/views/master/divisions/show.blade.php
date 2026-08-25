@extends('layouts.app')

@section('title', 'Detail Divisi')

@section('content')
<x-page-header title="Detail Divisi">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.divisions.index') }}" class="text-decoration-none">Divisi</a></li>
            <li class="breadcrumb-item active">{{ $division->code }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Divisi</h5>
        <a href="{{ route('master.divisions.edit', $division) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <x-detail-list>
            <x-detail-item label="Kode">{{ $division->code }}</x-detail-item>
            <x-detail-item label="Nama Divisi">{{ $division->name }}</x-detail-item>
            <x-detail-item label="Status"><span class="badge bg-{{ $division->is_active ? 'success' : 'danger' }}">{{ $division->is_active ? 'Aktif' : 'Non-Aktif' }}</span></x-detail-item>
            <x-detail-item label="Deskripsi">{{ $division->description ?: '-' }}</x-detail-item>
        </x-detail-list>
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