<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        .hdr { text-align: center; margin-bottom: 18px; }
        .hdr h1 { font-size: 16px; margin: 0 0 2px; text-transform: uppercase; letter-spacing: .03em; }
        .hdr p { margin: 0; font-size: 11px; color: #555; }
        .meta { font-size: 10px; color: #555; margin-bottom: 14px; }
        .meta span { display: inline-block; margin-right: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #e6e6e6; font-weight: 700; }
        tr:nth-child(even) td { background: #f7f7f7; }
        .empty { text-align: center; color: #777; padding: 24px; }
    </style>
</head>
<body>
    <div class="hdr">
        <h1>{{ $meta['title'] }}</h1>
        <p>PT Pindad (Persero) &mdash; Satuan Pengawasan Internal</p>
    </div>
    <div class="meta">
        <span>Dicetak: {{ $meta['generated_at'] }} WIB</span>
        <span>Oleh: {{ $meta['generated_by'] }}</span>
        <span>Total Data: {{ count($rows) }}</span>
    </div>
    <table>
        <thead>
            <tr>
                @foreach($headers as $h)<th>{{ $h }}</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)<td>{{ $cell }}</td>@endforeach
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ count($headers) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
