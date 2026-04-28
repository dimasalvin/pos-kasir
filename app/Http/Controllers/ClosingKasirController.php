<?php

namespace App\Http\Controllers;

use App\Models\ClosingKasir;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClosingKasirController extends Controller
{
    /**
     * Halaman utama closing kasir — filter periode & shift
     */
    public function index(Request $request)
    {
        $dari = $request->dari ?? now()->toDateString();
        $sampai = $request->sampai ?? now()->toDateString();
        $shiftFilter = $request->shift_filter; // pagi, malam, atau null (semua)

        $query = ClosingKasir::with('user')
            ->whereBetween('tanggal', [$dari, $sampai]);

        if ($shiftFilter) {
            $query->where('shift', $shiftFilter);
        }

        $closings = $query->orderBy('tanggal', 'desc')
            ->orderByRaw("FIELD(shift, 'pagi', 'malam')")
            ->get();

        // Hitung total footer
        $totals = [
            'jumlah_resep'     => $closings->sum('jumlah_resep'),
            'jumlah_hv'        => $closings->sum('jumlah_hv'),
            'pendapatan_resep' => $closings->sum('pendapatan_resep'),
            'pendapatan_hv'    => $closings->sum('pendapatan_hv'),
            'total_pendapatan' => $closings->sum('total_pendapatan'),
            'non_tunai'        => $closings->sum('non_tunai'),
            'total'            => $closings->sum('total'),
        ];

        return view('closing-kasir.index', compact('closings', 'totals', 'dari', 'sampai', 'shiftFilter'));
    }

    /**
     * Form generate closing
     */
    public function create()
    {
        return view('closing-kasir.create');
    }

    /**
     * Preview data closing sebelum simpan (AJAX)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift'   => 'required|in:pagi,malam',
        ]);

        // Cek sudah ada closing?
        $existing = ClosingKasir::where('tanggal', $request->tanggal)
            ->where('shift', $request->shift)
            ->first();

        if ($existing) {
            return response()->json([
                'exists'  => true,
                'message' => "Closing shift {$request->shift} tanggal {$request->tanggal} sudah ada.",
            ]);
        }

        $data = ClosingKasir::hitungDariTransaksi($request->tanggal, $request->shift);

        return response()->json([
            'exists' => false,
            'data'   => $data,
        ]);
    }

    /**
     * Simpan closing
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'shift'   => 'required|in:pagi,malam',
        ]);

        // Cek duplikat
        $existing = ClosingKasir::where('tanggal', $request->tanggal)
            ->where('shift', $request->shift)
            ->first();

        if ($existing) {
            return back()->with('error', "Closing shift {$request->shift} tanggal tersebut sudah ada.");
        }

        $data = ClosingKasir::hitungDariTransaksi($request->tanggal, $request->shift);

        ClosingKasir::create(array_merge($data, [
            'tanggal' => $request->tanggal,
            'shift'   => $request->shift,
            'user_id' => auth()->id(),
        ]));

        return redirect()->route('closing-kasir.index')
            ->with('success', "Closing shift {$request->shift} berhasil disimpan.");
    }

    /**
     * Hapus closing
     */
    public function destroy(ClosingKasir $closingKasir)
    {
        $closingKasir->delete();

        return redirect()->route('closing-kasir.index')
            ->with('success', 'Data closing berhasil dihapus.');
    }

    /**
     * Cetak closing (print view)
     */
    public function cetak(Request $request)
    {
        $dari = $request->dari ?? now()->toDateString();
        $sampai = $request->sampai ?? now()->toDateString();
        $shiftFilter = $request->shift_filter;

        $query = ClosingKasir::with('user')
            ->whereBetween('tanggal', [$dari, $sampai]);

        if ($shiftFilter) {
            $query->where('shift', $shiftFilter);
        }

        $closings = $query->orderBy('tanggal')
            ->orderByRaw("FIELD(shift, 'pagi', 'malam')")
            ->get();

        $totals = [
            'jumlah_resep'     => $closings->sum('jumlah_resep'),
            'jumlah_hv'        => $closings->sum('jumlah_hv'),
            'pendapatan_resep' => $closings->sum('pendapatan_resep'),
            'pendapatan_hv'    => $closings->sum('pendapatan_hv'),
            'total_pendapatan' => $closings->sum('total_pendapatan'),
            'non_tunai'        => $closings->sum('non_tunai'),
            'total'            => $closings->sum('total'),
        ];

        return view('closing-kasir.cetak', compact('closings', 'totals', 'dari', 'sampai', 'shiftFilter'));
    }
}
