<!-- Stat Card Component -->
@props(['icon', 'label', 'value', 'color' => 'primary', 'subtext' => null])

<div {{ $attributes->merge(['class' => 'card stat-card']) }}>
    <div class="card-body d-flex align-items-center">
        <div class="stat-icon me-3 bg-{{ $color }} bg-opacity-10 p-3 rounded-circle">
            <i class="bi bi-{{ $icon }} fs-3 text-{{ $color }}"></i>
        </div>
        <div>
            <div class="text-muted small text-uppercase">{{ $label }}</div>
            <div class="fs-4 fw-bold">{{ $value }}</div>
            @if($subtext)
                <div class="small text-muted">{{ $subtext }}</div>
            @endif
        </div>
    </div>
</div>