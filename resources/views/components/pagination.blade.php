<!-- Pagination Component: windowed (nomor awal/akhir + ellipsis) & rounded -->
@props(['paginator'])

@if($paginator->hasPages())
    @php
        $totalPages = $paginator->lastPage();
        $current    = $paginator->currentPage();
        $window     = 2; // nomor di kiri & kanan halaman aktif

        // Halaman yang selalu tampil: pertama, terakhir, dan jendela di sekitar halaman aktif
        $shown = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || abs($i - $current) <= $window) {
                $shown[] = $i;
            }
        }

        // Susun list dengan ellipsis pada celah lebih dari satu nomor
        $items = [];
        $prev = 0;
        foreach ($shown as $p) {
            if ($p - $prev > 1) {
                $items[] = '...';
            }
            $items[] = $p;
            $prev = $p;
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Menampilkan {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </div>
        <nav aria-label="Paginasi">
            <ul class="pagination pagination-sm mb-0 agx-pagination">
                {{-- Previous --}}
                @if($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&laquo;</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">&laquo;</a></li>
                @endif

                {{-- Nomor halaman (awal, jendela, akhir, dengan ellipsis) --}}
                @foreach($items as $item)
                    @if($item === '...')
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">&hellip;</span></li>
                    @elseif($item === $current)
                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $item }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($item) }}">{{ $item }}</a></li>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($paginator->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">&raquo;</a></li>
                @else
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&raquo;</span></li>
                @endif
            </ul>
        </nav>
    </div>

    <style>
        .agx-pagination .page-item { margin: 0 2px; }
        .agx-pagination .page-link {
            border-radius: 0.45rem;
            min-width: 2rem;
            text-align: center;
        }
        .agx-pagination .page-link:hover { z-index: 0; }
        .agx-pagination .active > .page-link {
            border-color: var(--tinta, #10263f);
            background: var(--tinta, #10263f);
            color: var(--kuning, #ffc72c);
        }
    </style>
@endif