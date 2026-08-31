<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 16mm 14mm 18mm 14mm;
            @bottom-center {
                content: "Halaman " counter(page) " dari " counter(pages) "  |  PT Pindad Enjiniring Indonesia - Satuan Pengawasan Internal  |  " "{{ $meta['generated_at'] }}";
                font-size: 8px; color: #888;
            }
        }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #222; margin: 0; }

        .hdr { text-align: center; padding-bottom: 8px; border-bottom: 2px solid #1f4e79; margin-bottom: 10px; }
        .hdr .inst { font-size: 9px; color: #666; margin: 0 0 4px; }
        .hdr h1 { font-size: 15px; margin: 0 0 2px; text-transform: uppercase; letter-spacing: .04em; color: #1f4e79; }
        .hdr .sub { font-size: 9px; color: #444; margin: 0; }

        .meta { font-size: 8.5px; color: #555; margin-bottom: 8px; }
        .meta span { display: inline-block; }

        table { width: 100%; border-collapse: collapse; table-layout: auto; }
        thead { display: table-header-group; }
        th, td { border: 0.6pt solid #999; padding: 4px 6px; text-align: left; vertical-align: middle; word-wrap: break-word; }
        th { background: #1f4e79; color: #fff; font-weight: 700; }
        tr:nth-child(even) td { background: #f2f6fb; }
        tbody tr:hover td { background: #e7eef7; }
        .empty { text-align: center; color: #777; padding: 18px; font-style: italic; }
        td.num { text-align: right; }
        .foot { margin-top: 10px; font-size: 8.5px; color: #555; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="hdr">
        <p class="inst">PT Pindad Enjiniring Indonesia</p>
        <h1>{{ $meta['title'] }}</h1>
        <p class="sub">Satuan Pengawasan Internal</p>
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
                    @foreach($row as $i => $cell)
                        <td class="{{ is_numeric($cell) ? 'num' : '' }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ count($headers) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="foot">
        <span>Dokumen ini dihasilkan otomatis oleh Sistem SPI.</span>
        <span>Total {{ count($rows) }} data</span>
    </div>
</body>
</html>
