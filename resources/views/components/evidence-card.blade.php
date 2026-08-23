<!-- Evidence Card Component -->
@props([
    'file',
    'type' => null,
    'size' => null,
    'preview' => null,
    'url' => null,
    'downloadUrl' => null,
])

@php
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $icon = match(true) {
        in_array($ext, $imageTypes) => 'bi-file-earmark-image',
        $ext === 'pdf' => 'bi-file-earmark-pdf',
        in_array($ext, ['doc', 'docx']) => 'bi-file-earmark-word',
        in_array($ext, ['xls', 'xlsx', 'csv']) => 'bi-file-earmark-excel',
        default => 'bi-file-earmark',
    };
@endphp

<div {{ $attributes->merge(['class' => 'sdx-evidence']) }}>
    <div class="sdx-evidence-icon">
        @if($preview)
            <img src="{{ $preview }}" alt="Pratinjau {{ $file }}">
        @else
            <i class="bi {{ $icon }}"></i>
        @endif
    </div>
    <div class="flex-grow-1" style="min-width: 0;">
        <div class="sdx-evidence-name">{{ $file }}</div>
        <div class="small text-muted">
            @if($type)<x-chip class="me-1">{{ $type }}</x-chip>@endif
            @if($size !== null)<span>{{ number_format($size / 1024, 1) }} KB</span>@endif
        </div>
    </div>
    @if($url || $downloadUrl)
        <div class="d-flex gap-1">
            @if($url)
                <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Lihat file" aria-label="Lihat {{ $file }}"><i class="bi bi-eye"></i></a>
            @endif
            @if($downloadUrl)
                <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-secondary" title="Unduh file" aria-label="Unduh {{ $file }}"><i class="bi bi-download"></i></a>
            @endif
        </div>
    @endif
</div>
