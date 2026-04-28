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

        $transaksis = Transaksi::with(['user', 'details'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Ringkasan
        $ringkasan = Transaksi::whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('
                COUNT(*) as jumlah_nota,
                SUM(grand_total) as total_transaksi,
                SUM(CASE WHEN metode_bayar = "cash" THEN grand_total ELSE 0 END) as total_cash,
                SUM(CASE WHEN metode_bayar = "non-cash" THEN grand_total ELSE 0 END) as total_non_cash
            ')
            ->first();

        return view('laporan.penjualan', compact('transaksis', 'ringkasan', 'dari', 'sampai'));
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

        // Ringkasan stok
        $ringkasanStok = [
            'total_item'   => Barang::count(),
            'stok_rendah'  => Barang::stokRendah()->count(),
            'total_nilai'  => Barang::selectRaw('SUM(stok * harga_beli) as total')->value('total') ?? 0,
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

        $pembelians = Pembelian::with(['supplier', 'user'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->withQueryString();

        $totalPembelian = Pembelian::whereBetween('tanggal', [$dari, $sampai])
            ->sum('grand_total');

        return view('laporan.pembelian', compact('pembelians', 'totalPembelian', 'dari', 'sampai'));
    }
}
