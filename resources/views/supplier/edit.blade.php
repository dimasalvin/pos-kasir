@extends('layouts.dashboard')
@section('title', 'Edit Supplier')
@section('page-title', 'Edit Supplier')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <div class="card-title">✏️ Edit Supplier: {{ $supplier->nama }}</div>
        <a href="{{ route('supplier.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('supplier.update', $supplier) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Supplier *</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $supplier->nama) }}" required>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $supplier->alamat) }}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $supplier->no_telp) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Jatuh Tempo (hari) *</label>
                    <input type="number" name="jatuh_tempo" class="form-control" value="{{ old('jatuh_tempo', $supplier->jatuh_tempo) }}" min="1" required>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">💾 Update</button>
                <a href="{{ route('supplier.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
