<!-- Card Component -->
@props(['header' => null, 'bodyClass' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($header)
        <div class="card-header">{{ $header }}</div>
    @endif
    <div class="card-body {{ $bodyClass }}">{{ $slot }}</div>
</div>
