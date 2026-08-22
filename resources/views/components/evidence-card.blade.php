<!-- Evidence Card Component -->
@props(['file', 'type', 'size', 'preview' => null])

<div {{ $attributes->merge(['class' => 'card evidence-card']) }}>
    <div class="card-body">
        <div class="d-flex align-items-start">
            <div class="me-3">
                @if($preview)
                    <img src="{{ $preview }}" alt="Preview" class="img-thumbnail" style="max-width: 80px; max-height: 80px;">
                @else
                    <i class="bi bi-file-earmark fs-2 text-muted"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1">{{ $file }}</h6>
                <div class="small text-muted">
                    <span class="badge bg-light text-dark">{{ $type }}</span>
                    <span>{{ number_format($size / 1024, 1) }} KB</span>
                </div>
            </div>
            <div>
                <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                <a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
            </div>
        </div>
    </div>
</div>