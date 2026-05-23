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
            ->orderByRaw("FIELD(shift, 'pagi', 'siang')")
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
            'shift'   => 'required|in:pagi,siang',
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
        // Parse rupiah format ke angka sebelum validasi
        $request->merge([
            'modal_awal' => $this->parseRupiah($request->modal_awal),
            'uang_fisik' => $request->uang_fisik ? $this->parseRupiah($request->uang_fisik) : null,
            'setoran'    => $this->parseRupiah($request->setoran),
        ]);

        $request->validate([
            'tanggal'    => 'required|date',
            'shift'      => 'required|in:pagi,siang',
            'modal_awal' => 'nullable|numeric|min:0',
            'uang_fisik' => 'nullable|numeric|min:0',
            'setoran'    => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Cek duplikat
        $existing = ClosingKasir::where('tanggal', $request->tanggal)
            ->where('shift', $request->shift)
            ->first();

        if ($existing) {
            return back()->with('error', "Closing shift {$request->shift} tanggal tersebut sudah ada.");
        }

        $data = ClosingKasir::hitungDariTransaksi($request->tanggal, $request->shift);

        $modalAwal = $request->modal_awal ?? 0;
        $setoran = $request->setoran ?? 0;
        $uangFisik = $request->uang_fisik;

        // Seharusnya = Kas Awal + Pendapatan Tunai - Setoran ke Pemilik
        $seharusnya = $modalAwal + $data['total'] - $setoran;
        $selisih = $uangFisik !== null ? ($uangFisik - $seharusnya) : null;

        ClosingKasir::create(array_merge($data, [
            'tanggal'    => $request->tanggal,
            'shift'      => $request->shift,
            'modal_awal' => $modalAwal,
            'uang_fisik' => $uangFisik,
            'setoran'    => $setoran,
            'selisih'    => $selisih,
            'keterangan' => $request->keterangan,
            'user_id'    => auth()->id(),
        ]));

        $msg = "Closing shift {$request->shift} berhasil disimpan.";
        if ($selisih !== null && $selisih != 0) {
            $label = $selisih > 0 ? 'lebih' : 'kurang';
            $msg .= " ⚠️ Selisih kas: Rp " . number_format(abs($selisih), 0, ',', '.') . " ({$label})";
        }

        return redirect()->route('closing-kasir.index')
            ->with($selisih !== null && $selisih < 0 ? 'warning' : 'success', $msg);
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
            ->orderByRaw("FIELD(shift, 'pagi', 'siang')")
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

    /**
     * Parse format rupiah "Rp 175,000" atau "Rp 175.000" ke integer
     */
    private function parseRupiah($value): float
    {
        if ($value === null || $value === '') return 0;
        // Hapus semua karakter non-digit
        return (float) preg_replace('/[^\d]/', '', $value);
    }
}
