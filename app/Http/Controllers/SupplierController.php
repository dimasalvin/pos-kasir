<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('barangs');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('nama', 'like', "%{$search}%");
        }

        $suppliers = $query->orderBy('nama')->paginate(20)->withQueryString();

        return view('supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'alamat'      => 'nullable|string',
            'no_telp'     => 'nullable|string|max:20',
            'jatuh_tempo' => 'required|integer|min:1',
        ]);

        Supplier::create($validated);

        return redirect()->route('supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'alamat'      => 'nullable|string',
            'no_telp'     => 'nullable|string|max:20',
            'jatuh_tempo' => 'required|integer|min:1',
        ]);

        $supplier->update($validated);

        return redirect()->route('supplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->barangs()->exists() || $supplier->pembelians()->exists()) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena masih memiliki data terkait.');
        }

        $supplier->delete();

        return redirect()->route('supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
