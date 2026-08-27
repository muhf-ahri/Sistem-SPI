{{-- Evidence Card Component: kartu bukti (pemeriksaan / tindak lanjut).
     Usage:
     <x-evidence-card :file="$e->file_name" :type="$e->file_type" :size="$e->file_size"
         :url="asset('storage/'.$e->file_path)" :keterangan="$e->keterangan"
         :uploader="$e->uploadedBy->name ?? null" :time="'20:13'" />
--}}
@props([
    'file',
    'type' => null,
    'size' => null,
    'preview' => null,
    'url' => null,
    'downloadUrl' => null,
    'keterangan' => null,
    'uploader' => null,
    'time' => null,
    'icon' => null,
    'modalId' => null,
])

@php
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $defaultIcon = match(true) {
        in_array($ext, $imageTypes) => 'bi-file-earmark-image',
        $ext === 'pdf' => 'bi-file-earmark-pdf',
        in_array($ext, ['doc', 'docx']) => 'bi-file-earmark-word',
        in_array($ext, ['xls', 'xlsx', 'csv']) => 'bi-file-earmark-excel',
        default => 'bi-file-earmark',
    };
@endphp

<div {{ $attributes->merge(['class' => 'sdx-evidence h-100']) }}>
    <div class="sdx-evidence-icon">
        @if($preview)
            <img src="{{ $preview }}" alt="Pratinjau {{ $file }}">
        @else
            <i class="bi {{ $icon ?? $defaultIcon }}"></i>
        @endif
    </div>
    <div class="flex-grow-1" style="min-width: 0;">
        <div class="sdx-evidence-name" title="{{ $file }}">{{ $file }}</div>
        <div class="small text-muted">
            @if($type)<x-chip class="me-1">{{ $type }}</x-chip>@endif
            @if($size !== null)<span>{{ number_format($size / 1024, 1) }} KB</span>@endif
        </div>
        @if($uploader || $time)
            <small class="text-muted d-block mt-1">
                @if($uploader)Oleh: {{ $uploader }}@endif
                @if($uploader && $time) &middot; @endif
                @if($time){{ $time }}@endif
            </small>
        @endif
        @if($keterangan)
            <div class="border-top pt-2 mt-2">
                <small class="text-muted d-block fw-bold"><i class="bi bi-card-text me-1"></i>Keterangan Perbaikan:</small>
                <p class="small text-muted mb-0">{{ $keterangan }}</p>
            </div>
        @endif
        @if($url || $downloadUrl)
            <div class="d-flex gap-1 mt-2">
                @if($url)
                    @if($modalId)
                        <button type="button" class="btn btn-sm btn-link p-0 sdx-preview-btn" title="Lihat file" aria-label="Lihat {{ $file }}" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}" data-url="{{ $url }}" data-type="{{ $ext }}" data-file="{{ $file }}"><i class="bi bi-eye me-1"></i>Lihat</button>
                    @else
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-link p-0" title="Lihat file" aria-label="Lihat {{ $file }}"><i class="bi bi-eye me-1"></i>Lihat</a>
                    @endif
                @endif
                @if($downloadUrl)
                    <a href="{{ $downloadUrl }}" class="btn btn-sm btn-link p-0" title="Unduh file" aria-label="Unduh {{ $file }}"><i class="bi bi-download me-1"></i>Unduh</a>
                @endif
            </div>
        @endif
    </div>
</div>
