<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Closing Kasir</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Nunito','Segoe UI', sans-serif; font-size:13px; padding:20px; }
        h2 { background:#1A2B3C; color:white; padding:12px 16px; font-size:18px; margin-bottom:0; border-radius:10px 10px 0 0; }
        table { width:100%; border-collapse:collapse; }
        th { background:#F0F4F8; color:#7A90A8; padding:8px 12px; font-size:11px; text-align:left;
             text-transform:uppercase; letter-spacing:.06em; font-weight:800; border-bottom:2px solid #E2E8F0; }
        td { padding:8px 12px; border-bottom:1px solid #E2E8F0; }
        .footer-row td { background:#F0F4F8; font-weight:800; }
        .info-row td { background:#F8FAFC; font-size:12px; }
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
    <h2>Closing Kasir</h2>
    <table>
        <tr class="info-row">
            <td><strong>Periode</strong></td>
            <td>{{ \Carbon\Carbon::parse($dari)->format('d-m-Y') }}</td>
            <td>s/d</td>
            <td>{{ \Carbon\Carbon::parse($sampai)->format('d-m-Y') }}</td>
            <td><strong>Shift</strong></td>
            <td>{{ $shiftFilter ? ucfirst($shiftFilter) : 'Semua' }}</td>
        </tr>
    </table>

    <table style="margin-top:4px;">
        <thead>
            <tr>
                <th>Shift</th>
                <th>Tanggal</th>
                <th>R/</th>
                <th>HV</th>
                <th>Pendapatan R/</th>
                <th>Pendapatan HV</th>
                <th>Total Pendapatan</th>
                <th>Non Tunai</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($closings as $c)
            <tr>
                <td>{{ ucfirst($c->shift) }}</td>
                <td>{{ $c->tanggal->format('d-m-Y') }}</td>
                <td>{{ $c->jumlah_resep }}</td>
                <td>{{ $c->jumlah_hv }}</td>
                <td>{{ number_format($c->pendapatan_resep, 0, ',', '.') }}</td>
                <td>{{ number_format($c->pendapatan_hv, 0, ',', '.') }}</td>
                <td>{{ number_format($c->total_pendapatan, 0, ',', '.') }}</td>
                <td>{{ number_format($c->non_tunai, 0, ',', '.') }}</td>
                <td style="font-weight:700;">{{ number_format($c->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="2" style="text-align:right;">TOTAL</td>
                <td>{{ $totals['jumlah_resep'] }}</td>
                <td>{{ $totals['jumlah_hv'] }}</td>
                <td>{{ number_format($totals['pendapatan_resep'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['pendapatan_hv'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['total_pendapatan'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['non_tunai'], 0, ',', '.') }}</td>
                <td>{{ number_format($totals['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="actions">
        <button onclick="window.print()">🖨️ CETAK</button>
    </div>
</body>
</html>
