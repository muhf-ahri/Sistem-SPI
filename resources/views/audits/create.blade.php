@extends('layouts.app')

@section('title', 'Buat Pengawasan Baru')

@section('content')
<x-page-header title="Buat Pengawasan Baru">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('audit-plans.index') }}" class="text-decoration-none">Pengawasan</a></li>
            <li class="breadcrumb-item active">Buat</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Pengawasan</h5>
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

        <form method="POST" action="{{ route('audit-plans.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="audit_number" class="form-label">No. Pengawasan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('audit_number') is-invalid @enderror" id="audit_number" name="audit_number" value="{{ old('audit_number', 'AUD-' . date('YmdHis')) }}" required>
                    @error('audit_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Judul Pengawasan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Audit Keuangan Triwulan I">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="division_id" class="form-label">Divisi <span class="text-danger">*</span></label>
                    <select class="form-select @error('division_id') is-invalid @enderror" id="division_id" name="division_id" required>
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $id => $name)
                            <option value="{{ $id }}" {{ old('division_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('division_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="audit_type_id" class="form-label">Jenis Pengawasan <span class="text-danger">*</span></label>
                    <select class="form-select @error('audit_type_id') is-invalid @enderror" id="audit_type_id" name="audit_type_id" required>
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($auditTypes as $id => $name)
                            <option value="{{ $id }}" {{ old('audit_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('audit_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="start_date" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="auditor_ids" class="form-label">Auditor Ditugaskan</label>
                    <select class="form-select @error('auditor_ids') is-invalid @enderror" id="auditor_ids" name="auditor_ids[]" multiple style="height: 100px;">
                        @foreach($auditors as $id => $name)
                            <option value="{{ $id }}" {{ is_array(old('auditor_ids')) && in_array($id, old('auditor_ids')) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-1">Tahan tombol Ctrl (Windows) / Cmd (Mac) untuk memilih lebih dari satu auditor.</small>
                    @error('auditor_ids')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Deskripsi / Ruang Lingkup</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Deskripsikan ruang lingkup pemeriksaan pengawasan...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('audit-plans.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Pengawasan</button>
            </div>
        </form>
    </div>
</div>
@endsection