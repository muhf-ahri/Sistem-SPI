@extends('layouts.app')

@section('title', 'Tambah Hari Libur')

@section('content')
<x-page-header title="Tambah Hari Libur / Cuti">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.audit-types.index') }}" class="text-decoration-none">Master Data</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master.holidays.index') }}" class="text-decoration-none">Hari Libur</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Form Tambah Hari Libur</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.holidays.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal Libur <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date') }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Hari Sabtu &amp; Minggu sudah otomatis libur tanpa perlu dicatat.</small>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Libur / Cuti <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Cuti Bersama, Perayaan, dll." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="custom" {{ old('type', 'custom') === 'custom' ? 'selected' : '' }}>Custom (Cuti / Libur internal)</option>
                            <option value="international" {{ old('type') === 'international' ? 'selected' : '' }}>Libur Internasional</option>
                            <option value="national" {{ old('type') === 'national' ? 'selected' : '' }}>Libur Nasional (manual)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Catatan</label>
                        <input type="text" class="form-control @error('note') is-invalid @enderror" id="note" name="note" value="{{ old('note') }}" placeholder="Opsional">
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('master.holidays.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
