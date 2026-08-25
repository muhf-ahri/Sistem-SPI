@extends('layouts.app')

@section('title', 'Tambah User Baru')

@section('content')
<x-page-header title="Tambah User Baru">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.users.index') }}" class="text-decoration-none">Pengguna</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Pengguna</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('master.users.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email">
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="division_id" class="form-label">Divisi (Khusus Kepala Divisi / Management)</label>
                    <select class="form-select" id="division_id" name="division_id">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $id => $name)
                            <option value="{{ $id }}" {{ old('division_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password (min. 8 karakter)">
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi password">
                </div>

                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_active">
                            Pengguna Aktif
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan User</button>
            </div>
        </form>
    </div>
</div>
@endsection