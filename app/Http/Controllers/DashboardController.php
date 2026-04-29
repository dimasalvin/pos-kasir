<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Pembelian;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // Statistik utama — combined queries
        $barangStats = Barang::selectRaw('
            COUNT(*) as total_barang,
            SUM(CASE WHEN stok < stok_minimum THEN 1 ELSE 0 END) as stok_rendah
        ')->first();

        $transaksiStats = Transaksi::whereDate('tanggal', $today)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(grand_total), 0) as pendapatan')
            ->first();

        $stats = [
            'total_barang'        => $barangStats->total_barang ?? 0,
            'stok_rendah'         => $barangStats->stok_rendah ?? 0,
            'transaksi_hari_ini'  => $transaksiStats->jumlah ?? 0,
            'pendapatan_hari_ini' => $transaksiStats->pendapatan ?? 0,
        ];

        // Penjualan 7 hari terakhir
        $chartPenjualan = Transaksi::select(
                DB::raw('DATE(tanggal) as tgl'),
                DB::raw('SUM(grand_total) as total'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->where('tanggal', '>=', now()->subDays(7))
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        // Barang stok rendah (top 10)
        $barangStokRendah = Barang::stokRendah()
            ->with('kategori')
            ->orderBy('stok')
            ->limit(10)
            ->get();

        // Transaksi terakhir
        $transaksiTerakhir = Transaksi::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'chartPenjualan',
            'barangStokRendah',
            'transaksiTerakhir'
        ));
    }
}
