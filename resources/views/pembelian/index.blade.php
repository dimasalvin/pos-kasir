@extends('layouts.dashboard')
@section('title', 'Data Pembelian')
@section('page-title', 'Data Pembelian')

@section('content')
<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">No. Faktur</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari faktur...">
    </div>
    <div class="form-group">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-control">
            <option value="">Semua</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Dari</label>
        <input type="date" name="dari" class="form-control" value="{{ request('dari') }}">
    </div>
    <div class="form-group">
        <label class="form-label">Sampai</label>
        <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📥 Daftar Pembelian</div>
            <div class="card-subtitle">{{ $pembelians->total() }} pembelian</div>
        </div>
        <a href="{{ route('pembelian.create') }}" class="btn btn-primary">+ Tambah Pembelian</a>
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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelians as $p)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($pembelians->currentPage()-1) * $pembelians->perPage() }}</td>
                    <td><strong>{{ $p->no_faktur }}</strong></td>
                    <td style="color:var(--muted); font-size:13px;">{{ $p->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $p->supplier->nama }}</td>
                    <td style="font-weight:700;">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</td>
                    <td style="font-size:13px;">{{ $p->user->name }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('pembelian.show', $p) }}" class="btn btn-ghost btn-sm">👁️</a>
                        <form method="POST" action="{{ route('pembelian.destroy', $p) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus pembelian ini? Stok akan dikembalikan.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:var(--muted); padding:40px;">Belum ada data pembelian</td></tr>
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
