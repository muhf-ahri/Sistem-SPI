@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<x-page-header title="Edit Pengguna">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.users.index') }}" class="text-decoration-none">Pengguna</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Edit Pengguna</h5>
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

        <form method="POST" action="{{ route('master.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="division_id" class="form-label">Divisi (Khusus Kepala Divisi / Management)</label>
                    <select class="form-select" id="division_id" name="division_id">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $id => $name)
                            <option value="{{ $id }}" {{ old('division_id', $user->division_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password Baru (Biarkan kosong jika tidak diubah)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password baru">
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                </div>

                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_active">
                            Pengguna Aktif
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Perbarui User</button>
            </div>
        </form>
    </div>
</div>
@endsection