<?php

namespace App\Http\Controllers;

use App\Models\LaporanKas;
use App\Models\SaldoKas;
use Illuminate\Http\Request;

class LaporanKasController extends Controller
{
    /**
     * Halaman utama laporan kas
     */
    public function index(Request $request)
    {
        $dari = $request->dari ?? now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? now()->toDateString();

        $entries = LaporanKas::with('user')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Hitung ringkasan — single aggregate query
        $totals = LaporanKas::whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('COALESCE(SUM(kredit), 0) as total_kredit, COALESCE(SUM(debit), 0) as total_debit')
            ->first();
        $totalKredit = $totals->total_kredit;
        $totalDebit = $totals->total_debit;

        // Hitung saldo awal periode secara dinamis dari saldo global
        $ringkasan = $this->hitungSaldoPeriode($dari, $sampai, $totalDebit, $totalKredit);

        return view('laporan-kas.index', compact('entries', 'ringkasan', 'dari', 'sampai'));
    }

    /**
     * Form tambah entri kas
     */
    public function create()
    {
        return view('laporan-kas.create');
    }

    /**
     * Simpan entri kas
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'            => 'required|date',
            'keterangan'         => 'required|string|max:255',
            'tipe'               => 'required|in:kredit,debit',
            'jumlah'             => 'required|numeric|min:1',
            'tanggal_transaksi'  => 'nullable|date',
        ], [
            'keterangan.required' => 'Keterangan wajib diisi.',
            'jumlah.required'     => 'Jumlah wajib diisi.',
            'jumlah.min'          => 'Jumlah minimal 1.',
        ]);

        LaporanKas::create([
            'tanggal'            => $request->tanggal,
            'keterangan'         => $request->keterangan,
            'kredit'             => $request->tipe === 'kredit' ? $request->jumlah : 0,
            'debit'              => $request->tipe === 'debit' ? $request->jumlah : 0,
            'tanggal_transaksi'  => $request->tanggal_transaksi,
            'user_id'            => auth()->id(),
        ]);

        return redirect()->route('laporan-kas.index')
            ->with('success', 'Entri kas berhasil ditambahkan.');
    }

    /**
     * Hapus entri kas
     */
    public function destroy(LaporanKas $laporanKa)
    {
        $laporanKa->delete();

        return redirect()->route('laporan-kas.index')
            ->with('success', 'Entri kas berhasil dihapus.');
    }

    /**
     * Update saldo awal
     */
    public function updateSaldo(Request $request)
    {
        $request->validate([
            'saldo_awal' => 'required|numeric|min:0',
        ]);

        SaldoKas::setSaldo($request->saldo_awal);

        return redirect()->route('laporan-kas.index')
            ->with('success', 'Saldo awal berhasil diperbarui.');
    }

    /**
     * Cetak laporan kas
     */
    public function cetak(Request $request)
    {
        $dari = $request->dari ?? now()->startOfMonth()->toDateString();
        $sampai = $request->sampai ?? now()->toDateString();

        $entries = LaporanKas::whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $totalKredit = $entries->sum('kredit');
        $totalDebit = $entries->sum('debit');

        // Hitung saldo awal periode secara dinamis dari saldo global
        $ringkasan = $this->hitungSaldoPeriode($dari, $sampai, $totalDebit, $totalKredit);

        return view('laporan-kas.cetak', compact('entries', 'ringkasan', 'dari', 'sampai'));
    }

    /**
     * Hitung saldo awal & akhir periode secara dinamis.
     *
     * Logika: Saldo global saat ini = saldo awal + semua mutasi historis.
     * Untuk menghitung saldo akhir pada tanggal $sampai, kita kurangi
     * mutasi yang terjadi SETELAH tanggal $sampai dari saldo global.
     * Saldo awal periode = saldo akhir periode - debit periode + kredit periode.
     */
    private function hitungSaldoPeriode(string $dari, string $sampai, float $totalDebit, float $totalKredit): array
    {
        $saldoKasGlobal = SaldoKas::getSaldo();

        // Hitung akumulasi mutasi setelah tanggal $sampai hingga sekarang
        $mutasiSetelahPeriode = LaporanKas::where('tanggal', '>', $sampai)
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(kredit), 0) as total_kredit')
            ->first();

        // Saldo akhir periode = Saldo saat ini - debit masa depan + kredit masa depan
        $saldoAkhir = $saldoKasGlobal
            - $mutasiSetelahPeriode->total_debit
            + $mutasiSetelahPeriode->total_kredit;

        // Saldo awal periode = Saldo akhir periode - debit periode ini + kredit periode ini
        $saldoAwal = $saldoAkhir - $totalDebit + $totalKredit;

        return [
            'saldo_awal'   => $saldoAwal,
            'total_debit'  => $totalDebit,
            'total_kredit' => $totalKredit,
            'saldo_akhir'  => $saldoAkhir,
        ];
    }
}
