<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Kas</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Nunito','Segoe UI', sans-serif; font-size:13px; padding:20px; }
        h2 { background:#1A2B3C; color:white; padding:12px 16px; font-size:18px; margin-bottom:0; border-radius:10px 10px 0 0; }
        table { width:100%; border-collapse:collapse; }
        th { background:#F0F4F8; color:#7A90A8; padding:8px 12px; font-size:11px; text-align:left;
             text-transform:uppercase; letter-spacing:.06em; font-weight:800; border-bottom:2px solid #E2E8F0; }
        td { padding:8px 12px; border-bottom:1px solid #E2E8F0; }
        .saldo-table { margin-top:16px; width:auto; }
        .saldo-table td { font-size:14px; padding:6px 16px; }
        .saldo-table .label { font-weight:800; }
        .saldo-table .highlight td { background:#2BBFA4; color:white; font-weight:800; border-radius:6px; }
        .actions { margin-top:20px; text-align:center; }
        .actions button { padding:10px 30px; font-size:14px; font-weight:700; cursor:pointer;
                          border:none; background:#2BBFA4; color:white; border-radius:8px; }
        @media print {
            .actions { display:none; }
            body { padding:0; }
        }
    </style>
</head>
<body>
    <h2>Laporan Kas Apotek</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Kredit</th>
                <th>Debit</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $e)
            <tr>
                <td>{{ $e->tanggal->format('d-m-Y') }}</td>
                <td>{{ $e->keterangan }}</td>
                <td>{{ $e->kredit > 0 ? number_format($e->kredit, 0, ',', '.') : '' }}</td>
                <td>{{ $e->debit > 0 ? number_format($e->debit, 0, ',', '.') : '' }}</td>
                <td>{{ $e->tanggal_transaksi ? $e->tanggal_transaksi->format('d-m-Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="saldo-table">
        <tr>
            <td class="label">Saldo awal</td>
            <td>{{ number_format($ringkasan['saldo_awal'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Debit</td>
            <td>{{ number_format($ringkasan['total_debit'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Kredit</td>
            <td>{{ number_format($ringkasan['total_kredit'], 0, ',', '.') }}</td>
        </tr>
        <tr class="highlight">
            <td class="label">Saldo akhir</td>
            <td>{{ number_format($ringkasan['saldo_akhir'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="actions">
        <button onclick="window.print()">🖨️ CETAK</button>
    </div>
</body>
</html>
