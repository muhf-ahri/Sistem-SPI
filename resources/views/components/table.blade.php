<!-- Table Component -->
@props(['headers', 'rows'])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table table-hover table-bordered']) }}>
        <thead class="table-light">
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>