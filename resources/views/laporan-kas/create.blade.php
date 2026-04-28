@extends('layouts.dashboard')
@section('title', 'Tambah Entri Kas')
@section('page-title', 'Tambah Entri Kas')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <div class="card-title">💰 Form Entri Kas</div>
        <a href="{{ route('laporan-kas.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                ⚠️ Terjadi kesalahan:
                <ul style="margin:8px 0 0 16px; font-size:12px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('laporan-kas.store') }}">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tanggal Pencatatan *</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="date" name="tanggal_transaksi" class="form-control" value="{{ old('tanggal_transaksi') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan *</label>
                <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}"
                       placeholder="Contoh: Pembayaran iuran sampah, Pajak MMT..." required>
                @error('keterangan') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipe *</label>
                    <select name="tipe" class="form-control" required>
                        <option value="kredit" {{ old('tipe') === 'kredit' ? 'selected' : '' }}>Kredit (Uang Keluar)</option>
                        <option value="debit" {{ old('tipe') === 'debit' ? 'selected' : '' }}>Debit (Uang Masuk)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah (Rp) *</label>
                    <input type="text" name="jumlah" class="form-control input-rupiah" value="{{ old('jumlah') }}"
                           required placeholder="Rp 0" style="font-weight:700;">
                    @error('jumlah') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <a href="{{ route('laporan-kas.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
