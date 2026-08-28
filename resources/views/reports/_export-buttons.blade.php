@php
    $exportQuery = array_merge(['type' => $type], request()->query());
@endphp
<a href="{{ route('reports.export', array_merge($exportQuery, ['format' => 'excel'])) }}"
   class="btn btn-outline-success sdx-export-btn" title="Cetak ke Excel">
    <i class="bi bi-file-earmark-excel me-1"></i>Excel
</a>
<a href="{{ route('reports.export', array_merge($exportQuery, ['format' => 'pdf'])) }}"
   class="btn btn-outline-danger sdx-export-btn" title="Cetak ke PDF">
    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
</a>
