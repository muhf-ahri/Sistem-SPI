@extends('layouts.app')

@section('title', 'Buat Rencana Tindak Lanjut')

@section('content')
<x-page-header title="Buat Rencana Tindak Lanjut">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('findings.index') }}" class="text-decoration-none">Temuan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('findings.show', $finding) }}" class="text-decoration-none">{{ $finding->finding_number }}</a></li>
            <li class="breadcrumb-item active">Buat Tindak Lanjut</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Rencana Tindak Lanjut</h5>
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

        <form method="POST" action="{{ route('action-plans.store') }}">
            @csrf
            
            <!-- Hidden Finding ID -->
            <input type="hidden" name="finding_id" value="{{ $finding->id }}">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label text-muted">Temuan Terkait</label>
                    <input type="text" class="form-control bg-light" value="{{ $finding->finding_number }} - {{ $finding->title }}" readonly>
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Judul Tindakan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Perbaikan SOP Kas Kecil">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status Awal <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label text-muted">Target Tanggal Selesai</label>
                    <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($finding->deadline)->format('d M Y') }} (mengikuti batas waktu temuan)" readonly>
                </div>

                <div class="col-12">
                    <label for="action" class="form-label">Rencana Tindakan / Perbaikan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('action') is-invalid @enderror" id="action" name="action" rows="4" placeholder="Tuliskan rencana tindakan perbaikan..." required>{{ old('action') }}</textarea>
                    @error('action')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('findings.show', $finding) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Rencana</button>
            </div>
        </form>
    </div>
</div>
@endsection