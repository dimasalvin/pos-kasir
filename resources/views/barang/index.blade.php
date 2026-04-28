@extends('layouts.dashboard')
@section('title', 'Data Barang')
@section('page-title', 'Data Barang')

@section('content')
{{-- Filter --}}
<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Cari</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
               placeholder="Nama / kode / barcode...">
    </div>
    <div class="form-group">
        <label class="form-label">Kategori</label>
        <select name="kategori" class="form-control">
            <option value="">Semua</option>
            @foreach($kategoris as $k)
                <option value="{{ $k->id }}" {{ request('kategori') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Stok</label>
        <select name="stok_filter" class="form-control">
            <option value="">Semua</option>
            <option value="rendah" {{ request('stok_filter') === 'rendah' ? 'selected' : '' }}>Stok Rendah</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
    </div>
    <div class="form-group">
        <a href="{{ route('barang.index') }}" class="btn btn-ghost">Reset</a>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📦 Daftar Barang</div>
            <div class="card-subtitle">{{ $barangs->total() }} barang</div>
        </div>
        <a href="{{ route('barang.create') }}" class="btn btn-primary">+ Tambah Barang</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Barcode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Harga Beli</th>
                    <th>Harga Umum</th>
                    <th>Harga Resep</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barangs as $b)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($barangs->currentPage()-1) * $barangs->perPage() }}</td>
                    <td><strong>{{ $b->kode_barang }}</strong></td>
                    <td style="font-size:12px; color:var(--muted);">{{ $b->barcode }}</td>
                    <td><strong>{{ $b->nama_barang }}</strong></td>
                    <td>
                        <span class="badge" style="background:{{ $b->kategori->warna ?? '#E2E8F0' }}20; color:{{ $b->kategori->warna ?? '#7A90A8' }};">
                            {{ $b->kategori->nama }}
                        </span>
                    </td>
                    <td>{{ $b->satuan }}</td>
                    <td style="font-size:13px;">Rp {{ number_format($b->harga_beli, 0, ',', '.') }}</td>
                    <td style="font-size:13px;">Rp {{ number_format($b->harga_jual_umum, 0, ',', '.') }}</td>
                    <td style="font-size:13px;">Rp {{ number_format($b->harga_jual_resep, 0, ',', '.') }}</td>
                    <td>
                        @if($b->isStokRendah())
                            <span class="badge badge-coral">{{ $b->stok }} / {{ $b->stok_minimum }}</span>
                        @else
                            <span class="badge badge-teal">{{ $b->stok }}</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('barang.edit', $b) }}" class="btn btn-ghost btn-sm">✏️</a>
                        <form method="POST" action="{{ route('barang.destroy', $b) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus barang ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center; color:var(--muted); padding:40px;">
                        Belum ada data barang
                    </td>
                </tr>
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
