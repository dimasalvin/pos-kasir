@extends('layouts.dashboard')
@section('title', 'Edit Barang')
@section('page-title', 'Edit Barang')

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-header">
        <div class="card-title">✏️ Edit Barang: {{ $barang->nama_barang }}</div>
        <a href="{{ route('barang.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('barang.update', $barang) }}">
            @csrf @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Barang *</label>
                    <input type="text" name="kode_barang" class="form-control" value="{{ old('kode_barang', $barang->kode_barang) }}" required>
                    @error('kode_barang') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Barcode *</label>
                    <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $barang->barcode) }}" required>
                    @error('barcode') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Barang *</label>
                <input type="text" name="nama_barang" class="form-control" value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                @error('nama_barang') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Satuan *</label>
                    <select name="satuan" class="form-control" required>
                        @foreach(['pcs', 'box', 'strip', 'botol', 'tube', 'sachet', 'tablet', 'kapsul', 'ampul'] as $s)
                            <option value="{{ $s }}" {{ old('satuan', $barang->satuan) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori *</label>
                    <select name="kategori_id" class="form-control" required>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ old('kategori_id', $barang->kategori_id) == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">Pilih Supplier (opsional)</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id', $barang->supplier_id) == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Harga Beli *</label>
                    <input type="text" name="harga_beli" class="form-control input-rupiah" value="{{ old('harga_beli', $barang->harga_beli) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual Umum *</label>
                    <input type="text" name="harga_jual_umum" class="form-control input-rupiah" value="{{ old('harga_jual_umum', $barang->harga_jual_umum) }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Harga Jual Resep *</label>
                    <input type="text" name="harga_jual_resep" class="form-control input-rupiah" value="{{ old('harga_jual_resep', $barang->harga_jual_resep) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stok *</label>
                    <input type="number" name="stok" class="form-control" value="{{ old('stok', $barang->stok) }}" min="0" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Stok Minimum *</label>
                <input type="number" name="stok_minimum" class="form-control" value="{{ old('stok_minimum', $barang->stok_minimum) }}" min="0" required>
            </div>

            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">💾 Update</button>
                <a href="{{ route('barang.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
