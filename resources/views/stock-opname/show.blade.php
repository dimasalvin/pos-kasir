@extends('layouts.dashboard')
@section('title', 'Detail Stock Opname')
@section('page-title', 'Detail Stock Opname')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <div>
            <div class="card-title">📋 Detail Opname</div>
            <div class="card-subtitle">{{ $stockOpname->tanggal->format('d F Y') }}</div>
        </div>
        <a href="{{ route('stock-opname.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <div class="form-row mb-20">
            <div>
                <div class="form-label">Barang</div>
                <strong>{{ $stockOpname->barang->nama_barang }}</strong>
                <div style="font-size:12px; color:var(--muted);">{{ $stockOpname->barang->kode_barang }}</div>
            </div>
            <div>
                <div class="form-label">Dilakukan Oleh</div>
                <strong>{{ $stockOpname->user->name }}</strong>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card sky">
                <div class="stat-label">Stok Sistem</div>
                <div class="stat-value">{{ $stockOpname->stok_sistem }}</div>
            </div>
            <div class="stat-card teal">
                <div class="stat-label">Stok Fisik</div>
                <div class="stat-value">{{ $stockOpname->stok_fisik }}</div>
            </div>
            <div class="stat-card {{ $stockOpname->selisih < 0 ? 'coral' : ($stockOpname->selisih > 0 ? 'teal' : 'sky') }}">
                <div class="stat-label">Selisih</div>
                <div class="stat-value">{{ $stockOpname->selisih > 0 ? '+' : '' }}{{ $stockOpname->selisih }}</div>
            </div>
        </div>

        @if($stockOpname->keterangan)
        <div style="margin-top:20px;">
            <div class="form-label">Keterangan</div>
            <p>{{ $stockOpname->keterangan }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
