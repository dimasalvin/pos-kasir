@extends('layouts.dashboard')
@section('title', 'Detail Pembelian')
@section('page-title', 'Detail Pembelian')

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-header">
        <div>
            <div class="card-title">📥 {{ $pembelian->no_faktur }}</div>
            <div class="card-subtitle">{{ $pembelian->tanggal->format('d F Y') }}</div>
        </div>
        <a href="{{ route('pembelian.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        <div class="form-row mb-20">
            <div>
                <div class="form-label">Supplier</div>
                <strong>{{ $pembelian->supplier->nama }}</strong>
            </div>
            <div>
                <div class="form-label">Dibuat Oleh</div>
                <strong>{{ $pembelian->user->name }}</strong>
            </div>
        </div>
        @if($pembelian->keterangan)
        <div class="mb-20">
            <div class="form-label">Keterangan</div>
            <p>{{ $pembelian->keterangan }}</p>
        </div>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barang</th>
                    <th>Qty</th>
                    <th>Harga Beli</th>
                    <th>Diskon</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pembelian->details as $d)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $d->barang->nama_barang }}</strong></td>
                    <td>{{ $d->qty }}</td>
                    <td>Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($d->diskon, 0, ',', '.') }}</td>
                    <td style="font-weight:700;">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:800;">Grand Total</td>
                    <td style="font-weight:800; font-size:16px;">Rp {{ number_format($pembelian->grand_total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
