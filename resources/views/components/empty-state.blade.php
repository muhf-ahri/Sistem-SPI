<!-- Empty State Component -->
@props(['icon', 'title', 'description', 'buttonText' => null, 'buttonUrl' => null])

<div class="text-center py-5 px-3">
    <div class="sdx-empty-icon">
        <i class="bi bi-{{ $icon }}"></i>
    </div>
    <h4 class="mt-4 mb-2" style="font-family: var(--font-display, 'Chakra Petch', sans-serif); font-weight: 700; text-transform: uppercase; letter-spacing: .01em; color: var(--tinta, #10263f);">{{ $title }}</h4>
    <p class="text-muted mb-0 mx-auto" style="max-width: 40ch;">{{ $description }}</p>
    @if($buttonText && $buttonUrl)
        <a href="{{ $buttonUrl }}" class="btn btn-primary mt-4">{{ $buttonText }}</a>
    @endif
</div>
