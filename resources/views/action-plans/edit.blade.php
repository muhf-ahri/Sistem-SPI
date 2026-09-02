@extends('layouts.app')

@section('title', 'Edit Rencana Tindak Lanjut')

@section('content')
<x-page-header title="Edit Rencana Tindak Lanjut">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('action-plans.index') }}" class="text-decoration-none">Tindak Lanjut</a></li>
            <li class="breadcrumb-item"><a href="{{ route('action-plans.show', $actionPlan) }}" class="text-decoration-none">Detail</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-primary">Formulir Edit Tindak Lanjut</h5>
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

        <form method="POST" action="{{ route('action-plans.update', $actionPlan) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label text-muted">Temuan Terkait</label>
                    <input type="text" class="form-control bg-light" value="{{ $actionPlan->finding->finding_number }} - {{ $actionPlan->finding->title }}" readonly>
                </div>

                <div class="col-md-6">
                    <label for="title" class="form-label">Judul Tindakan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $actionPlan->title) }}" required placeholder="Contoh: Perbaikan SOP Kas Kecil">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status Awal <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="pending" {{ old('status', $actionPlan->status) == 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="in_progress" {{ old('status', $actionPlan->status) == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="submitted" {{ old('status', $actionPlan->status) == 'submitted' ? 'selected' : '' }}>Diajukan</option>
                        <option value="verified" {{ old('status', $actionPlan->status) == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="rejected" {{ old('status', $actionPlan->status) == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="completed" {{ old('status', $actionPlan->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label text-muted">Target Tanggal Selesai</label>
                    <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($actionPlan->target_date)->format('d M Y') }} (mengikuti batas waktu temuan)" readonly>
                </div>

                <div class="col-12">
                    <label for="action" class="form-label">Rencana Tindakan / Perbaikan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('action') is-invalid @enderror" id="action" name="action" rows="4" required>{{ old('action', $actionPlan->action) }}</textarea>
                    @error('action')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('action-plans.show', $actionPlan) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Perbarui Rencana</button>
            </div>
        </form>
    </div>
</div>
@endsection