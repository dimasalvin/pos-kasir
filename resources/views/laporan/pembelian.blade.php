@extends('layouts.dashboard')
@section('title', 'Laporan Pembelian')
@section('page-title', 'Laporan Pembelian')

@section('content')
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

<div class="stats-grid mb-28">
    <div class="stat-card sky">
        <div class="stat-icon">📥</div>
        <div class="stat-value">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</div>
        <div class="stat-label">Total Pembelian ({{ $dari }} s/d {{ $sampai }})</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">📉 Detail Pembelian</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Faktur</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelians as $p)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($pembelians->currentPage()-1) * $pembelians->perPage() }}</td>
                    <td><strong>{{ $p->no_faktur }}</strong></td>
                    <td style="font-size:13px; color:var(--muted);">{{ $p->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $p->supplier->nama }}</td>
                    <td style="font-weight:700;">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</td>
                    <td style="font-size:13px;">{{ $p->user->name }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center; color:var(--muted); padding:40px;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pembelians->hasPages())
    <div class="pagination">
        @foreach($pembelians->getUrlRange(1, $pembelians->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $pembelians->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
    </div>
    @endif
</div>
@endsection
