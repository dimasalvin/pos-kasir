<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    /**
     * Halaman kasir utama
     */
    public function index()
    {
        return view('kasir.index');
    }

    /**
     * Proses transaksi
     */
    public function store(Request $request)
    {
        $rules = [
            'tipe_harga'   => 'required|in:umum,resep',
            'pelanggan'    => 'nullable|string|max:255',
            'metode_bayar' => 'required|in:cash,non-cash',
            'bayar'        => 'required|numeric|min:0',
            'items'        => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.harga'     => 'required|numeric|min:0',
            'items.*.diskon'    => 'nullable|numeric|min:0',
        ];

        // Jika tipe resep, data pasien wajib diisi
        if ($request->tipe_harga === 'resep') {
            $rules['pasien_nama']   = 'required|string|max:255';
            $rules['pasien_telp']   = 'required|string|max:20';
            $rules['pasien_alamat'] = 'required|string|max:500';
        }

        $request->validate($rules, [
            'pasien_nama.required'   => 'Nama pasien wajib diisi untuk resep.',
            'pasien_telp.required'   => 'No. telepon pasien wajib diisi untuk resep.',
            'pasien_alamat.required' => 'Alamat pasien wajib diisi untuk resep.',
        ]);

        DB::beginTransaction();

        try {
            $items = $request->items;
            $total = 0;

            // Hitung total
            foreach ($items as &$item) {
                $diskon = $item['diskon'] ?? 0;
                $subtotal = ($item['harga'] * $item['qty']) - $diskon;
                $item['subtotal'] = $subtotal;
                $total += $subtotal;
            }
            unset($item);

            $grandTotal = $total;
            $bayar = $request->bayar;
            $kembalian = $bayar - $grandTotal;

            if ($kembalian < 0 && $request->metode_bayar === 'cash') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran kurang! Kurang Rp ' . number_format(abs($kembalian), 0, ',', '.'),
                ], 422);
            }

            // Buat transaksi
            $transaksi = Transaksi::create([
                'no_nota'       => Transaksi::generateNoNota(),
                'tanggal'       => now()->toDateString(),
                'pelanggan'     => $request->pelanggan,
                'pasien_nama'   => $request->tipe_harga === 'resep' ? $request->pasien_nama : null,
                'pasien_telp'   => $request->tipe_harga === 'resep' ? $request->pasien_telp : null,
                'pasien_alamat' => $request->tipe_harga === 'resep' ? $request->pasien_alamat : null,
                'tipe_harga'    => $request->tipe_harga,
                'total'         => $total,
                'diskon'        => 0,
                'grand_total'   => $grandTotal,
                'bayar'         => $bayar,
                'kembalian'     => max(0, $kembalian),
                'metode_bayar'  => $request->metode_bayar,
                'user_id'       => auth()->id(),
            ]);

            // Pre-fetch semua barang yang dibutuhkan (1 query)
            $barangIds = collect($items)->pluck('barang_id');
            $barangs = Barang::whereIn('id', $barangIds)->get()->keyBy('id');

            // Simpan detail & kurangi stok
            foreach ($items as $item) {
                $barang = $barangs[$item['barang_id']] ?? null;
                if (!$barang) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan.'], 422);
                }

                // Cek stok cukup
                if ($barang->stok < $item['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$barang->nama_barang} tidak cukup! Sisa: {$barang->stok}",
                    ], 422);
                }

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id'    => $barang->id,
                    'nama_barang'  => $barang->nama_barang,
                    'qty'          => $item['qty'],
                    'harga'        => $item['harga'],
                    'diskon'       => $item['diskon'] ?? 0,
                    'subtotal'     => $item['subtotal'],
                ]);

                // Kurangi stok
                $barang->decrement('stok', $item['qty']);
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Transaksi berhasil!',
                'transaksi_id' => $transaksi->id,
                'no_nota'      => $transaksi->no_nota,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview struk
     */
    public function struk(Transaksi $transaksi)
    {
        $transaksi->load('details.barang', 'user');

        return view('kasir.struk', compact('transaksi'));
    }

    /**
     * Download struk PDF
     */
    public function strukPdf(Transaksi $transaksi)
    {
        $transaksi->load('details.barang', 'user');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kasir.struk-pdf', compact('transaksi'));
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm width

        return $pdf->download("struk-{$transaksi->no_nota}.pdf");
    }

    /**
     * API: Cari data pasien dari transaksi sebelumnya
     */
    public function searchPasien(Request $request)
    {
        $keyword = $request->q;

        if (!$keyword || strlen($keyword) < 2) {
            return response()->json([]);
        }

        $pasiens = Transaksi::whereNotNull('pasien_nama')
            ->where(function ($query) use ($keyword) {
                $query->where('pasien_nama', 'like', "%{$keyword}%")
                      ->orWhere('pasien_telp', 'like', "%{$keyword}%");
            })
            ->select('pasien_nama', 'pasien_telp', 'pasien_alamat')
            ->distinct()
            ->orderBy('pasien_nama')
            ->limit(10)
            ->get()
            ->unique(function ($item) {
                return strtolower($item->pasien_nama) . '|' . $item->pasien_telp;
            })
            ->values();

        return response()->json($pasiens);
    }
}
