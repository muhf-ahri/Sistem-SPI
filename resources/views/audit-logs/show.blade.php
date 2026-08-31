@extends('layouts.app')

@section('title', 'Detail Audit Log')

@section('content')
<x-page-header title="Detail Aktivitas">
    <x-slot:breadcrumb>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('audit-logs.index') }}" class="text-decoration-none">Audit Log</a></li>
            <li class="breadcrumb-item active">#{{ $auditLog->id }}</li>
        </ol>
    </x-slot:breadcrumb>
</x-page-header>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Informasi Aktivitas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">WAKTU</span>
                    <strong>{{ $auditLog->created_at->format('d M Y H:i:s') }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">PENGGUNA</span>
                    <strong>{{ $auditLog->user->name ?? 'System' }}</strong>
                    <div class="mt-1">
                        @if($auditLog->user)
                            <span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $auditLog->user->role)) }}</span>
                            @if($auditLog->user->division)
                                <span class="badge bg-light text-dark border">{{ $auditLog->user->division->name }}</span>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">AKSI</span>
                    <strong>{{ ucwords(str_replace('_', ' ', $auditLog->action)) }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">ENTITAS</span>
                    <strong>{{ ucwords(str_replace('_', ' ', $auditLog->entity_type)) }} #{{ $auditLog->entity_id ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0 text-primary">Perubahan Data</h5>
            </div>
            <div class="card-body">
                @php
                    $diffBlock = function ($label, $data, $cls) {
                        $data = is_array($data) ? $data : (array) $data;
                        if (!$data) {
                            return '<h6 class="fw-bold ' . $cls . ' mb-2">' . $label . '</h6><p class="text-muted fst-italic small mb-0">Tidak ada data.</p>';
                        }
                        $rows = '';
                        foreach ($data as $k => $v) {
                            $val = is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                            $rows .= '<div class="row g-1 small py-1 border-bottom" style="border-color:#f0f0f0!important;"><div class="col-4 text-muted fw-semibold">' . ucwords(str_replace('_', ' ', $k)) . '</div><div class="col-8 text-break">' . e($val) . '</div></div>';
                        }
                        return '<h6 class="fw-bold ' . $cls . ' mb-2">' . $label . '</h6><div class="rounded p-2 mb-3" style="background:#fafafa;border:1px solid #eef2f6;">' . $rows . '</div>';
                    };
                @endphp

                {!! $diffBlock('<i class="bi bi-arrow-left-circle me-1"></i>Data Lama', $auditLog->old_values, 'text-danger') !!}
                {!! $diffBlock('<i class="bi bi-arrow-right-circle me-1"></i>Data Baru', $auditLog->new_values, 'text-success') !!}

                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleRaw">
                        <i class="bi bi-braces me-1"></i>Tampilkan JSON Mentah
                    </button>
                </div>
                <div id="rawJson" class="d-none mt-2">
                    <pre class="bg-dark text-light p-3 rounded small mb-0" style="max-height:320px;overflow:auto;">{{ json_encode(['action' => $auditLog->action, 'entity' => $auditLog->entity_type, 'entity_id' => $auditLog->entity_id, 'old' => $auditLog->old_values, 'new' => $auditLog->new_values], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('toggleRaw');
    var box = document.getElementById('rawJson');
    if (btn && box) {
        btn.addEventListener('click', function () {
            var hidden = box.classList.toggle('d-none');
            btn.innerHTML = hidden
                ? '<i class="bi bi-braces me-1"></i>Tampilkan JSON Mentah'
                : '<i class="bi bi-eye-slash me-1"></i>Sembunyikan JSON Mentah';
        });
    }
});
</script>
@endsection