<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arsip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h2 { margin: 0 0 4px; font-size: 16px; }
        .info { margin: 0 0 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #eef2ff; }
        .center { text-align: center; }
        .footer { margin-top: 12px; color: #666; }
    </style>
</head>
<body>
    <h2>Laporan Arsip</h2>
    <div class="info">
        {{ implode('  |  ', $infoFilter) }}<br>
        Dicetak {{ now()->format('d M Y, H:i') }} oleh {{ auth()->user()->nama }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: 14%">No. Arsip</th>
                <th>Judul</th>
                <th style="width: 14%">Kategori</th>
                <th style="width: 11%">Tanggal</th>
                <th style="width: 14%">Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($arsips as $arsip)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $arsip->nomor_arsip }}</td>
                    <td>{{ $arsip->judul }}</td>
                    <td>{{ $arsip->kategori->nama }}</td>
                    <td>{{ $arsip->tanggal_arsip->format('d M Y') }}</td>
                    <td>{{ $arsip->pembuat->nama ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Total: {{ $arsips->count() }} arsip</div>
</body>
</html>