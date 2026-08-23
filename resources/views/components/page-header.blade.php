<!-- Page Header Component -->
@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'breadcrumb' => [],
])

<div class="sdx-page-head">
    <div>
        @if($eyebrow)
            <div class="sdx-eyebrow">{{ $eyebrow }}</div>
        @elseif(count($breadcrumb))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    @foreach($breadcrumb as $item)
                        @if(!$loop->last)
                            <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1>{{ $title }}</h1>
        @if($description)
            <p class="sdx-page-desc">{{ $description }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="sdx-page-actions">{{ $actions }}</div>
    @endif
</div>
