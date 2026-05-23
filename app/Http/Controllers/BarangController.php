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
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
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
            'nama_barang'     => 'required|string|max:255',
            'satuan'          => 'required|string|max:50',
            'kategori_id'     => 'required|exists:kategoris,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'harga_beli'      => 'required|numeric|min:0',
            'harga_jual_umum' => 'required|numeric|min:0',
            'harga_jual_resep'=> 'required|numeric|min:0',
            'stok'            => 'required|numeric|min:0',
            'stok_minimum'    => 'required|numeric|min:0',
        ]);

        // Cast stok ke integer
        $validated['stok'] = (int) $validated['stok'];
        $validated['stok_minimum'] = (int) $validated['stok_minimum'];

        // Fallback: jika harga jual 0, auto-hitung dari harga beli
        $hargaBeli = $validated['harga_beli'];
        if ($validated['harga_jual_umum'] <= 0) {
            $validated['harga_jual_umum'] = ceil($hargaBeli + ($hargaBeli * 10 / 100));
        }
        if ($validated['harga_jual_resep'] <= 0) {
            $validated['harga_jual_resep'] = ceil($hargaBeli + ($hargaBeli * 30 / 100));
        }

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
            'nama_barang'     => 'required|string|max:255',
            'satuan'          => 'required|string|max:50',
            'kategori_id'     => 'required|exists:kategoris,id',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'harga_beli'      => 'required|numeric|min:0',
            'harga_jual_umum' => 'required|numeric|min:0',
            'harga_jual_resep'=> 'required|numeric|min:0',
            'stok'            => 'required|numeric|min:0',
            'stok_minimum'    => 'required|numeric|min:0',
        ]);

        // Cast stok ke integer
        $validated['stok'] = (int) $validated['stok'];
        $validated['stok_minimum'] = (int) $validated['stok_minimum'];

        // Fallback: jika harga jual 0, auto-hitung dari harga beli
        $hargaBeli = $validated['harga_beli'];
        if ($validated['harga_jual_umum'] <= 0) {
            $validated['harga_jual_umum'] = ceil($hargaBeli + ($hargaBeli * 10 / 100));
        }
        if ($validated['harga_jual_resep'] <= 0) {
            $validated['harga_jual_resep'] = ceil($hargaBeli + ($hargaBeli * 30 / 100));
        }

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
     * API: Cari barang berdasarkan keyword (untuk kasir)
     */
    public function search(Request $request)
    {
        $keyword = $request->q;

        if (!$keyword || strlen($keyword) < 2) {
            return response()->json([]);
        }

        // Escape wildcard characters untuk mencegah abuse LIKE query
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $keyword);

        $barangs = Barang::where('nama_barang', 'like', "%{$escaped}%")
            ->orWhere('kode_barang', 'like', "%{$escaped}%")
            ->limit(15)
            ->get(['id', 'kode_barang', 'nama_barang', 'satuan', 'harga_jual_umum', 'harga_jual_resep', 'stok']);

        return response()->json($barangs);
    }
}
