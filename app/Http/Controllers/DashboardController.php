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

        // Statistik utama
        $stats = [
            'total_barang'      => Barang::count(),
            'stok_rendah'       => Barang::stokRendah()->count(),
            'transaksi_hari_ini' => Transaksi::whereDate('tanggal', $today)->count(),
            'pendapatan_hari_ini' => Transaksi::whereDate('tanggal', $today)->sum('grand_total'),
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
