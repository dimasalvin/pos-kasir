<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $transaksi->no_nota }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New', monospace; font-size:12px; background:#f5f5f5;
               display:flex; justify-content:center; padding:20px; }
        .struk { background:white; width:300px; padding:20px; box-shadow:0 2px 20px rgba(0,0,0,.1); }
        .struk-header { text-align:center; border-bottom:1px dashed #333; padding-bottom:10px; margin-bottom:10px; }
        .struk-header h2 { font-size:16px; margin-bottom:2px; }
        .struk-header p { font-size:10px; color:#666; }
        .struk-info { margin-bottom:10px; font-size:11px; }
        .struk-info div { display:flex; justify-content:space-between; padding:1px 0; }
        .struk-items { border-top:1px dashed #333; border-bottom:1px dashed #333;
                       padding:8px 0; margin-bottom:8px; }
        .struk-item { margin-bottom:6px; }
        .struk-item .name { font-weight:bold; }
        .struk-item .detail { display:flex; justify-content:space-between; font-size:11px; color:#555; }
        .struk-total { font-size:11px; }
        .struk-total div { display:flex; justify-content:space-between; padding:2px 0; }
        .struk-total .grand { font-size:14px; font-weight:bold; border-top:1px dashed #333;
                              padding-top:6px; margin-top:4px; }
        .struk-footer { text-align:center; margin-top:12px; font-size:10px; color:#999;
                        border-top:1px dashed #333; padding-top:10px; }
        .struk-actions { display:flex; gap:10px; justify-content:center; margin-top:20px; }
        .struk-actions a, .struk-actions button {
            padding:10px 20px; border-radius:8px; font-size:13px; font-weight:700;
            cursor:pointer; text-decoration:none; border:none; font-family:'Nunito',sans-serif;
        }
        .btn-print { background:#2BBFA4; color:white; }
        .btn-pdf { background:#f0f4f8; color:#1A2B3C; border:1px solid #E2E8F0; }
        @media print {
            body { background:white; padding:0; }
            .struk { box-shadow:none; width:80mm; }
            .struk-actions { display:none; }
        }
    </style>
</head>
<body>
    <div>
        <div class="struk" id="strukPrint">
            <div class="struk-header">
                <h2>KASIR POS</h2>
                <p>Jl. Contoh No. 123, Kota</p>
                <p>Telp: (021) 123-4567</p>
            </div>

            <div class="struk-info">
                <div><span>No. Nota</span><span>{{ $transaksi->no_nota }}</span></div>
                <div><span>Tanggal</span><span>{{ $transaksi->tanggal->format('d/m/Y H:i') }}</span></div>
                <div><span>Kasir</span><span>{{ $transaksi->user->name }}</span></div>
                @if($transaksi->pelanggan)
                <div><span>Pelanggan</span><span>{{ $transaksi->pelanggan }}</span></div>
                @endif
                <div><span>Tipe</span><span>{{ $transaksi->tipe_harga === 'resep' ? 'Resep' : 'Non Resep' }}</span></div>
                @if($transaksi->tipe_harga === 'resep' && $transaksi->pasien_nama)
                <div style="border-top:1px dashed #ccc; margin-top:4px; padding-top:4px;"><span><strong>Pasien:</strong></span><span>{{ $transaksi->pasien_nama }}</span></div>
                <div><span>Telp</span><span>{{ $transaksi->pasien_telp }}</span></div>
                <div><span>Alamat</span><span>{{ $transaksi->pasien_alamat }}</span></div>
                @endif
            </div>

            <div class="struk-items">
                @foreach($transaksi->details as $d)
                <div class="struk-item">
                    <div class="name">{{ $d->nama_barang }}</div>
                    <div class="detail">
                        <span>{{ $d->qty }} x Rp {{ number_format($d->harga, 0, ',', '.') }}
                            @if($d->diskon > 0) - Disc {{ number_format($d->diskon, 0, ',', '.') }} @endif
                        </span>
                        <span>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="struk-total">
                <div><span>Subtotal</span><span>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</span></div>
                @if($transaksi->diskon > 0)
                <div><span>Diskon</span><span>Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span></div>
                @endif
                <div class="grand"><span>TOTAL</span><span>Rp {{ number_format($transaksi->grand_total, 0, ',', '.') }}</span></div>
                <div><span>Bayar ({{ $transaksi->metode_bayar }})</span><span>Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</span></div>
                <div><span>Kembalian</span><span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span></div>
            </div>

            <div class="struk-footer">
                <p>Terima kasih atas kunjungan Anda!</p>
                <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
            </div>
        </div>

        <div class="struk-actions">
            <button class="btn-print" onclick="window.print()">🖨️ Print</button>
            <a class="btn-pdf" href="{{ route('kasir.struk-pdf', $transaksi) }}">📄 Download PDF</a>
        </div>
    </div>
</body>
</html>
