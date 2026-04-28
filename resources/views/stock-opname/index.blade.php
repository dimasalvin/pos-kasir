@extends('layouts.dashboard')
@section('title', 'Stock Opname')
@section('page-title', 'Stock Opname')

@section('content')
<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Cari Barang</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nama barang...">
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
            <div class="card-title">📋 Riwayat Stock Opname</div>
            <div class="card-subtitle">{{ $opnames->total() }} record</div>
        </div>
        <a href="{{ route('stock-opname.create') }}" class="btn btn-primary">+ Opname Baru</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Barang</th>
                    <th>Stok Sistem</th>
                    <th>Stok Fisik</th>
                    <th>Selisih</th>
                    <th>Keterangan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opnames as $o)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($opnames->currentPage()-1) * $opnames->perPage() }}</td>
                    <td style="font-size:13px;">{{ $o->tanggal->format('d/m/Y') }}</td>
                    <td><strong>{{ $o->barang->nama_barang }}</strong></td>
                    <td>{{ $o->stok_sistem }}</td>
                    <td>{{ $o->stok_fisik }}</td>
                    <td>
                        @if($o->selisih > 0)
                            <span class="badge badge-teal">+{{ $o->selisih }}</span>
                        @elseif($o->selisih < 0)
                            <span class="badge badge-coral">{{ $o->selisih }}</span>
                        @else
                            <span class="badge badge-sky">0</span>
                        @endif
                    </td>
                    <td style="font-size:12px; color:var(--muted); max-width:200px;">{{ Str::limit($o->keterangan, 40) }}</td>
                    <td style="font-size:13px;">{{ $o->user->name }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center; color:var(--muted); padding:40px;">Belum ada data stock opname</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($opnames->hasPages())
    <div class="pagination">
        @foreach($opnames->getUrlRange(1, $opnames->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $opnames->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
    </div>
    @endif
</div>
@endsection
