@extends('layouts.app')

@section('title', 'Tambah Pemeriksaan')

@section('content')
<x-page-header title="Tambah Pemeriksaan">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}" class="text-decoration-none">Pemeriksaan</a></li>
            <li class="breadcrumb-item active">Tambah</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Pemeriksaan Lapangan</h5>
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

        <form method="POST" action="{{ route('inspections.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="audit_plan_id" class="form-label">Pengawasan Terkait <span class="text-danger">*</span></label>
                    <select class="form-select @error('audit_plan_id') is-invalid @enderror" id="audit_plan_id" name="audit_plan_id" required>
                        <option value="">-- Pilih Pengawasan --</option>
                        @foreach($auditPlans as $id => $title)
                            <option value="{{ $id }}" {{ old('audit_plan_id', request('audit_plan_id')) == $id ? 'selected' : '' }}>{{ $title }}</option>
                        @endforeach
                    </select>
                    @error('audit_plan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="inspection_date" class="form-label">Tanggal Pemeriksaan <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('inspection_date') is-invalid @enderror" id="inspection_date" name="inspection_date" value="{{ old('inspection_date', date('Y-m-d')) }}" required>
                    @error('inspection_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="result" class="form-label">Hasil Pemeriksaan <span class="text-danger">*</span></label>
                    <select class="form-select @error('result') is-invalid @enderror" id="result" name="result" required>
                        <option value="satisfactory" {{ old('result') == 'satisfactory' ? 'selected' : '' }}>Satisfactory (Memuaskan)</option>
                        <option value="needs_improvement" {{ old('result') == 'needs_improvement' ? 'selected' : '' }}>Needs Improvement (Perlu Perbaikan)</option>
                        <option value="non_conformity" {{ old('result') == 'non_conformity' ? 'selected' : '' }}>Non Conformity (Ketidaksesuaian)</option>
                    </select>
                    @error('result')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="summary" class="form-label">Ringkasan Temuan / Hasil Lapangan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="4" placeholder="Tuliskan ringkasan hasil kunjungan/pemeriksaan secara umum..." required>{{ old('summary') }}</textarea>
                    @error('summary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Catatan Tambahan</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4" placeholder="Catatan internal tim auditor...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('inspections.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Pemeriksaan</button>
            </div>
        </form>
    </div>
</div>
@endsection