@extends('layouts.dashboard')
@section('title', 'Closing Kasir')
@section('page-title', 'Closing Kasir')

@push('styles')
<style>
.shift-badge { padding:4px 12px; border-radius:20px; font-size:11px; font-weight:800; text-transform:uppercase; }
.shift-pagi { background:#FFF9E6; color:#996B00; }
.shift-malam { background:#F0EEFF; color:#7C6BE8; }
</style>
@endpush

@section('content')
{{-- Filter Periode --}}
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
        <label class="form-label">Shift</label>
        <select name="shift_filter" class="form-control">
            <option value="">Semua Shift</option>
            <option value="pagi" {{ $shiftFilter === 'pagi' ? 'selected' : '' }}>Pagi</option>
            <option value="malam" {{ $shiftFilter === 'malam' ? 'selected' : '' }}>Malam</option>
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">📊 Tampilkan</button>
    </div>
    <div class="form-group">
        <a href="{{ route('closing-kasir.cetak', ['dari' => $dari, 'sampai' => $sampai, 'shift_filter' => $shiftFilter]) }}"
           target="_blank" class="btn btn-warning">🖨️ Cetak</a>
    </div>
</form>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📋 Closing Kasir</div>
            <div class="card-subtitle">
                Periode: {{ \Carbon\Carbon::parse($dari)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d-m-Y') }}
            </div>
        </div>
        <a href="{{ route('closing-kasir.create') }}" class="btn btn-primary">+ Buat Closing</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Shift</th>
                    <th>Tanggal</th>
                    <th>R/</th>
                    <th>HV</th>
                    <th>Pendapatan R/</th>
                    <th>Pendapatan HV</th>
                    <th>Total Pendapatan</th>
                    <th>Non Tunai</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($closings as $c)
                <tr>
                    <td>
                        <span class="shift-badge {{ $c->shift === 'pagi' ? 'shift-pagi' : 'shift-malam' }}">
                            {{ ucfirst($c->shift) }}
                        </span>
                    </td>
                    <td style="font-size:13px;">{{ $c->tanggal->format('d-m-Y') }}</td>
                    <td style="font-weight:700;">{{ $c->jumlah_resep }}</td>
                    <td style="font-weight:700;">{{ $c->jumlah_hv }}</td>
                    <td>{{ number_format($c->pendapatan_resep, 0, ',', '.') }}</td>
                    <td>{{ number_format($c->pendapatan_hv, 0, ',', '.') }}</td>
                    <td style="font-weight:700;">{{ number_format($c->total_pendapatan, 0, ',', '.') }}</td>
                    <td>{{ number_format($c->non_tunai, 0, ',', '.') }}</td>
                    <td style="font-weight:800;">{{ number_format($c->total, 0, ',', '.') }}</td>
                    <td>
                        <form method="POST" action="{{ route('closing-kasir.destroy', $c) }}" style="display:inline;"
                              onsubmit="return confirm('Hapus closing ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:40px; color:var(--muted);">
                        Belum ada data closing untuk periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($closings->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right; font-weight:800; background:var(--bg);">TOTAL</td>
                    <td style="font-weight:800; background:var(--bg);">{{ $totals['jumlah_resep'] }}</td>
                    <td style="font-weight:800; background:var(--bg);">{{ $totals['jumlah_hv'] }}</td>
                    <td style="font-weight:800; background:var(--bg);">{{ number_format($totals['pendapatan_resep'], 0, ',', '.') }}</td>
                    <td style="font-weight:800; background:var(--bg);">{{ number_format($totals['pendapatan_hv'], 0, ',', '.') }}</td>
                    <td style="font-weight:800; background:var(--bg);">{{ number_format($totals['total_pendapatan'], 0, ',', '.') }}</td>
                    <td style="font-weight:800; background:var(--bg);">{{ number_format($totals['non_tunai'], 0, ',', '.') }}</td>
                    <td style="font-weight:800; background:var(--bg);">{{ number_format($totals['total'], 0, ',', '.') }}</td>
                    <td style="background:var(--bg);"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
