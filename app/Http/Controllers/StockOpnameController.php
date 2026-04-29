<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StockOpname;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['barang', 'user']);

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        if ($request->filled('search')) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', "%{$request->search}%");
            });
        }

        $opnames = $query->latest('tanggal')->paginate(20)->withQueryString();

        return view('stock-opname.index', compact('opnames'));
    }

    public function create()
    {
        $barangs = Barang::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'stok']);

        return view('stock-opname.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.barang_id'   => 'required|exists:barangs,id',
            'items.*.stok_fisik'  => 'required|numeric|min:0',
            'items.*.keterangan'  => 'nullable|string',
        ], [
            'items.*.barang_id.required' => 'Barang harus dipilih pada setiap baris.',
            'items.*.barang_id.exists'   => 'Barang yang dipilih tidak valid.',
            'items.*.stok_fisik.required' => 'Stok fisik wajib diisi pada setiap baris.',
            'items.*.stok_fisik.numeric'  => 'Stok fisik harus berupa angka.',
            'items.*.stok_fisik.min'      => 'Stok fisik tidak boleh kurang dari 0.',
        ]);

        // Pre-fetch semua barang yang dibutuhkan (1 query)
        $barangIds = collect($request->items)->pluck('barang_id');
        $barangs = Barang::whereIn('id', $barangIds)->get()->keyBy('id');

        foreach ($request->items as $item) {
            $barang = $barangs[$item['barang_id']];
            $selisih = $item['stok_fisik'] - $barang->stok;

            StockOpname::create([
                'tanggal'    => now()->toDateString(),
                'barang_id'  => $barang->id,
                'stok_sistem' => $barang->stok,
                'stok_fisik' => $item['stok_fisik'],
                'selisih'    => $selisih,
                'keterangan' => $item['keterangan'] ?? null,
                'user_id'    => auth()->id(),
            ]);

            // Update stok barang ke stok fisik
            $barang->update(['stok' => $item['stok_fisik']]);
        }

        return redirect()->route('stock-opname.index')
            ->with('success', 'Stock opname berhasil disimpan. Stok telah diperbarui.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['barang', 'user']);

        return view('stock-opname.show', compact('stockOpname'));
    }
}
