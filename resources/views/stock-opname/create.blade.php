@extends('layouts.dashboard')
@section('title', 'Stock Opname Baru')
@section('page-title', 'Stock Opname Baru')

@push('styles')
<style>
.opname-row { display:grid; grid-template-columns:2fr 1fr 1fr 1fr 2fr auto; gap:10px; align-items:end; margin-bottom:10px; }
@media(max-width:768px) { .opname-row { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="card" style="max-width:1100px;">
    <div class="card-header">
        <div class="card-title">📋 Form Stock Opname</div>
        <a href="{{ route('stock-opname.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
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

        <div class="alert alert-warning">
            ⚠️ Setelah disimpan, stok barang akan diperbarui sesuai stok fisik yang diinput.
        </div>

        <form method="POST" action="{{ route('stock-opname.store') }}">
            @csrf

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:15px; font-weight:800;">📦 Daftar Barang</h3>
                <button type="button" onclick="addOpnameRow()" class="btn btn-primary btn-sm">+ Tambah Barang</button>
            </div>

            <div id="opnameContainer">
                <div class="opname-row" data-index="0">
                    <div class="form-group">
                        <label class="form-label">Barang</label>
                        <select name="items[0][barang_id]" class="form-control barang-select" required onchange="loadStokSistem(this)">
                            <option value="">Pilih Barang</option>
                            @foreach($barangs as $b)
                                <option value="{{ $b->id }}" data-stok="{{ $b->stok }}">{{ $b->kode_barang }} - {{ $b->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok Sistem</label>
                        <input type="text" class="form-control stok-sistem" disabled value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok Fisik</label>
                        <input type="number" name="items[0][stok_fisik]" class="form-control stok-fisik" min="0" value="0" required oninput="calcSelisih(this)" onblur="if(this.value==='')this.value=0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Selisih</label>
                        <input type="text" class="form-control selisih-display" disabled value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="items[0][keterangan]" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="removeOpnameRow(this)" class="btn btn-danger btn-sm" style="margin-top:24px;">🗑️</button>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Simpan stock opname? Stok akan diperbarui.')">💾 Simpan Opname</button>
                <a href="{{ route('stock-opname.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let opnameIndex = 1;
const barangList = @json($barangs);

function addOpnameRow() {
    const container = document.getElementById('opnameContainer');
    const options = barangList.map(b => `<option value="${b.id}" data-stok="${b.stok}">${b.kode_barang} - ${b.nama_barang}</option>`).join('');

    const html = `
        <div class="opname-row" data-index="${opnameIndex}">
            <div class="form-group">
                <select name="items[${opnameIndex}][barang_id]" class="form-control barang-select" required onchange="loadStokSistem(this)">
                    <option value="">Pilih Barang</option>
                    ${options}
                </select>
            </div>
            <div class="form-group">
                <input type="text" class="form-control stok-sistem" disabled value="0">
            </div>
            <div class="form-group">
                <input type="number" name="items[${opnameIndex}][stok_fisik]" class="form-control stok-fisik" min="0" value="0" required oninput="calcSelisih(this)" onblur="if(this.value==='')this.value=0">
            </div>
            <div class="form-group">
                <input type="text" class="form-control selisih-display" disabled value="0">
            </div>
            <div class="form-group">
                <input type="text" name="items[${opnameIndex}][keterangan]" class="form-control" placeholder="Opsional">
            </div>
            <div class="form-group">
                <button type="button" onclick="removeOpnameRow(this)" class="btn btn-danger btn-sm">🗑️</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    opnameIndex++;
}

function removeOpnameRow(btn) {
    const rows = document.querySelectorAll('.opname-row');
    if (rows.length <= 1) return alert('Minimal 1 item!');
    btn.closest('.opname-row').remove();
}

function loadStokSistem(select) {
    const row = select.closest('.opname-row');
    const option = select.options[select.selectedIndex];
    const stok = option.dataset.stok || 0;
    row.querySelector('.stok-sistem').value = stok;
    calcSelisih(row.querySelector('.stok-fisik'));
}

function calcSelisih(input) {
    const row = input.closest('.opname-row');
    const stokSistem = parseInt(row.querySelector('.stok-sistem').value) || 0;
    const stokFisik = parseInt(input.value) || 0;
    const selisih = stokFisik - stokSistem;
    const display = row.querySelector('.selisih-display');
    display.value = selisih;
    display.style.color = selisih < 0 ? 'var(--coral)' : selisih > 0 ? 'var(--teal)' : 'var(--text)';
    display.style.fontWeight = '800';
}

// Pastikan stok_fisik tidak kosong saat submit
document.querySelector('form').addEventListener('submit', function() {
    document.querySelectorAll('.stok-fisik').forEach(input => {
        if (input.value === '' || isNaN(input.value)) {
            input.value = 0;
        }
    });
});
</script>
@endpush
