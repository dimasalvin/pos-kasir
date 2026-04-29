<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Laporan Penjualan
     */
    public function penjualan(Request $request)
    {
        $dari = $request->dari ?? now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? now()->toDateString();
        $namaBarang = $request->nama_barang;
        $shift = $request->shift;
        $metode = $request->metode;

        // Base query — shared filters applied once
        $baseQuery = Transaksi::whereBetween('tanggal', [$dari, $sampai]);
        $this->applyShiftFilter($baseQuery, $shift);
        $this->applyMetodeFilter($baseQuery, $metode);
        if ($namaBarang) {
            $baseQuery->whereHas('details', function ($q) use ($namaBarang) {
                $q->where('nama_barang', 'like', "%{$namaBarang}%");
            });
        }

        // Paginated list
        $transaksis = (clone $baseQuery)->with(['user', 'details'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Ringkasan — single aggregate query
        $ringkasan = (clone $baseQuery)->selectRaw('
                COUNT(*) as jumlah_nota,
                SUM(grand_total) as total_transaksi,
                SUM(CASE WHEN metode_bayar = "cash" THEN grand_total ELSE 0 END) as total_cash,
                SUM(CASE WHEN metode_bayar = "non-cash" THEN grand_total ELSE 0 END) as total_non_cash
            ')->first();

        // Riwayat penjualan per obat (jika filter nama barang aktif)
        $riwayatObat = null;
        if ($namaBarang) {
            $riwayatQuery = DB::table('transaksi_details')
                ->join('transaksis', 'transaksis.id', '=', 'transaksi_details.transaksi_id')
                ->whereBetween('transaksis.tanggal', [$dari, $sampai])
                ->where('transaksi_details.nama_barang', 'like', "%{$namaBarang}%");

            $this->applyShiftFilterRaw($riwayatQuery, $shift);
            $this->applyMetodeFilterRaw($riwayatQuery, $metode);

            $riwayatObat = $riwayatQuery->select(
                    'transaksi_details.nama_barang',
                    'transaksi_details.qty',
                    'transaksi_details.harga',
                    'transaksi_details.diskon',
                    'transaksi_details.subtotal',
                    'transaksis.no_nota',
                    'transaksis.tanggal',
                    'transaksis.created_at',
                    'transaksis.metode_bayar'
                )
                ->orderBy('transaksis.tanggal', 'desc')
                ->orderBy('transaksis.created_at', 'desc')
                ->limit(500)
                ->get();
        }

        return view('laporan.penjualan', compact(
            'transaksis', 'ringkasan', 'dari', 'sampai',
            'namaBarang', 'shift', 'metode', 'riwayatObat'
        ));
    }

    /** Apply shift filter to Eloquent query */
    private function applyShiftFilter($query, ?string $shift): void
    {
        if ($shift === 'pagi') {
            $query->whereTime('created_at', '>=', '07:00:00')
                  ->whereTime('created_at', '<', '14:00:00');
        } elseif ($shift === 'siang') {
            $query->whereTime('created_at', '>=', '14:00:00')
                  ->whereTime('created_at', '<=', '21:00:00');
        }
    }

    /** Apply metode filter to Eloquent query */
    private function applyMetodeFilter($query, ?string $metode): void
    {
        if ($metode === 'cash') {
            $query->where('metode_bayar', 'cash');
        } elseif ($metode === 'non-cash') {
            $query->where('metode_bayar', 'non-cash');
        }
    }

    /** Apply shift filter to raw DB query */
    private function applyShiftFilterRaw($query, ?string $shift): void
    {
        if ($shift === 'pagi') {
            $query->whereTime('transaksis.created_at', '>=', '07:00:00')
                  ->whereTime('transaksis.created_at', '<', '14:00:00');
        } elseif ($shift === 'siang') {
            $query->whereTime('transaksis.created_at', '>=', '14:00:00')
                  ->whereTime('transaksis.created_at', '<=', '21:00:00');
        }
    }

    /** Apply metode filter to raw DB query */
    private function applyMetodeFilterRaw($query, ?string $metode): void
    {
        if ($metode === 'cash') {
            $query->where('transaksis.metode_bayar', 'cash');
        } elseif ($metode === 'non-cash') {
            $query->where('transaksis.metode_bayar', 'non-cash');
        }
    }

    /**
     * Laporan Stok
     */
    public function stok(Request $request)
    {
        $query = Barang::with(['kategori', 'supplier']);

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('filter') && $request->filter === 'rendah') {
            $query->stokRendah();
        }

        $barangs = $query->orderBy('nama_barang')->paginate(30)->withQueryString();

        // Ringkasan stok — single aggregate query
        $ringkasanRaw = Barang::selectRaw('
            COUNT(*) as total_item,
            SUM(CASE WHEN stok < stok_minimum THEN 1 ELSE 0 END) as stok_rendah,
            SUM(stok * harga_beli) as total_nilai
        ')->first();

        $ringkasanStok = [
            'total_item'   => $ringkasanRaw->total_item ?? 0,
            'stok_rendah'  => $ringkasanRaw->stok_rendah ?? 0,
            'total_nilai'  => $ringkasanRaw->total_nilai ?? 0,
        ];

        return view('laporan.stok', compact('barangs', 'ringkasanStok'));
    }

    /**
     * Laporan Pembelian
     */
    public function pembelian(Request $request)
    {
        $dari = $request->dari ?? now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? now()->toDateString();

        $baseQuery = Pembelian::whereBetween('tanggal', [$dari, $sampai]);

        $pembelians = (clone $baseQuery)->with(['supplier', 'user'])
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalPembelian = (clone $baseQuery)->sum('grand_total');

        return view('laporan.pembelian', compact('pembelians', 'totalPembelian', 'dari', 'sampai'));
    }
}
