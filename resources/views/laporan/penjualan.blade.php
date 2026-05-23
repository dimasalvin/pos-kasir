@extends('layouts.dashboard')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@push('styles')
<style>
.obat-search-wrap { position:relative; }
.obat-results { position:absolute; top:100%; left:0; right:0; background:var(--surface);
                border:1px solid var(--border); border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,.12);
                max-height:250px; overflow-y:auto; z-index:120; display:none; }
.obat-results.show { display:block; }
.obat-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid var(--border);
             font-size:13px; transition:background .1s; }
.obat-item:hover { background:var(--teal-light); }
.obat-item:last-child { border-bottom:none; }
.obat-item .obat-name { font-weight:700; }
.obat-item .obat-info { font-size:11px; color:var(--muted); margin-top:2px; }
</style>
@endpush

@section('content')
{{-- Filter --}}
<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Dari</label>
        <input type="date" name="dari" class="form-control" value="{{ $dari }}">
    </div>
    <div class="form-group">
        <label class="form-label">Sampai</label>
        <input type="date" name="sampai" class="form-control" value="{{ $sampai }}">
    </div>
    <div class="form-group obat-search-wrap">
        <label class="form-label">Nama Obat</label>
        <input type="text" name="nama_barang" id="namaBarangInput" class="form-control" value="{{ $namaBarang ?? '' }}" placeholder="Ketik nama obat..." autocomplete="off">
        <div class="obat-results" id="obatResults"></div>
    </div>
    <div class="form-group">
        <label class="form-label">Shift</label>
        <select name="shift" class="form-control">
            <option value="">Semua Shift</option>
            <option value="pagi" {{ ($shift ?? '') === 'pagi' ? 'selected' : '' }}>Pagi (07:00 - 13:59)</option>
            <option value="siang" {{ ($shift ?? '') === 'siang' ? 'selected' : '' }}>Siang (14:00 - 21:00)</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Metode Bayar</label>
        <select name="metode" class="form-control">
            <option value="">Semua</option>
            <option value="cash" {{ ($metode ?? '') === 'cash' ? 'selected' : '' }}>💵 Tunai</option>
            <option value="non-cash" {{ ($metode ?? '') === 'non-cash' ? 'selected' : '' }}>💳 Non Tunai</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">📊 Tampilkan</button>
    </div>
</form>

{{-- Ringkasan --}}
<div class="stats-grid mb-28">
    <div class="stat-card teal">
        <div class="stat-icon">🧾</div>
        <div class="stat-value">{{ number_format($ringkasan->jumlah_nota ?? 0) }}</div>
        <div class="stat-label">Jumlah Nota</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">💰</div>
        <div class="stat-value">Rp {{ number_format($ringkasan->total_transaksi ?? 0, 0, ',', '.') }}</div>
        <div class="stat-label">Total Transaksi</div>
    </div>
    <div class="stat-card sky">
        <div class="stat-icon">💵</div>
        <div class="stat-value">Rp {{ number_format($ringkasan->total_cash ?? 0, 0, ',', '.') }}</div>
        <div class="stat-label">Total Cash</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon">💳</div>
        <div class="stat-value">Rp {{ number_format($ringkasan->total_non_cash ?? 0, 0, ',', '.') }}</div>
        <div class="stat-label">Total Non-Cash</div>
    </div>
</div>

{{-- Riwayat Penjualan Obat (muncul jika filter nama barang aktif) --}}
@if(!empty($namaBarang) && $riwayatObat && $riwayatObat->count() > 0)
<div class="card mb-20">
    <div class="card-header">
        <div>
            <div class="card-title">💊 Riwayat Penjualan Obat: "{{ $namaBarang }}"</div>
            <div class="card-subtitle">Ditemukan {{ $riwayatObat->count() }} penjualan — Total Qty: {{ $riwayatObat->sum('qty') }} — Total: Rp {{ number_format($riwayatObat->sum('subtotal'), 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Obat</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Diskon</th>
                    <th>Subtotal</th>
                    <th>No. Nota</th>
                    <th>Tanggal</th>
                    <th>Shift</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                @foreach($riwayatObat as $r)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration }}</td>
                    <td style="font-weight:600;">{{ $r->nama_barang }}</td>
                    <td style="text-align:center; font-weight:700;">{{ $r->qty }}</td>
                    <td style="text-align:right;">Rp {{ number_format($r->harga, 0, ',', '.') }}</td>
                    <td style="text-align:right;">{{ $r->diskon > 0 ? 'Rp ' . number_format($r->diskon, 0, ',', '.') : '-' }}</td>
                    <td style="text-align:right; font-weight:700;">Rp {{ number_format($r->subtotal, 0, ',', '.') }}</td>
                    <td><strong>{{ $r->no_nota }}</strong></td>
                    <td style="font-size:13px; color:var(--muted);">{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                    <td>
                        @php $jam = \Carbon\Carbon::parse($r->created_at)->format('H:i:s'); @endphp
                        <span class="badge {{ $jam < '14:00:00' ? 'badge-gold' : 'badge-purple' }}">
                            {{ $jam < '14:00:00' ? 'Pagi' : 'Siang' }}
                        </span>
                    </td>
                    <td><span class="badge {{ $r->metode_bayar === 'cash' ? 'badge-teal' : 'badge-sky' }}">{{ $r->metode_bayar }}</span></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight:800; background:var(--bg);">
                    <td colspan="2" style="text-align:right;">Total</td>
                    <td style="text-align:center;">{{ $riwayatObat->sum('qty') }}</td>
                    <td></td>
                    <td style="text-align:right;">Rp {{ number_format($riwayatObat->sum('diskon'), 0, ',', '.') }}</td>
                    <td style="text-align:right; color:var(--teal-dark);">Rp {{ number_format($riwayatObat->sum('subtotal'), 0, ',', '.') }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@elseif(!empty($namaBarang) && ($riwayatObat === null || $riwayatObat->count() === 0))
<div class="alert alert-warning mb-20">⚠️ Tidak ditemukan penjualan obat "{{ $namaBarang }}" pada periode ini.</div>
@endif

{{-- Tabel --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📈 Detail Penjualan</div>
            <div class="card-subtitle">{{ $dari }} s/d {{ $sampai }}</div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Nota</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Tipe</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Kasir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $t)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($transaksis->currentPage()-1) * $transaksis->perPage() }}</td>
                    <td><strong>{{ $t->no_nota }}</strong></td>
                    <td style="font-size:13px; color:var(--muted);">{{ $t->tanggal->format('d/m/Y') }} <span style="font-size:11px;">{{ $t->created_at->format('H:i') }}</span></td>
                    <td>
                        {{ $t->pelanggan ?? '-' }}
                        @if($t->has_resep && $t->pasien_nama)
                            <div style="font-size:11px; color:var(--muted);">Pasien: {{ $t->pasien_nama }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $t->has_resep ? 'badge-purple' : 'badge-teal' }}">{{ $t->has_resep ? 'Resep' : 'Reguler' }}</span>
                        @if($t->has_minus_stok)
                            <span class="badge badge-danger" title="Transaksi ini mengakibatkan stok minus" style="background:#F0AD4E; color:#fff; font-size:9px;">⚠️ MINUS</span>
                        @endif
                    </td>
                    <td style="font-weight:700;">Rp {{ number_format($t->grand_total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $t->metode_bayar === 'cash' ? 'badge-teal' : 'badge-sky' }}">{{ $t->metode_bayar }}</span></td>
                    <td style="font-size:13px;">{{ $t->user->name }}</td>
                    <td style="white-space:nowrap;">
                        <button onclick="toggleDetail({{ $t->id }})" class="btn btn-ghost btn-sm" title="Lihat Detail">📦</button>
                        <a href="{{ route('kasir.struk', $t) }}" class="btn btn-ghost btn-sm" target="_blank" title="Struk">🧾</a>
                    </td>
                </tr>
                {{-- Detail row (hidden by default) --}}
                <tr id="detail-{{ $t->id }}" style="display:none;">
                    <td colspan="9" style="padding:0;">
                        <div style="background:var(--bg); padding:16px; border-radius:0 0 10px 10px;">
                            <div style="font-weight:800; font-size:13px; margin-bottom:8px;">📦 Detail Obat - {{ $t->no_nota }}</div>
                            <table style="width:100%; font-size:12px;">
                                <thead>
                                    <tr style="background:var(--surface);">
                                        <th style="padding:6px 10px; text-align:left;">No</th>
                                        <th style="padding:6px 10px; text-align:left;">Nama Obat</th>
                                        <th style="padding:6px 10px; text-align:center;">Qty</th>
                                        <th style="padding:6px 10px; text-align:right;">Harga</th>
                                        <th style="padding:6px 10px; text-align:right;">Diskon</th>
                                        <th style="padding:6px 10px; text-align:right;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($t->details as $d)
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <td style="padding:6px 10px;">{{ $loop->iteration }}</td>
                                        <td style="padding:6px 10px; font-weight:600;">{{ $d->nama_barang }}</td>
                                        <td style="padding:6px 10px; text-align:center;">{{ $d->qty }}</td>
                                        <td style="padding:6px 10px; text-align:right;">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                                        <td style="padding:6px 10px; text-align:right;">{{ $d->diskon > 0 ? 'Rp ' . number_format($d->diskon, 0, ',', '.') : '-' }}</td>
                                        <td style="padding:6px 10px; text-align:right; font-weight:700;">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight:800; background:var(--surface);">
                                        <td colspan="5" style="padding:8px 10px; text-align:right;">Total:</td>
                                        <td style="padding:8px 10px; text-align:right; color:var(--teal-dark);">Rp {{ number_format($t->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center; color:var(--muted); padding:40px;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transaksis->hasPages())
    <div class="pagination">
        @foreach($transaksis->getUrlRange(1, $transaksis->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $transaksis->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}

// ── Autocomplete Nama Obat ──
(function() {
    let timeout = null;
    const input = document.getElementById('namaBarangInput');
    const results = document.getElementById('obatResults');

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const val = this.value.trim();
        if (val.length < 2) {
            results.classList.remove('show');
            return;
        }

        timeout = setTimeout(() => {
            fetch(`{{ route('api.barang.search') }}?q=${encodeURIComponent(val)}`)
                .then(r => r.json())
                .then(items => {
                    if (items.length === 0) {
                        results.classList.remove('show');
                    } else {
                        results.innerHTML = items.map(b => `
                            <div class="obat-item" data-nama="${b.nama_barang}">
                                <div class="obat-name">${b.nama_barang}</div>
                                <div class="obat-info">${b.kode_barang} | Stok: ${b.stok}</div>
                            </div>
                        `).join('');
                        results.classList.add('show');

                        // Bind click
                        results.querySelectorAll('.obat-item').forEach(el => {
                            el.addEventListener('click', function() {
                                input.value = this.dataset.nama;
                                results.classList.remove('show');
                            });
                        });
                    }
                });
        }, 300);
    });

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(e) {
        if (!results.contains(e.target) && e.target !== input) {
            results.classList.remove('show');
        }
    });
})();
</script>
@endpush
