@extends('layouts.dashboard')
@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok')

@section('content')
{{-- Ringkasan --}}
<div class="stats-grid mb-28">
    <div class="stat-card teal">
        <div class="stat-icon">📦</div>
        <div class="stat-value">{{ number_format($ringkasanStok['total_item']) }}</div>
        <div class="stat-label">Total Item</div>
    </div>
    <div class="stat-card coral">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value">{{ number_format($ringkasanStok['stok_rendah']) }}</div>
        <div class="stat-label">Stok Rendah</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">💰</div>
        <div class="stat-value">Rp {{ number_format($ringkasanStok['total_nilai'], 0, ',', '.') }}</div>
        <div class="stat-label">Nilai Inventori</div>
    </div>
</div>

<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Filter</label>
        <select name="filter" class="form-control">
            <option value="">Semua</option>
            <option value="rendah" {{ request('filter') === 'rendah' ? 'selected' : '' }}>Stok Rendah</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div class="card-title">📊 Laporan Stok Barang</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th>Stok</th>
                    <th>Min</th>
                    <th>Harga Beli</th>
                    <th>Nilai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barangs as $b)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($barangs->currentPage()-1) * $barangs->perPage() }}</td>
                    <td><strong>{{ $b->kode_barang }}</strong></td>
                    <td>{{ $b->nama_barang }}</td>
                    <td><span class="badge" style="background:{{ $b->kategori->warna ?? '#E2E8F0' }}20; color:{{ $b->kategori->warna ?? '#7A90A8' }};">{{ $b->kategori->nama }}</span></td>
                    <td style="font-size:13px;">{{ $b->supplier->nama ?? '-' }}</td>
                    <td style="font-weight:800;">{{ $b->stok }}</td>
                    <td style="color:var(--muted);">{{ $b->stok_minimum }}</td>
                    <td>Rp {{ number_format($b->harga_beli, 0, ',', '.') }}</td>
                    <td style="font-weight:700;">Rp {{ number_format($b->stok * $b->harga_beli, 0, ',', '.') }}</td>
                    <td>
                        @if($b->isStokRendah())
                            <span class="badge badge-coral">Rendah</span>
                        @else
                            <span class="badge badge-teal">Aman</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" style="text-align:center; color:var(--muted); padding:40px;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($barangs->hasPages())
    <div class="pagination">
        @foreach($barangs->getUrlRange(1, $barangs->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $barangs->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
    </div>
    @endif
</div>
@endsection
