@extends('layouts.app')

@section('title', 'Buat Temuan Baru')

@section('content')
<x-page-header title="Buat Temuan Baru">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('audit-plans.index') }}" class="text-decoration-none">Audit</a></li>
            <li class="breadcrumb-item"><a href="{{ route('audit-plans.show', $auditPlan) }}" class="text-decoration-none">{{ $auditPlan->audit_number }}</a></li>
            <li class="breadcrumb-item active">Buat Temuan</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Temuan Audit</h5>
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

        <form method="POST" action="{{ route('findings.store') }}">
            @csrf
            
            <!-- Hidden Audit Plan ID -->
            <input type="hidden" name="audit_plan_id" value="{{ $auditPlan->id }}">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label text-muted">Audit Terkait</label>
                    <input type="text" class="form-control bg-light" value="{{ $auditPlan->audit_number }} - {{ $auditPlan->title }}" readonly>
                </div>

                <div class="col-md-12">
                    <label for="inspection_id" class="form-label">Berdasarkan Pemeriksaan</label>
                    <select class="form-select" id="inspection_id" name="inspection_id">
                        <option value="">Custom (Tidak terikat pemeriksaan)</option>
                        @foreach($inspections as $insp)
                            <option value="{{ $insp->id }}" {{ $selectedInspectionId == $insp->id ? 'selected' : '' }}>
                                Kunjungan {{ \Carbon\Carbon::parse($insp->inspection_date)->format('d M Y') }} � {{ $insp->auditor->name ?? '-' }} � hasil: {{ ucwords(str_replace('_', ' ', $insp->result)) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilih pemeriksaan yang menjadi dasar temuan ini, atau biarkan kosong jika tidak terkait.</small>
                </div>

                <div class="col-md-6">
                    <label for="category_id" class="form-label">Kategori Temuan <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="risk_category_id" class="form-label">Tingkat Risiko <span class="text-danger">*</span></label>
                    <select class="form-select @error('risk_category_id') is-invalid @enderror" id="risk_category_id" name="risk_category_id" required>
                        <option value="">-- Pilih Tingkat Risiko --</option>
                        @foreach($riskCategories as $id => $name)
                            <option value="{{ $id }}" {{ old('risk_category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('risk_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Judul Temuan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Selisih Saldo Kas Kecil">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="deadline" class="form-label">Batas Waktu Tindak Lanjut <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('deadline') is-invalid @enderror" id="deadline" name="deadline" value="{{ old('deadline') }}" required>
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status Awal <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="open" {{ old('status', 'open') == 'open' ? 'selected' : '' }}>Terbuka</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Deskripsi Temuan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Jelaskan secara mendetail temuan pemeriksaan..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="risk_description" class="form-label">Deskripsi Resiko <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('risk_description') is-invalid @enderror" id="risk_description" name="risk_description" rows="3" placeholder="Jelaskan potensi resiko yang ditimbulkan dari temuan ini..." required>{{ old('risk_description') }}</textarea>
                    @error('risk_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="criteria_explanation" class="form-label">Kriteria Penjelasan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('criteria_explanation') is-invalid @enderror" id="criteria_explanation" name="criteria_explanation" rows="3" placeholder="Jelaskan kriteria atau standar yang tidak terpenuhi..." required>{{ old('criteria_explanation') }}</textarea>
                    @error('criteria_explanation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="recommendation" class="form-label">Rekomendasi Perbaikan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('recommendation') is-invalid @enderror" id="recommendation" name="recommendation" rows="4" placeholder="Berikan saran/rekomendasi penyelesaian tindak lanjut..." required>{{ old('recommendation') }}</textarea>
                    @error('recommendation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('audit-plans.show', $auditPlan) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Temuan</button>
            </div>
        </form>
    </div>
</div>
@endsection