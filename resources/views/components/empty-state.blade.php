<!-- Empty State Component -->
@props(['icon', 'title', 'description', 'buttonText' => null, 'buttonUrl' => null])

<div class="text-center py-5">
    <i class="bi bi-{{ $icon }} fs-1 text-muted"></i>
    <h4 class="mt-3">{{ $title }}</h4>
    <p class="text-muted">{{ $description }}</p>
    @if($buttonText && $buttonUrl)
        <a href="{{ $buttonUrl }}" class="btn btn-primary">{{ $buttonText }}</a>
    @endif
</div>