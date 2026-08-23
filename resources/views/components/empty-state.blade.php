<!-- Empty State Component -->
@props(['icon', 'title', 'description', 'buttonText' => null, 'buttonUrl' => null])

<div class="text-center py-5 px-3">
    <div class="sdx-empty-icon">
        <i class="bi bi-{{ $icon }}"></i>
    </div>
    <h4 class="mt-4 mb-2" style="font-weight: 700; color: var(--spi-navy-deep, #122e56);">{{ $title }}</h4>
    <p class="text-muted mb-0 mx-auto" style="max-width: 40ch;">{{ $description }}</p>
    @if($buttonText && $buttonUrl)
        <a href="{{ $buttonUrl }}" class="btn btn-primary mt-4">{{ $buttonText }}</a>
    @endif
</div>
