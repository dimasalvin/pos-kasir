@extends('layouts.dashboard')
@section('title', 'Laporan Kas')
@section('page-title', 'Laporan Kas')

@push('styles')
<style>
.saldo-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-top:20px; }
.saldo-item { padding:18px 20px; background:var(--surface); border-radius:var(--radius);
              box-shadow:var(--shadow); border:1px solid var(--border); }
.saldo-item .label { font-size:11px; font-weight:800; text-transform:uppercase;
                     letter-spacing:.06em; color:var(--muted); margin-bottom:6px; }
.saldo-item .value { font-size:22px; font-weight:800; color:var(--text); }
.saldo-item.highlight { background:var(--teal); border-color:var(--teal); }
.saldo-item.highlight .label { color:rgba(255,255,255,.7); }
.saldo-item.highlight .value { color:white; }
@media(max-width:768px) { .saldo-grid { grid-template-columns:1fr 1fr; } }

.saldo-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:200;
               align-items:center; justify-content:center; }
.saldo-modal.show { display:flex; }
.saldo-modal-content { background:white; border-radius:16px; padding:28px; max-width:400px; width:90%; }
</style>
@endpush

@section('content')
{{-- Filter --}}
<form method="GET" class="filter-bar">
    <div class="form-group">
        <label class="form-label">Dari</label>
        <input type="date" name="dari" class="form-control" value="{{ $dari }}">
    </div>
    <div class="form-group">
        <label class="form-label">Sampai</label>
        <input type="date" name="sampai" class="form-control" value="{{ $sampai }}">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">📊 Tampilkan</button>
    </div>
    <div class="form-group">
        <a href="{{ route('laporan-kas.cetak', ['dari' => $dari, 'sampai' => $sampai]) }}"
           target="_blank" class="btn btn-warning">🖨️ Cetak</a>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">💰 Laporan Kas Apotek</div>
            <div class="card-subtitle">
                Periode: {{ \Carbon\Carbon::parse($dari)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d-m-Y') }}
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="document.getElementById('saldoModal').classList.add('show')" class="btn btn-ghost btn-sm">
                ⚙️ Set Saldo Awal
            </button>
            <a href="{{ route('laporan-kas.create') }}" class="btn btn-primary btn-sm">+ Tambah Entri</a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Kredit</th>
                    <th>Debit</th>
                    <th>Tanggal Transaksi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $e)
                <tr>
                    <td style="font-size:13px; white-space:nowrap;">{{ $e->tanggal->format('d-m-Y') }}</td>
                    <td>{{ $e->keterangan }}</td>
                    <td style="font-weight:700; {{ $e->kredit > 0 ? 'color:var(--coral);' : '' }}">
                        {{ $e->kredit > 0 ? number_format($e->kredit, 0, ',', '.') : '' }}
                    </td>
                    <td style="font-weight:700; {{ $e->debit > 0 ? 'color:var(--teal-dark);' : '' }}">
                        {{ $e->debit > 0 ? number_format($e->debit, 0, ',', '.') : '' }}
                    </td>
                    <td style="font-size:13px; color:var(--muted);">
                        {{ $e->tanggal_transaksi ? $e->tanggal_transaksi->format('d-m-Y') : '' }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('laporan-kas.destroy', $e) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus entri ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--muted);">
                        Belum ada data kas untuk periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($entries->hasPages())
    <div class="pagination">
        @foreach($entries->getUrlRange(1, $entries->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page == $entries->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
    </div>
    @endif
</div>

{{-- Saldo Summary --}}
<div class="saldo-grid">
    <div class="saldo-item">
        <div class="label">Saldo Awal</div>
        <div class="value">Rp {{ number_format($ringkasan['saldo_awal'], 0, ',', '.') }}</div>
    </div>
    <div class="saldo-item">
        <div class="label">Debit (Masuk)</div>
        <div class="value" style="color:var(--teal-dark);">Rp {{ number_format($ringkasan['total_debit'], 0, ',', '.') }}</div>
    </div>
    <div class="saldo-item">
        <div class="label">Kredit (Keluar)</div>
        <div class="value" style="color:var(--coral);">Rp {{ number_format($ringkasan['total_kredit'], 0, ',', '.') }}</div>
    </div>
    <div class="saldo-item highlight">
        <div class="label">Saldo Akhir</div>
        <div class="value">Rp {{ number_format($ringkasan['saldo_akhir'], 0, ',', '.') }}</div>
    </div>
</div>

{{-- Modal Set Saldo Awal --}}
<div class="saldo-modal" id="saldoModal">
    <div class="saldo-modal-content">
        <h3 style="font-size:16px; font-weight:800; margin-bottom:16px;">⚙️ Set Saldo Awal Kas</h3>
        <form method="POST" action="{{ route('laporan-kas.update-saldo') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Saldo Awal (Rp)</label>
                <input type="text" name="saldo_awal" class="form-control input-rupiah" 
                       value="{{ $ringkasan['saldo_awal'] }}" style="font-size:18px; font-weight:700;">
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <button type="button" onclick="document.getElementById('saldoModal').classList.remove('show')"
                        class="btn btn-ghost">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection
