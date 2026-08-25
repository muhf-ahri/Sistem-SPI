@extends('layouts.app')

@section('title', 'Edit Kategori Risiko')

@section('content')
<x-page-header title="Edit Kategori Risiko">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('master.risk-categories.index') }}" class="text-decoration-none">Kategori Risiko</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Edit Kategori Risiko</h5>
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

        <form method="POST" action="{{ route('master.risk-categories.update', $riskCategory) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $riskCategory->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="level" class="form-label">Level Risiko <span class="text-danger">*</span></label>
                    <select class="form-select" id="level" name="level" required>
                        <option value="low" {{ old('level', $riskCategory->level) == 'low' ? 'selected' : '' }}>Low (Rendah)</option>
                        <option value="medium" {{ old('level', $riskCategory->level) == 'medium' ? 'selected' : '' }}>Medium (Sedang)</option>
                        <option value="high" {{ old('level', $riskCategory->level) == 'high' ? 'selected' : '' }}>High (Tinggi)</option>
                        <option value="critical" {{ old('level', $riskCategory->level) == 'critical' ? 'selected' : '' }}>Critical (Kritis)</option>
                    </select>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $riskCategory->description) }}</textarea>
                </div>

                <div class="col-md-6">
                    <div class="form-check mt-3">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $riskCategory->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="is_active">
                            Kategori Aktif
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('master.risk-categories.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Perbarui Kategori</button>
            </div>
        </form>
    </div>
</div>
@endsection