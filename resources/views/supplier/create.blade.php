@extends('layouts.dashboard')
@section('title', 'Tambah Supplier')
@section('page-title', 'Tambah Supplier')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <div class="card-title">🏭 Form Tambah Supplier</div>
        <a href="{{ route('supplier.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('supplier.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Supplier *</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Jatuh Tempo (hari) *</label>
                    <input type="number" name="jatuh_tempo" class="form-control" value="{{ old('jatuh_tempo', 30) }}" min="1" required>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <a href="{{ route('supplier.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
