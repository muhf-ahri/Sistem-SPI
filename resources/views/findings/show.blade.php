@extends('layouts.app')

@section('title', 'Detail Temuan')

@section('content')
<x-page-header
    :breadcrumb="[
        ['url' => route('dashboard'), 'label' => 'Dashboard'],
        ['url' => route('findings.index'), 'label' => 'Temuan'],
        ['url' => '#', 'label' => $finding->finding_number],
    ]"
    title="Detail Temuan"
    :description="$finding->title"
>
    <x-slot name="actions">
        @can('update', $finding)
            <a href="{{ route('findings.edit', $finding) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
        @endcan
        @can('delete', $finding)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#hapusTemuan">
                <i class="bi bi-trash me-2"></i>Hapus
            </button>
        @endcan
    </x-slot>
</x-page-header>

<!-- Siklus temuan -->
<div class="card mb-4">
    <div class="card-body">
        @php
            $statusIndex = match ($finding->status) {
                'open' => 0,
                'in_progress' => 1,
                'waiting_verification' => 2,
                'closed' => 3,
                default => 0,
            };
        @endphp
        <x-stepper :current="$statusIndex" :steps="[
            ['label' => 'Dibuka', 'sub' => 'Open', 'tone' => '#e63232'],
            ['label' => 'Dikerjakan', 'sub' => 'In progress', 'tone' => '#f2913b'],
            ['label' => 'Menunggu verifikasi', 'sub' => 'Verification', 'tone' => '#3f7fd4'],
            ['label' => 'Ditutup', 'sub' => 'Closed', 'tone' => '#27a35f'],
        ]" />
    </div>
</div>

<div class="row g-4">
    <!-- Informasi & Rekomendasi -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Informasi Temuan
                <x-status-badge status="{{ $finding->status }}" />
            </div>
            <div class="card-body">
                <x-detail-list>
                    <x-detail-item label="No. Temuan">{{ $finding->finding_number }}</x-detail-item>
                    <x-detail-item label="Kategori">{{ $finding->category->name ?? '-' }}</x-detail-item>
                    <x-detail-item label="Tingkat Risiko"><x-risk-badge level="{{ $finding->riskCategory->level ?? 'low' }}" /></x-detail-item>
                    <x-detail-item label="Batas Waktu">
                        <span class="{{ $finding->status != 'closed' && $finding->deadline < now() ? 'text-danger fw-bold' : '' }}">
                            {{ \Carbon\Carbon::parse($finding->deadline)->translatedFormat('d F Y') }}
                        </span>
                    </x-detail-item>
                    <x-detail-item label="Pengawasan Asal">
                        <a href="{{ route('audit-plans.show', $finding->auditPlan) }}">
                            {{ $finding->auditPlan->audit_number }} - {{ $finding->auditPlan->title }}
                        </a>
                    </x-detail-item>
                </x-detail-list>

                <div class="border-top pt-3 mt-4">
                    <h6 class="fw-bold">Deskripsi temuan</h6>
                    <p class="text-muted mb-0">{{ $finding->description }}</p>
                </div>

                <div class="border-top pt-3 mt-3">
                    <h6 class="fw-bold">Rekomendasi perbaikan</h6>
                    <p class="text-muted mb-0">{{ $finding->recommendation ?: 'Belum ada rekomendasi.' }}</p>
                </div>
            </div>
        </div>

        <!-- Tindak Lanjut -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                Rencana Tindak Lanjut
                @can('create', App\Models\ActionPlan::class)
                    <a href="{{ route('action-plans.create', ['finding_id' => $finding->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Buat Tindak Lanjut
                    </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">PIC</th>
                            <th>Rencana Aksi</th>
                            <th>Target Selesai</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($finding->actionPlans as $actionPlan)
                            <tr>
                                <td class="ps-4">
                                    <span class="d-inline-flex align-items-center gap-2">
                                        <x-avatar :name="$actionPlan->pic->name ?? '-'" size="sm" />
                                        {{ $actionPlan->pic->name ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($actionPlan->action, 60) }}</td>
                                <td>{{ \Carbon\Carbon::parse($actionPlan->target_date)->format('d M Y') }}</td>
                                <td><x-status-badge status="{{ $actionPlan->status }}" /></td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('action-plans.show', $actionPlan) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada rencana tindak lanjut dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Penugasan -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Informasi Penugasan</div>
            <div class="card-body">
                <x-detail-list>
                    <x-detail-item label="Divisi Terperiksa">{{ $finding->auditPlan->division->name ?? '-' }} ({{ $finding->auditPlan->division->code ?? '-' }})</x-detail-item>
                    <x-detail-item label="Dicatat Oleh">{{ $finding->createdBy->name ?? '-' }}</x-detail-item>
                    <x-detail-item label="Tanggal Dicatat">{{ $finding->created_at->translatedFormat('d F Y, H:i') }} WIB</x-detail-item>
                </x-detail-list>
            </div>
        </div>
    </div>
</div>

@can('delete', $finding)
    <x-confirm-modal
        id="hapusTemuan"
        title="Hapus temuan ini?"
        description="Temuan beserta seluruh rencana tindak lanjutnya akan dihapus. Tindakan ini tidak dapat dibatalkan."
        :form-action="route('findings.destroy', $finding)"
    />
@endcan
@endsection
