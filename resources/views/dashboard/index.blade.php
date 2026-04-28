@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="stats-grid mb-28">
    <div class="stat-card teal">
        <div class="stat-icon">📦</div>
        <div class="stat-value">{{ number_format($stats['total_barang']) }}</div>
        <div class="stat-label">Total Barang</div>
    </div>
    <div class="stat-card coral">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value">{{ number_format($stats['stok_rendah']) }}</div>
        <div class="stat-label">Stok Rendah</div>
    </div>
    <div class="stat-card sky">
        <div class="stat-icon">🧾</div>
        <div class="stat-value">{{ number_format($stats['transaksi_hari_ini']) }}</div>
        <div class="stat-label">Transaksi Hari Ini</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">💰</div>
        <div class="stat-value">Rp {{ number_format($stats['pendapatan_hari_ini'], 0, ',', '.') }}</div>
        <div class="stat-label">Pendapatan Hari Ini</div>
    </div>
</div>

<div class="grid-2 mb-20">
    {{-- Chart Penjualan --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">📈 Penjualan 7 Hari Terakhir</div>
                <div class="card-subtitle">Grafik pendapatan harian</div>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-wrap">
                <canvas id="chartPenjualan"></canvas>
            </div>
        </div>
    </div>

    {{-- Barang Stok Rendah --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">⚠️ Stok Rendah</div>
                <div class="card-subtitle">Barang di bawah stok minimum</div>
            </div>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('barang.index', ['stok_filter' => 'rendah']) }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
            @endif
        </div>
        @if($barangStokRendah->isEmpty())
            <div class="card-body" style="text-align:center; color:var(--muted); padding:40px;">
                ✅ Semua stok aman
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Stok</th>
                            <th>Min</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangStokRendah as $b)
                        <tr>
                            <td><strong>{{ $b->nama_barang }}</strong></td>
                            <td style="font-weight:800; color:var(--coral);">{{ $b->stok }}</td>
                            <td style="color:var(--muted);">{{ $b->stok_minimum }}</td>
                            <td><span class="badge badge-coral">Rendah</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Transaksi Terakhir --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">🧾 Transaksi Terakhir</div>
            <div class="card-subtitle">5 transaksi terbaru</div>
        </div>
    </div>
    @if($transaksiTerakhir->isEmpty())
        <div class="card-body" style="text-align:center; color:var(--muted); padding:40px;">
            Belum ada transaksi
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Nota</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksiTerakhir as $t)
                    <tr>
                        <td><strong>{{ $t->no_nota }}</strong></td>
                        <td style="color:var(--muted); font-size:13px;">{{ $t->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $t->user->name }}</td>
                        <td style="font-weight:700;">Rp {{ number_format($t->grand_total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $t->metode_bayar === 'cash' ? 'badge-teal' : 'badge-purple' }}">
                                {{ $t->metode_bayar }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('kasir.struk', $t) }}" class="btn btn-ghost btn-sm">🧾 Struk</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const labels = @json($chartPenjualan->pluck('tgl'));
const data = @json($chartPenjualan->pluck('total'));

new Chart(document.getElementById('chartPenjualan'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: data,
            backgroundColor: 'rgba(43,191,164,0.7)',
            borderColor: '#1E9A87',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => 'Rp ' + v.toLocaleString('id-ID'),
                    font: { family: 'Nunito', size: 11 }
                },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                ticks: { font: { family: 'Nunito', size: 11 } },
                grid: { display: false }
            }
        }
    }
});
</script>
@endpush
