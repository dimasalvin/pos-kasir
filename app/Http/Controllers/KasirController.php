<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'tipe_harga'   => 'required|in:umum',
            'pelanggan'    => 'nullable|string|max:255',
            'has_resep'    => 'nullable|boolean',
            'metode_bayar' => 'required|in:cash,non-cash',
            'bayar'        => 'required|numeric|min:0',
            'confirm_minus_stok' => 'nullable|boolean',
            'items'        => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.harga'     => 'required|numeric|min:0',
            'items.*.diskon'    => 'nullable|numeric|min:0',
            'items.*.is_resep_item' => 'nullable|boolean',
        ];

        // Jika ada resep, data pasien wajib diisi
        if ($request->has_resep) {
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

            // Pre-fetch semua barang yang dibutuhkan dengan pessimistic lock (1 query)
            // lockForUpdate mencegah race condition saat 2 kasir beli barang yang sama
            $barangIds = collect($items)->pluck('barang_id');
            $barangs = Barang::whereIn('id', $barangIds)->lockForUpdate()->get()->keyBy('id');

            // Hitung total — harga di-override dari database untuk mencegah manipulasi
            foreach ($items as &$item) {
                $barang = $barangs[$item['barang_id']] ?? null;
                if (!$barang) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan.'], 422);
                }

                // Override harga dari database berdasarkan tipe item
                $isResepItem = $item['is_resep_item'] ?? false;
                $hargaValid = $isResepItem
                    ? (float) $barang->harga_jual_resep
                    : (float) $barang->harga_jual_umum;
                $item['harga'] = $hargaValid;

                $diskon = $item['diskon'] ?? 0;
                $subtotal = ($hargaValid * $item['qty']) - $diskon;
                $item['subtotal'] = max(0, $subtotal);
                $total += $item['subtotal'];
            }
            unset($item);

            $grandTotal = $total;
            $bayar = $request->bayar;
            $kembalian = $bayar - $grandTotal;

            if ($kembalian < 0 && $request->metode_bayar === 'cash') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran kurang! Kurang Rp ' . number_format(abs($kembalian), 0, ',', '.'),
                ], 422);
            }

            // Cek stok mencukupi — jika ada yang minus, minta konfirmasi user
            if (!$request->confirm_minus_stok) {
                $stokKurang = [];
                foreach ($items as $item) {
                    $barang = $barangs[$item['barang_id']];
                    if ($barang->stok < $item['qty']) {
                        $stokKurang[] = "{$barang->nama_barang} (stok: {$barang->stok}, diminta: {$item['qty']})";
                    }
                }

                if (!empty($stokKurang)) {
                    DB::rollBack();
                    return response()->json([
                        'success'       => false,
                        'needs_confirm' => true,
                        'message'       => 'Stok tidak mencukupi untuk: ' . implode(', ', $stokKurang) . '. Lanjutkan transaksi?',
                        'stok_kurang'   => $stokKurang,
                    ], 422);
                }
            }

            // Buat transaksi (selalu tipe umum/HV)
            $transaksi = Transaksi::create([
                'no_nota'       => Transaksi::generateNoNota(),
                'tanggal'       => now()->toDateString(),
                'pelanggan'     => $request->pelanggan,
                'pasien_nama'   => $request->has_resep ? $request->pasien_nama : null,
                'pasien_telp'   => $request->has_resep ? $request->pasien_telp : null,
                'pasien_alamat' => $request->has_resep ? $request->pasien_alamat : null,
                'tipe_harga'    => 'umum',
                'has_resep'     => $request->has_resep ? true : false,
                'total'         => $total,
                'diskon'        => 0,
                'grand_total'   => $grandTotal,
                'bayar'         => $bayar,
                'kembalian'     => max(0, $kembalian),
                'metode_bayar'  => $request->metode_bayar,
                'user_id'       => auth()->id(),
            ]);

            // Simpan detail & kurangi stok
            $hasMinusStok = false;

            foreach ($items as $item) {
                $barang = $barangs[$item['barang_id']];

                // Tandai jika stok akan minus
                if ($barang->stok < $item['qty']) {
                    $hasMinusStok = true;
                }

                TransaksiDetail::create([
                    'transaksi_id'  => $transaksi->id,
                    'barang_id'     => $barang->id,
                    'nama_barang'   => $barang->nama_barang,
                    'qty'           => $item['qty'],
                    'harga'         => $item['harga'],
                    'diskon'        => $item['diskon'] ?? 0,
                    'subtotal'      => $item['subtotal'],
                    'is_resep_item' => $item['is_resep_item'] ?? false,
                ]);

                // Kurangi stok (bisa minus)
                $barang->decrement('stok', $item['qty']);
            }

            // Update flag minus stok jika ada
            if ($hasMinusStok) {
                $transaksi->update(['has_minus_stok' => true]);
            }

            DB::commit();

            Log::info('Transaksi kasir berhasil', [
                'user_id'    => auth()->id(),
                'no_nota'    => $transaksi->no_nota,
                'grand_total' => $grandTotal,
                'metode'     => $request->metode_bayar,
                'has_resep'  => $request->has_resep ? true : false,
            ]);

            return response()->json([
                'success'        => true,
                'message'        => 'Transaksi berhasil!',
                'transaksi_id'   => $transaksi->id,
                'no_nota'        => $transaksi->no_nota,
                'has_minus_stok' => $hasMinusStok,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaksi kasir gagal', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses transaksi. Silakan coba lagi.',
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
     * Note: Search hanya by nama (plaintext), telp & alamat terenkripsi jadi tidak bisa di-LIKE
     */
    public function searchPasien(Request $request)
    {
        $keyword = $request->q;

        if (!$keyword || strlen($keyword) < 2) {
            return response()->json([]);
        }

        $escaped = str_replace(['%', '_'], ['\%', '\_'], $keyword);

        $pasiens = Transaksi::whereNotNull('pasien_nama')
            ->where('pasien_nama', 'like', "%{$escaped}%")
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
