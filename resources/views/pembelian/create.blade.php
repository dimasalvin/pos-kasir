@extends('layouts.dashboard')
@section('title', 'Tambah Pembelian')
@section('page-title', 'Tambah Pembelian')

@push('styles')
<style>
.item-row { display:grid; grid-template-columns:2fr 1fr 1fr 1.2fr 1fr auto; gap:10px; align-items:end; margin-bottom:10px; }
@media(max-width:768px) { .item-row { grid-template-columns:1fr; } }
.diskon-group { display:flex; gap:4px; align-items:end; }
.diskon-group select { width:70px; padding:8px 4px; font-size:12px; border:1px solid var(--border); border-radius:6px; }
.diskon-group input { flex:1; }

</style>
@endpush

@section('content')
<div class="card" style="max-width:1000px;">
    <div class="card-header">
        <div class="card-title">📥 Form Pembelian Baru</div>
        <a href="{{ route('pembelian.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                ⚠️ Terjadi kesalahan:
                <ul style="margin:8px 0 0 16px; font-size:12px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pembelian.store') }}" id="formPembelian">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">No. Faktur</label>
                    <input type="text" class="form-control" value="{{ $noFaktur }}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal *</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Supplier *</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">Pilih Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan') }}</textarea>
            </div>

            <hr style="border:none; border-top:2px solid var(--border); margin:24px 0;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:15px; font-weight:800;">📦 Daftar Barang</h3>
                <button type="button" onclick="addItemRow()" class="btn btn-primary btn-sm">+ Tambah Item</button>
            </div>

            <div id="itemsContainer">
                <div class="item-row" data-index="0">
                    <div class="form-group">
                        <label class="form-label">Barang</label>
                        <select name="items[0][barang_id]" class="form-control" required>
                            <option value="">Pilih Barang</option>
                            @foreach($barangs as $b)
                                <option value="{{ $b->id }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Qty</label>
                        <input type="number" name="items[0][qty]" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga Beli</label>
                        <input type="text" name="items[0][harga_beli]" class="form-control input-rupiah" value="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Diskon</label>
                        <div class="diskon-group">
                            <select name="items[0][diskon_tipe]" class="form-control diskon-tipe-select">
                                <option value="rupiah">Rp</option>
                                <option value="persen">%</option>
                            </select>
                            <input type="number" name="items[0][diskon]" class="form-control diskon-input" value="0" min="0" step="any">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control subtotal-display" disabled value="Rp 0">
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="removeItemRow(this)" class="btn btn-danger btn-sm" style="margin-top:24px;">🗑️</button>
                    </div>
                </div>
            </div>

            <div style="text-align:right; font-size:18px; font-weight:800; margin-top:16px; padding:16px; background:var(--teal-light); border-radius:10px;">
                Total: <span id="grandTotal">Rp 0</span>
            </div>

            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">💾 Simpan Pembelian</button>
                <a href="{{ route('pembelian.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemIndex = 1;
const barangs = @json($barangs);

function addItemRow() {
    const container = document.getElementById('itemsContainer');
    const options = barangs.map(b => `<option value="${b.id}">${b.kode_barang} - ${b.nama_barang}</option>`).join('');

    const html = `
        <div class="item-row" data-index="${itemIndex}">
            <div class="form-group">
                <select name="items[${itemIndex}][barang_id]" class="form-control" required>
                    <option value="">Pilih Barang</option>
                    ${options}
                </select>
            </div>
            <div class="form-group">
                <input type="number" name="items[${itemIndex}][qty]" class="form-control" min="1" value="1" required>
            </div>
            <div class="form-group">
                <input type="text" name="items[${itemIndex}][harga_beli]" class="form-control input-rupiah" value="0" required>
            </div>
            <div class="form-group">
                <div class="diskon-group">
                    <select name="items[${itemIndex}][diskon_tipe]" class="form-control diskon-tipe-select">
                        <option value="rupiah">Rp</option>
                        <option value="persen">%</option>
                    </select>
                    <input type="number" name="items[${itemIndex}][diskon]" class="form-control diskon-input" value="0" min="0" step="any">
                </div>
            </div>
            <div class="form-group">
                <input type="text" class="form-control subtotal-display" disabled value="Rp 0">
            </div>
            <div class="form-group">
                <button type="button" onclick="removeItemRow(this)" class="btn btn-danger btn-sm">🗑️</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    initRupiahInputs(); // format input rupiah baru
    itemIndex++;
}

function removeItemRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) return alert('Minimal 1 item!');
    btn.closest('.item-row').remove();
    calcTotal();
}

document.getElementById('itemsContainer').addEventListener('input', function(e) {
    const row = e.target.closest('.item-row');
    if (!row) return;
    calcRowSubtotal(row);
    calcTotal();
});

document.getElementById('itemsContainer').addEventListener('change', function(e) {
    if (e.target.matches('select[name*="diskon_tipe"]')) {
        const row = e.target.closest('.item-row');
        if (!row) return;
        calcRowSubtotal(row);
        calcTotal();
    }
});



function getRowValues(row) {
    const qty = parseInt(row.querySelector('[name*="qty"]')?.value) || 0;
    // Harga beli: ambil dari hidden input (dibuat oleh input-rupiah)
    const hargaHidden = row.querySelector('input[type="hidden"][name*="harga_beli"]');
    const harga = hargaHidden ? parseFloat(hargaHidden.value) || 0 : 0;
    const hargaTotal = qty * harga;

    // Diskon: langsung dari input number
    const diskonTipeEl = row.querySelector('.diskon-tipe-select');
    const diskonTipe = diskonTipeEl ? diskonTipeEl.value : 'rupiah';
    const diskonInput = row.querySelector('.diskon-input');
    const diskonValue = diskonInput ? parseFloat(diskonInput.value) || 0 : 0;

    let diskonRupiah = 0;
    if (diskonTipe === 'persen') {
        diskonRupiah = (hargaTotal * diskonValue) / 100;
    } else {
        diskonRupiah = diskonValue;
    }

    return { qty, harga, hargaTotal, diskonTipe, diskonValue, diskonRupiah };
}

function calcRowSubtotal(row) {
    const v = getRowValues(row);
    const subtotal = v.hargaTotal - v.diskonRupiah;
    row.querySelector('.subtotal-display').value = formatRupiah(Math.max(0, subtotal));
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const v = getRowValues(row);
        total += v.hargaTotal - v.diskonRupiah;
    });
    document.getElementById('grandTotal').textContent = formatRupiah(Math.max(0, total));
}
</script>
@endpush
