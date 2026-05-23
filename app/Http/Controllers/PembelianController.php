<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembelian::with(['supplier', 'user']);

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('no_faktur', 'like', "%{$search}%");
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        $pembelians = $query->latest('tanggal')->paginate(20)->withQueryString();
        $suppliers = Supplier::orderBy('nama')->get();

        return view('pembelian.index', compact('pembelians', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('nama')->get();
        $barangs = Barang::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'harga_beli']);
        $noFaktur = Pembelian::generateNoFaktur();

        return view('pembelian.create', compact('suppliers', 'barangs', 'noFaktur'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'              => 'required|exists:suppliers,id',
            'tanggal'                  => 'required|date',
            'keterangan'               => 'nullable|string',
            'items'                    => 'required|array|min:1',
            'items.*.barang_id'        => 'required|exists:barangs,id',
            'items.*.qty'              => 'required|numeric|min:1',
            'items.*.harga_beli'       => 'required|numeric|min:0',
            'items.*.diskon_tipe'      => 'nullable|in:rupiah,persen',
            'items.*.diskon'           => 'nullable|numeric|min:0',
        ], [
            'supplier_id.required'        => 'Supplier harus dipilih.',
            'items.*.barang_id.required'  => 'Barang harus dipilih pada setiap baris.',
            'items.*.qty.required'        => 'Qty wajib diisi pada setiap baris.',
            'items.*.qty.min'             => 'Qty minimal 1.',
            'items.*.harga_beli.required' => 'Harga beli wajib diisi pada setiap baris.',
        ]);

        DB::beginTransaction();

        try {
            $items = $request->items;
            $total = 0;

            foreach ($items as &$item) {
                $diskonTipe = $item['diskon_tipe'] ?? 'rupiah';
                $diskonValue = $item['diskon'] ?? 0;
                $hargaTotal = $item['harga_beli'] * $item['qty'];

                // Hitung diskon per item
                if ($diskonTipe === 'persen') {
                    $diskonRupiah = ($hargaTotal * $diskonValue) / 100;
                } else {
                    $diskonRupiah = $diskonValue;
                }

                $subtotal = $hargaTotal - $diskonRupiah;
                $item['diskon_tipe'] = $diskonTipe;
                $item['diskon_rupiah'] = $diskonRupiah;
                $item['subtotal'] = $subtotal;
                $total += $subtotal;
            }
            unset($item);

            $pembelian = Pembelian::create([
                'no_faktur'   => Pembelian::generateNoFaktur(),
                'tanggal'     => $request->tanggal,
                'supplier_id' => $request->supplier_id,
                'total'       => $total,
                'diskon'      => 0,
                'grand_total' => $total,
                'keterangan'  => $request->keterangan,
                'user_id'     => auth()->id(),
            ]);

            // Pre-fetch semua barang yang dibutuhkan dengan pessimistic lock (1 query)
            $barangIds = collect($items)->pluck('barang_id');
            $barangs = Barang::whereIn('id', $barangIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id'    => $item['barang_id'],
                    'qty'          => $item['qty'],
                    'harga_beli'   => $item['harga_beli'],
                    'diskon_tipe'  => $item['diskon_tipe'],
                    'diskon'       => $item['diskon'] ?? 0,
                    'subtotal'     => $item['subtotal'],
                ]);

                // Tambah stok & update harga beli
                $barang = $barangs[$item['barang_id']];
                $barang->increment('stok', (int) $item['qty']);
                $barang->update(['harga_beli' => $item['harga_beli']]);
            }

            DB::commit();

            Log::info('Pembelian berhasil disimpan', [
                'user_id'    => auth()->id(),
                'no_faktur'  => $pembelian->no_faktur,
                'supplier'   => $request->supplier_id,
                'grand_total' => $total,
            ]);

            return redirect()->route('pembelian.index')
                ->with('success', 'Pembelian berhasil disimpan. Stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pembelian gagal disimpan', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan pembelian. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function show(Pembelian $pembelian)
    {
        $pembelian->load(['supplier', 'user', 'details.barang']);

        return view('pembelian.show', compact('pembelian'));
    }

    public function destroy(Pembelian $pembelian)
    {
        DB::beginTransaction();

        try {
            // Eager load details + barang (1+1 query, bukan N+1)
            $pembelian->load('details.barang');

            // Kembalikan stok
            foreach ($pembelian->details as $detail) {
                $detail->barang->decrement('stok', $detail->qty);
            }

            $pembelian->delete();

            DB::commit();

            Log::info('Pembelian dihapus', [
                'user_id'    => auth()->id(),
                'no_faktur'  => $pembelian->no_faktur,
                'grand_total' => $pembelian->grand_total,
            ]);

            return redirect()->route('pembelian.index')
                ->with('success', 'Pembelian berhasil dihapus. Stok telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pembelian gagal dihapus', [
                'user_id'      => auth()->id(),
                'pembelian_id' => $pembelian->id,
                'error'        => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus pembelian. Silakan coba lagi.');
        }
    }
}
