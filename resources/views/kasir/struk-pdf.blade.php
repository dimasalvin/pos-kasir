<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New', monospace; font-size:11px; width:80mm; }
        .center { text-align:center; }
        .header { text-align:center; border-bottom:1px dashed #000; padding-bottom:8px; margin-bottom:8px; }
        .header h2 { font-size:14px; }
        .header p { font-size:9px; }
        .info div { display:flex; justify-content:space-between; font-size:10px; padding:1px 0; }
        .items { border-top:1px dashed #000; border-bottom:1px dashed #000; padding:6px 0; margin:6px 0; }
        .item { margin-bottom:4px; }
        .item .name { font-weight:bold; font-size:10px; }
        .item .detail { display:flex; justify-content:space-between; font-size:10px; }
        .totals div { display:flex; justify-content:space-between; font-size:10px; padding:1px 0; }
        .totals .grand { font-weight:bold; font-size:12px; border-top:1px dashed #000; padding-top:4px; margin-top:4px; }
        .footer { text-align:center; font-size:9px; margin-top:8px; border-top:1px dashed #000; padding-top:6px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>KASIR POS</h2>
        <p>Jl. Contoh No. 123, Kota</p>
        <p>Telp: (021) 123-4567</p>
    </div>

    <div class="info">
        <div><span>No. Nota</span><span>{{ $transaksi->no_nota }}</span></div>
        <div><span>Tanggal</span><span>{{ $transaksi->tanggal->format('d/m/Y H:i') }}</span></div>
        <div><span>Kasir</span><span>{{ $transaksi->user->name }}</span></div>
        @if($transaksi->pelanggan)
        <div><span>Pelanggan</span><span>{{ $transaksi->pelanggan }}</span></div>
        @endif
    </div>

    <div class="items">
        @foreach($transaksi->details as $d)
        <div class="item">
            <div class="name">{{ $d->nama_barang }}</div>
            <div class="detail">
                <span>{{ $d->qty }} x {{ number_format($d->harga, 0, ',', '.') }}
                    @if($d->diskon > 0) -{{ number_format($d->diskon, 0, ',', '.') }}@endif
                </span>
                <span>{{ number_format($d->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="totals">
        <div><span>Subtotal</span><span>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</span></div>
        <div class="grand"><span>TOTAL</span><span>Rp {{ number_format($transaksi->grand_total, 0, ',', '.') }}</span></div>
        <div><span>Bayar</span><span>Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</span></div>
        <div><span>Kembalian</span><span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span></div>
    </div>

    <div class="footer">
        <p>Terima kasih!</p>
    </div>
</body>
</html>
