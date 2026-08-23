<!-- Timeline Item Component -->
@props([
    'time' => null,
    'tone' => '#6d8ab4',
])

<li style="--tl-tone: {{ $tone }};">
    <div class="sdx-tl-body">{{ $slot }}</div>
    @if($time)
        <div class="sdx-tl-time">{{ $time }}</div>
    @endif
</li>
