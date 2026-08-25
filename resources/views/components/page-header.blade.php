{{-- Page Header Component: judul halaman + breadcrumb/eyebrow + deskripsi + aksi.
     Usage:
     <x-page-header title="Detail Pengawasan">
         <x-slot:breadcrumb>
             <ol class="breadcrumb mb-0">...</ol>
         </x-slot:breadcrumb>
         <x-slot:actions>
             <a href="..." class="btn btn-primary">Tambah</a>
         </x-slot:actions>
     </x-page-header>
     Breadcrumb juga bisa dikirim sebagai properti array: :breadcrumb="[['url'=>...,'label'=>...]]"
--}}
@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'breadcrumb' => null,
])

<div class="sdx-page-head">
    <div>
        @if($eyebrow)
            <div class="sdx-eyebrow">{{ $eyebrow }}</div>
        @elseif($breadcrumb instanceof \Illuminate\View\ComponentSlot && ! $breadcrumb->isEmpty())
            <nav aria-label="breadcrumb">{{ $breadcrumb }}</nav>
        @elseif(is_array($breadcrumb) && count($breadcrumb))
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
    @isset($actions)
        <div class="sdx-page-actions">{{ $actions }}</div>
    @endisset
</div>
