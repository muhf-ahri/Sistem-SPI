<!-- Loading State Component -->
@props(['text' => 'Memuat...'])

<div class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2 text-muted">{{ $text }}</p>
</div>