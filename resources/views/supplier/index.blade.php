@extends('layouts.dashboard')
@section('title', 'Data Supplier')
@section('page-title', 'Data Supplier')

@section('content')
<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Cari</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nama supplier...">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">🔍 Cari</button>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">🏭 Daftar Supplier</div>
            <div class="card-subtitle">{{ $suppliers->total() }} supplier</div>
        </div>
        <a href="{{ route('supplier.create') }}" class="btn btn-primary">+ Tambah Supplier</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No. Telp</th>
                    <th>Jatuh Tempo</th>
                    <th>Jml Barang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                <tr>
                    <td style="color:var(--muted); font-size:12px;">{{ $loop->iteration + ($suppliers->currentPage()-1) * $suppliers->perPage() }}</td>
                    <td><strong>{{ $s->nama }}</strong></td>
                    <td style="font-size:13px; color:var(--muted); max-width:200px;">{{ Str::limit($s->alamat, 50) }}</td>
                    <td>{{ $s->no_telp ?? '-' }}</td>
                    <td>{{ $s->jatuh_tempo }} hari</td>
                    <td><span class="badge badge-teal">{{ $s->barangs_count }}</span></td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('supplier.edit', $s) }}" class="btn btn-ghost btn-sm">✏️</a>
                        <form method="POST" action="{{ route('supplier.destroy', $s) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus supplier ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:var(--muted); padding:40px;">Belum ada data supplier</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="pagination">
        @foreach($suppliers->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $suppliers->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
    </div>
    @endif
</div>
@endsection
