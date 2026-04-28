<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with(['kategori', 'supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        if ($request->filled('stok_filter') && $request->stok_filter === 'rendah') {
            $query->stokRendah();
        }

        $barangs = $query->orderBy('nama_barang')->paginate(20)->withQueryString();
        $kategoris = Kategori::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();

        return view('barang.index', compact('barangs', 'kategoris', 'suppliers'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();

        return view('barang.create', compact('kategoris', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang'     => 'required|string|max:50|unique:barangs',
            'barcode'         => 'required|string|max:100|unique:barangs',
            'nama_barang'     => 'required|string|max:255',
            'satuan'          => 'required|string|max:50',
            'kategori_id'     => 'required|exists:kategoris,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'harga_beli'      => 'required|numeric|min:0',
            'harga_jual_umum' => 'required|numeric|min:0',
            'harga_jual_resep'=> 'required|numeric|min:0',
            'stok'            => 'required|integer|min:0',
            'stok_minimum'    => 'required|integer|min:0',
        ]);

        Barang::create($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        $kategoris = Kategori::orderBy('nama')->get();
        $suppliers = Supplier::orderBy('nama')->get();

        return view('barang.edit', compact('barang', 'kategoris', 'suppliers'));
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'kode_barang'     => 'required|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'barcode'         => 'required|string|max:100|unique:barangs,barcode,' . $barang->id,
            'nama_barang'     => 'required|string|max:255',
            'satuan'          => 'required|string|max:50',
            'kategori_id'     => 'required|exists:kategoris,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'harga_beli'      => 'required|numeric|min:0',
            'harga_jual_umum' => 'required|numeric|min:0',
            'harga_jual_resep'=> 'required|numeric|min:0',
            'stok'            => 'required|integer|min:0',
            'stok_minimum'    => 'required|integer|min:0',
        ]);

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        // Cek apakah barang punya transaksi
        if ($barang->transaksiDetails()->exists() || $barang->pembelianDetails()->exists()) {
            return back()->with('error', 'Barang tidak bisa dihapus karena sudah ada transaksi.');
        }

        $barang->delete();

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * API: Cari barang berdasarkan barcode (untuk kasir)
     */
    public function findByBarcode(Request $request)
    {
        $barang = Barang::where('barcode', $request->barcode)->first();

        if (!$barang) {
            return response()->json(['found' => false, 'message' => 'Barang tidak ditemukan.'], 404);
        }

        return response()->json([
            'found' => true,
            'barang' => [
                'id'              => $barang->id,
                'kode_barang'     => $barang->kode_barang,
                'barcode'         => $barang->barcode,
                'nama_barang'     => $barang->nama_barang,
                'satuan'          => $barang->satuan,
                'harga_jual_umum' => (float) $barang->harga_jual_umum,
                'harga_jual_resep'=> (float) $barang->harga_jual_resep,
                'stok'            => $barang->stok,
            ],
        ]);
    }

    /**
     * API: Cari barang berdasarkan keyword (untuk kasir)
     */
    public function search(Request $request)
    {
        $keyword = $request->q;

        $barangs = Barang::where('nama_barang', 'like', "%{$keyword}%")
            ->orWhere('kode_barang', 'like', "%{$keyword}%")
            ->orWhere('barcode', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'kode_barang', 'barcode', 'nama_barang', 'satuan', 'harga_jual_umum', 'harga_jual_resep', 'stok']);

        return response()->json($barangs);
    }
}
