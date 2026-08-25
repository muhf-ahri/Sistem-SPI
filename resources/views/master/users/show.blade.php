@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')
<x-page-header title="Detail Pengguna">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.users.index') }}" class="text-decoration-none">Pengguna</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-primary">Informasi Pengguna</h5>
        <a href="{{ route('master.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
    </div>
    <div class="card-body">
        <x-detail-list>
            <x-detail-item label="Nama Lengkap">{{ $user->name }}</x-detail-item>
            <x-detail-item label="Email">{{ $user->email }}</x-detail-item>
            <x-detail-item label="Role"><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span></x-detail-item>
            <x-detail-item label="Divisi">{{ $user->division->name ?? '-' }}</x-detail-item>
            <x-detail-item label="Status Akun"><span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</span></x-detail-item>
            <x-detail-item label="Terdaftar Sejak">{{ $user->created_at->format('d M Y H:i') }}</x-detail-item>
        </x-detail-list>
    </div>
</div>
@endsection