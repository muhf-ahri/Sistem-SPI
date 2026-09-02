@extends('layouts.app')

@section('title', 'Edit Temuan')

@section('content')
<x-page-header title="Edit Temuan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('findings.index') }}" class="text-decoration-none">Temuan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('findings.show', $finding) }}" class="text-decoration-none">{{ $finding->finding_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Edit Temuan</h5>
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

        <form method="POST" action="{{ route('findings.update', $finding) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label text-muted">Audit Terkait</label>
                    <input type="text" class="form-control bg-light" value="{{ $finding->auditPlan->audit_number }} - {{ $finding->auditPlan->title }}" readonly>
                </div>

                <div class="col-md-6">
                    <label for="category_id" class="form-label">Kategori Temuan <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id', $finding->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="risk_category_id" class="form-label">Tingkat Risiko <span class="text-danger">*</span></label>
                    <select class="form-select @error('risk_category_id') is-invalid @enderror" id="risk_category_id" name="risk_category_id" required>
                        @foreach($riskCategories as $id => $name)
                            <option value="{{ $id }}" {{ old('risk_category_id', $finding->risk_category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('risk_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Judul Temuan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $finding->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="deadline" class="form-label">Batas Waktu Tindak Lanjut <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('deadline') is-invalid @enderror" id="deadline" name="deadline" value="{{ old('deadline', $finding->deadline) }}" required>
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status Temuan</label>
                    <div class="d-flex align-items-center gap-2">
                        <x-status-badge status="{{ $finding->status }}" />
                        <small class="text-muted">Berubah otomatis mengikuti alur tindak lanjut &amp; verifikasi</small>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Open &rarr; In Progress (divisi membuat tindak lanjut) &rarr; Waiting Verification (bukti dikirim)
                        &rarr; Closed (disetujui SPI). Jika ditolak, kembali ke divisi.
                    </small>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Deskripsi Temuan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description', $finding->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="risk_description" class="form-label">Deskripsi Resiko <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('risk_description') is-invalid @enderror" id="risk_description" name="risk_description" rows="3" required>{{ old('risk_description', $finding->risk_description) }}</textarea>
                    @error('risk_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="criteria_explanation" class="form-label">Kriteria Penjelasan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('criteria_explanation') is-invalid @enderror" id="criteria_explanation" name="criteria_explanation" rows="3" required>{{ old('criteria_explanation', $finding->criteria_explanation) }}</textarea>
                    @error('criteria_explanation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="recommendation" class="form-label">Rekomendasi Perbaikan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('recommendation') is-invalid @enderror" id="recommendation" name="recommendation" rows="4" required>{{ old('recommendation', $finding->recommendation) }}</textarea>
                    @error('recommendation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('findings.show', $finding) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Perbarui Temuan</button>
            </div>
        </form>
    </div>
</div>
@endsection