@extends('layouts.dashboard')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

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
                    <td style="font-size:13px; color:var(--muted);">{{ $t->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $t->pelanggan ?? '-' }}</td>
                    <td><span class="badge {{ $t->tipe_harga === 'resep' ? 'badge-purple' : 'badge-teal' }}">{{ $t->tipe_harga }}</span></td>
                    <td style="font-weight:700;">Rp {{ number_format($t->grand_total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $t->metode_bayar === 'cash' ? 'badge-teal' : 'badge-sky' }}">{{ $t->metode_bayar }}</span></td>
                    <td style="font-size:13px;">{{ $t->user->name }}</td>
                    <td><a href="{{ route('kasir.struk', $t) }}" class="btn btn-ghost btn-sm" target="_blank">🧾</a></td>
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
