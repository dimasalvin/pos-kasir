@extends('layouts.dashboard')
@section('title', 'Kasir')
@section('page-title', 'Kasir')

@push('styles')
<style>
.kasir-wrap { display:grid; grid-template-columns:1fr 380px; gap:20px; }
@media(max-width:900px) { .kasir-wrap { grid-template-columns:1fr; } }

/* Search & Barcode */
.scan-bar { display:flex; gap:10px; margin-bottom:16px; }
.scan-bar input { flex:1; }
.search-results { position:absolute; top:100%; left:0; right:0; background:var(--surface);
                  border:1px solid var(--border); border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,.12);
                  max-height:300px; overflow-y:auto; z-index:100; display:none; }
.search-results.show { display:block; }
.search-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid var(--border);
               font-size:13px; transition:background .1s; }
.search-item:hover { background:var(--teal-light); }
.search-item:last-child { border-bottom:none; }
.search-item .name { font-weight:700; }
.search-item .info { font-size:11px; color:var(--muted); margin-top:2px; }

/* Cart */
.cart-item { display:flex; align-items:center; gap:10px; padding:10px 0;
             border-bottom:1px solid var(--border); font-size:13px; }
.cart-item:last-child { border-bottom:none; }
.cart-item-info { flex:1; min-width:0; }
.cart-item-name { font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cart-item-price { font-size:12px; color:var(--muted); }
.cart-qty { display:flex; align-items:center; gap:4px; }
.cart-qty button { width:28px; height:28px; border:1px solid var(--border); border-radius:6px;
                   background:var(--bg); cursor:pointer; font-weight:800; font-size:14px;
                   display:flex; align-items:center; justify-content:center; }
.cart-qty button:hover { background:var(--teal-light); border-color:var(--teal); }
.cart-qty input { width:40px; text-align:center; border:1px solid var(--border); border-radius:6px;
                  padding:4px; font-size:13px; font-weight:700; }
.cart-subtotal { font-weight:800; min-width:90px; text-align:right; }
.cart-remove { background:none; border:none; cursor:pointer; font-size:16px; padding:4px; }

/* Diskon per item */
.cart-diskon { width:70px; text-align:center; border:1px solid var(--border); border-radius:6px;
               padding:4px; font-size:12px; }

/* Toggle Harga */
.harga-toggle { display:flex; gap:4px; background:var(--bg); padding:4px; border-radius:10px;
                border:1px solid var(--border); margin-bottom:16px; }
.harga-toggle button { flex:1; padding:8px 16px; border:none; border-radius:8px; font-size:13px;
                       font-weight:700; cursor:pointer; font-family:'Nunito',sans-serif;
                       background:none; color:var(--muted); transition:all .15s; }
.harga-toggle button.active { background:var(--teal); color:white; box-shadow:0 2px 8px rgba(43,191,164,.3); }

/* Summary */
.cart-summary { border-top:2px solid var(--border); padding-top:16px; margin-top:16px; }
.summary-row { display:flex; justify-content:space-between; padding:4px 0; font-size:14px; }
.summary-row.total { font-size:20px; font-weight:800; color:var(--teal-dark); border-top:2px solid var(--teal);
                     padding-top:12px; margin-top:8px; }

/* Payment */
.payment-section { margin-top:16px; }
.metode-toggle { display:flex; gap:4px; margin-bottom:12px; }
.metode-toggle button { flex:1; padding:8px; border:1px solid var(--border); border-radius:8px;
                        font-size:12px; font-weight:700; cursor:pointer; background:var(--bg);
                        color:var(--muted); font-family:'Nunito',sans-serif; transition:all .15s; }
.metode-toggle button.active { background:var(--teal); color:white; border-color:var(--teal); }

.btn-bayar { width:100%; padding:14px; font-size:16px; margin-top:12px; }

/* Pasien Autocomplete */
.pasien-search-wrap { position:relative; }
.pasien-results { position:absolute; top:100%; left:0; right:0; background:var(--surface);
                  border:1px solid var(--border); border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,.12);
                  max-height:200px; overflow-y:auto; z-index:120; display:none; }
.pasien-results.show { display:block; }
.pasien-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid var(--border);
               font-size:13px; transition:background .1s; }
.pasien-item:hover { background:var(--teal-light); }
.pasien-item:last-child { border-bottom:none; }
.pasien-item .pasien-name { font-weight:700; }
.pasien-item .pasien-info { font-size:11px; color:var(--muted); margin-top:2px; }

/* Resep staging area */
.resep-staging { background:var(--bg); border:2px dashed var(--border); border-radius:12px;
                 padding:16px; margin-top:12px; }
.resep-staging.active { border-color:var(--teal); background:rgba(43,191,164,.05); }
.resep-item { display:flex; align-items:center; gap:8px; padding:8px 0;
              border-bottom:1px solid var(--border); font-size:12px; }
.resep-item:last-child { border-bottom:none; }
.resep-item-info { flex:1; min-width:0; }
.resep-item-name { font-weight:700; font-size:13px; }
.resep-item-price { font-size:11px; color:var(--muted); }
.resep-total { font-weight:800; font-size:15px; color:var(--teal-dark); text-align:right;
               border-top:2px solid var(--teal); padding-top:10px; margin-top:10px; }
.btn-konfirmasi-resep { width:100%; padding:10px; font-size:14px; margin-top:12px;
                        background:var(--teal); color:white; border:none; border-radius:8px;
                        font-weight:700; cursor:pointer; font-family:'Nunito',sans-serif; }
.btn-konfirmasi-resep:hover { background:var(--teal-dark); }

/* Resep badge in cart */
.resep-badge { display:inline-block; background:var(--teal); color:white; font-size:10px;
               padding:2px 6px; border-radius:4px; font-weight:700; margin-left:6px; }

/* Struk Modal */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:200;
                 align-items:center; justify-content:center; }
.modal-overlay.show { display:flex; }
.modal-content { background:white; border-radius:16px; padding:24px; max-width:400px; width:90%;
                 max-height:90vh; overflow-y:auto; }
</style>
@endpush

@section('content')
<div class="kasir-wrap">
    {{-- Left: Product Search + Cart --}}
    <div>
        {{-- Pencarian Barang --}}
        <div class="card mb-20" style="overflow:visible; position:relative; z-index:10;">
            <div class="card-body" style="overflow:visible;">
                <div style="position:relative;">
                    <div class="scan-bar">
                        <input type="text" id="barcodeInput" class="form-control"
                               placeholder="🔍 Ketik kode atau nama barang..." autofocus>
                    </div>
                    <div class="search-results" id="searchResults"></div>
                </div>
                <div id="scanNotif" style="display:none;" class="alert"></div>
            </div>
        </div>

        {{-- Resep Staging Area (hanya tampil saat mode resep) --}}
        <div class="card mb-20" id="resepCard" style="display:none;">
            <div class="card-header">
                <div>
                    <div class="card-title">📋 Racikan Resep</div>
                    <div class="card-subtitle" id="resepCount">0 obat</div>
                </div>
                <button onclick="clearResep()" class="btn btn-danger btn-sm">🗑️ Kosongkan</button>
            </div>
            <div class="card-body">
                <div id="resepItems" style="min-height:60px;">
                    <div id="resepEmpty" style="text-align:center; color:var(--muted); padding:20px; font-size:13px;">
                        Tambahkan obat resep dari pencarian di atas.
                    </div>
                </div>
                <div id="resepFooter" style="display:none;">
                    <div class="resep-total">
                        Total Resep: <span id="resepTotalDisplay">Rp 0</span>
                    </div>
                    <button onclick="konfirmasiResep()" class="btn-konfirmasi-resep">
                        ✓ Konfirmasi Resep ke Keranjang
                    </button>
                </div>
            </div>
        </div>

        {{-- Cart Table (keranjang utama) --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">🛒 Keranjang</div>
                    <div class="card-subtitle" id="cartCount">0 item</div>
                </div>
                <button onclick="clearCart()" class="btn btn-danger btn-sm">🗑️ Kosongkan</button>
            </div>
            <div class="card-body">
                <div id="cartItems" style="min-height:100px;">
                    <div id="cartEmpty" style="text-align:center; color:var(--muted); padding:40px;">
                        Keranjang kosong. Cari barang untuk menambahkan ke keranjang.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Payment Panel --}}
    <div>
        {{-- Mode Toggle + Pelanggan/Pasien --}}
        <div class="card mb-20" style="overflow:visible; position:relative; z-index:10;">
            <div class="card-body" style="padding:16px; overflow:visible;">
                <div class="harga-toggle" style="margin-bottom:12px;">
                    <button id="btnUmum" class="active" onclick="setTipeHarga('umum')">💊 Non Resep</button>
                    <button id="btnResep" onclick="setTipeHarga('resep')">📋 Resep</button>
                </div>

                {{-- Non Resep: Pelanggan opsional --}}
                <div id="pelangganSection">
                    <div class="form-group" style="margin-bottom:0;">
                        <input type="text" id="pelanggan" class="form-control" placeholder="Nama pelanggan (opsional)" style="padding:8px 12px; font-size:13px;">
                    </div>
                </div>

                {{-- Resep: Data Pasien wajib --}}
                <div id="pasienSection" style="display:none;">
                    <div class="form-group pasien-search-wrap" style="margin-bottom:8px;">
                        <input type="text" id="pasienNama" class="form-control" placeholder="🔍 Nama pasien *" autocomplete="off" style="padding:8px 12px; font-size:13px;">
                        <div class="pasien-results" id="pasienResults"></div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:0;">
                        <input type="text" id="pasienTelp" class="form-control" placeholder="📞 No. Telp *" style="padding:8px 12px; font-size:13px;">
                        <input type="text" id="pasienAlamat" class="form-control" placeholder="📍 Alamat *" style="padding:8px 12px; font-size:13px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary + Payment --}}
        <div class="card">
            <div class="card-body" style="padding:16px;">
                <div class="cart-summary" style="border-top:none; padding-top:0; margin-top:0;">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotalDisplay">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span id="diskonDisplay">Rp 0</span>
                    </div>
                    <div class="summary-row total">
                        <span>TOTAL</span>
                        <span id="totalDisplay">Rp 0</span>
                    </div>
                </div>

                <div style="border-top:1px solid var(--border); padding-top:14px; margin-top:14px;">
                    <div class="metode-toggle" style="margin-bottom:10px;">
                        <button id="btnCash" class="active" onclick="setMetode('cash')">💵 Cash</button>
                        <button id="btnNonCash" onclick="setMetode('non-cash')">💳 Non-Cash</button>
                    </div>

                    <div class="form-group" style="margin-bottom:10px;">
                        <input type="text" id="bayarInput" class="form-control" placeholder="Rp 0"
                               oninput="formatBayarInput(); hitungKembalian()" style="font-size:18px; font-weight:800; text-align:right; padding:10px 14px;">
                    </div>

                    <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:800; margin-bottom:12px;">
                        <span>Kembalian</span>
                        <span id="kembalianDisplay" style="color:var(--teal);">Rp 0</span>
                    </div>

                    <button onclick="prosesBayar()" class="btn btn-primary btn-bayar">
                        💰 Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Struk Modal --}}
<div class="modal-overlay" id="strukModal">
    <div class="modal-content" id="strukContent">
    </div>
</div>
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════
// STATE
// ══════════════════════════════════════════════════════════════
let cart = [];           // Keranjang utama (non-resep items + bundled resep)
let resepCart = [];      // Staging area untuk obat-obat resep
let tipeHarga = 'umum';
let metodeBayar = 'cash';
let searchTimeout = null;

// Resep bundle tracker
let resepBundles = [];

// ══════════════════════════════════════════════════════════════
// BARCODE / SEARCH INPUT
// ══════════════════════════════════════════════════════════════
const barcodeInput = document.getElementById('barcodeInput');
const searchResults = document.getElementById('searchResults');

barcodeInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (!val) return;

        fetch(`{{ route('api.barang.search') }}?q=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(items => {
                if (items.length > 0) {
                    const exact = items.find(b => b.kode_barang.toLowerCase() === val.toLowerCase());
                    const barang = exact || items[0];
                    handleAddBarang(barang);
                    barcodeInput.value = '';
                    searchResults.classList.remove('show');
                } else {
                    showNotif('danger', '⚠️ Barang tidak ditemukan');
                }
            })
            .catch(() => {
                showNotif('danger', '⚠️ Gagal mencari barang');
            });
    }
});

barcodeInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const val = this.value.trim();
    if (val.length < 2) {
        searchResults.classList.remove('show');
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('api.barang.search') }}?q=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(items => {
                if (items.length === 0) {
                    searchResults.innerHTML = '<div class="search-item" style="color:var(--muted);">Tidak ditemukan</div>';
                } else {
                    const hargaKey = tipeHarga === 'resep' ? 'harga_jual_resep' : 'harga_jual_umum';
                    searchResults.innerHTML = items.map(b => `
                        <div class="search-item" onclick='addFromSearch(${JSON.stringify(b)})'>
                            <div class="name">${b.nama_barang}</div>
                            <div class="info">${b.kode_barang} | Stok: ${b.stok} | ${formatRupiah(b[hargaKey] || b.harga_jual_umum)}</div>
                        </div>
                    `).join('');
                }
                searchResults.classList.add('show');
            });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!searchResults.contains(e.target) && e.target !== barcodeInput) {
        searchResults.classList.remove('show');
    }
});

function addFromSearch(barang) {
    handleAddBarang(barang);
    barcodeInput.value = '';
    searchResults.classList.remove('show');
    barcodeInput.focus();
}

// ══════════════════════════════════════════════════════════════
// ROUTING: resep staging or main cart based on mode
// ══════════════════════════════════════════════════════════════
function handleAddBarang(barang) {
    const stok = Number(barang.stok) || 0;

    // Cek apakah stok habis/kurang
    if (stok <= 0) {
        if (!confirm(`⚠️ STOK HABIS!\n\n${barang.nama_barang}\nStok saat ini: ${stok}\n\nLanjutkan transaksi? (Stok akan menjadi minus)`)) {
            return;
        }
    }

    if (tipeHarga === 'resep') {
        addToResep(barang);
        showNotif(stok <= 0 ? 'danger' : 'success', `${stok <= 0 ? '⚠️' : '✓'} ${barang.nama_barang} ditambahkan ke resep${stok <= 0 ? ' (STOK MINUS)' : ''}`);
    } else {
        addToCart(barang);
        showNotif(stok <= 0 ? 'danger' : 'success', `${stok <= 0 ? '⚠️' : '✓'} ${barang.nama_barang} ditambahkan${stok <= 0 ? ' (STOK MINUS)' : ''}`);
    }
}

// ══════════════════════════════════════════════════════════════
// RESEP STAGING CART
// ══════════════════════════════════════════════════════════════
function addToResep(barang) {
    const existing = resepCart.find(c => c.barang_id === barang.id);
    if (existing) {
        existing.qty++;
        existing.subtotal = (existing.harga * existing.qty) - existing.diskon;
    } else {
        const harga = Number(barang.harga_jual_resep) || Number(barang.harga_jual_umum) || 0;
        resepCart.push({
            barang_id: barang.id,
            nama_barang: barang.nama_barang,
            harga: harga,
            qty: 1,
            diskon: 0,
            stok: Number(barang.stok) || 0,
            subtotal: harga,
        });
    }
    renderResep();
}

function removeFromResep(index) {
    resepCart.splice(index, 1);
    renderResep();
}

function updateResepQty(index, delta) {
    resepCart[index].qty += delta;
    if (resepCart[index].qty < 1) resepCart[index].qty = 1;
    recalcResepItem(index);
    renderResep();
}

function setResepQty(index, val) {
    resepCart[index].qty = Math.max(1, parseInt(val) || 1);
    recalcResepItem(index);
    renderResep();
}

function setResepDiskon(index, rawVal) {
    resepCart[index].diskon = Math.max(0, parseRupiah(rawVal));
    recalcResepItem(index);
    renderResep();
}

function recalcResepItem(index) {
    const item = resepCart[index];
    item.subtotal = (item.harga * item.qty) - item.diskon;
    if (item.subtotal < 0) item.subtotal = 0;
}

function clearResep() {
    resepCart = [];
    renderResep();
}

function renderResep() {
    const container = document.getElementById('resepItems');
    const footer = document.getElementById('resepFooter');

    if (resepCart.length === 0) {
        container.innerHTML = '<div style="text-align:center; color:var(--muted); padding:20px; font-size:13px;">Tambahkan obat resep dari pencarian di atas.</div>';
        footer.style.display = 'none';
    } else {
        container.innerHTML = resepCart.map((item, i) => `
            <div class="resep-item">
                <div class="resep-item-info">
                    <div class="resep-item-name">${item.nama_barang}</div>
                    <div class="resep-item-price">${formatRupiah(item.harga)} × ${item.qty}</div>
                </div>
                <div class="cart-qty">
                    <button onclick="updateResepQty(${i}, -1)">−</button>
                    <input type="number" value="${item.qty}" min="1" onchange="setResepQty(${i}, this.value)" style="width:36px;">
                    <button onclick="updateResepQty(${i}, 1)">+</button>
                </div>
                <input type="text" class="cart-diskon" value="${item.diskon > 0 ? formatRupiah(item.diskon) : ''}"
                       placeholder="Rp 0" onchange="setResepDiskon(${i}, this.value)" title="Diskon (Rp)"
                       oninput="this.value = this.value ? formatRupiah(parseRupiah(this.value)) : ''" style="width:60px;">
                <div style="font-weight:800; min-width:80px; text-align:right; font-size:12px;">${formatRupiah(item.subtotal)}</div>
                <button class="cart-remove" onclick="removeFromResep(${i})" title="Hapus">❌</button>
            </div>
        `).join('');
        footer.style.display = 'block';

        const total = resepCart.reduce((sum, i) => sum + i.subtotal, 0);
        document.getElementById('resepTotalDisplay').textContent = formatRupiah(total);
    }

    document.getElementById('resepCount').textContent = resepCart.length + ' obat';
}

// ══════════════════════════════════════════════════════════════
// KONFIRMASI RESEP → Bundle ke keranjang utama
// ══════════════════════════════════════════════════════════════
function konfirmasiResep() {
    if (resepCart.length === 0) {
        showNotif('danger', '⚠️ Belum ada obat di resep!');
        return;
    }

    // Validasi data pasien
    const nama = document.getElementById('pasienNama').value.trim();
    const telp = document.getElementById('pasienTelp').value.trim();
    const alamat = document.getElementById('pasienAlamat').value.trim();

    if (!nama) {
        showNotif('danger', '⚠️ Nama pasien wajib diisi!');
        document.getElementById('pasienNama').focus();
        return;
    }
    if (!telp) {
        showNotif('danger', '⚠️ No. telepon pasien wajib diisi!');
        document.getElementById('pasienTelp').focus();
        return;
    }
    if (!alamat) {
        showNotif('danger', '⚠️ Alamat pasien wajib diisi!');
        document.getElementById('pasienAlamat').focus();
        return;
    }

    // Hitung total resep
    const totalResep = resepCart.reduce((sum, i) => sum + i.subtotal, 0);

    // Simpan detail obat resep untuk dikirim ke backend
    const resepDetail = resepCart.map(item => ({
        barang_id: item.barang_id,
        nama_barang: item.nama_barang,
        qty: item.qty,
        harga: item.harga,
        diskon: item.diskon,
        subtotal: item.subtotal,
    }));

    // Tambah 1 item "Resep" ke keranjang utama
    cart.push({
        barang_id: null,
        nama_barang: 'Resep',
        harga: totalResep,
        harga_umum: totalResep,
        harga_resep: totalResep,
        qty: 1,
        diskon: 0,
        stok: 9999,
        subtotal: totalResep,
        is_resep: true,
        resep_items: resepDetail,
        pasien_nama: nama,
        pasien_telp: telp,
        pasien_alamat: alamat,
    });

    // Reset resep staging
    resepCart = [];
    renderResep();

    // Switch back to non-resep mode
    setTipeHarga('umum');

    renderCart();
    showNotif('success', '✓ Resep dikonfirmasi dan ditambahkan ke keranjang');
}

// ══════════════════════════════════════════════════════════════
// MAIN CART (Non-Resep + bundled Resep)
// ══════════════════════════════════════════════════════════════
function addToCart(barang) {
    const existing = cart.find(c => c.barang_id === barang.id && !c.is_resep);
    if (existing) {
        existing.qty++;
        existing.subtotal = (existing.harga * existing.qty) - existing.diskon;
    } else {
        const harga = Number(barang.harga_jual_umum) || 0;
        cart.push({
            barang_id: barang.id,
            nama_barang: barang.nama_barang,
            harga: harga,
            harga_umum: harga,
            harga_resep: Number(barang.harga_jual_resep) || 0,
            qty: 1,
            diskon: 0,
            stok: Number(barang.stok) || 0,
            subtotal: harga,
            is_resep: false,
        });
    }
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateQty(index, delta) {
    if (cart[index].is_resep) return;
    cart[index].qty += delta;
    if (cart[index].qty < 1) cart[index].qty = 1;
    recalcItem(index);
    renderCart();
}

function setQty(index, val) {
    if (cart[index].is_resep) return;
    cart[index].qty = Math.max(1, parseInt(val) || 1);
    recalcItem(index);
    renderCart();
}

function setDiskon(index, rawVal) {
    if (cart[index].is_resep) return;
    cart[index].diskon = Math.max(0, parseRupiah(rawVal));
    recalcItem(index);
    renderCart();
}

function recalcItem(index) {
    const item = cart[index];
    item.subtotal = (item.harga * item.qty) - item.diskon;
    if (item.subtotal < 0) item.subtotal = 0;
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');

    if (cart.length === 0) {
        container.innerHTML = '<div style="text-align:center; color:var(--muted); padding:40px;">Keranjang kosong. Cari barang untuk menambahkan ke keranjang.</div>';
    } else {
        container.innerHTML = cart.map((item, i) => {
            if (item.is_resep) {
                // Render resep bundle
                const detailList = item.resep_items.map(r =>
                    `<span style="font-size:11px; color:var(--muted);">${r.nama_barang} (${r.qty})</span>`
                ).join(', ');
                return `
                    <div class="cart-item" style="background:rgba(43,191,164,.05); border-radius:8px; padding:10px; margin:4px 0;">
                        <div class="cart-item-info">
                            <div class="cart-item-name">📋 Resep <span class="resep-badge">R</span></div>
                            <div class="cart-item-price">${detailList}</div>
                            <div style="font-size:11px; color:var(--teal-dark); margin-top:2px;">Pasien: ${item.pasien_nama}</div>
                        </div>
                        <div class="cart-subtotal">${formatRupiah(item.subtotal)}</div>
                        <button class="cart-remove" onclick="removeFromCart(${i})" title="Hapus">❌</button>
                    </div>
                `;
            }
            // Render normal item
            return `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.nama_barang}</div>
                        <div class="cart-item-price">${formatRupiah(item.harga)} × ${item.qty}</div>
                    </div>
                    <div class="cart-qty">
                        <button onclick="updateQty(${i}, -1)">−</button>
                        <input type="number" value="${item.qty}" min="1" onchange="setQty(${i}, this.value)">
                        <button onclick="updateQty(${i}, 1)">+</button>
                    </div>
                    <input type="text" class="cart-diskon" value="${item.diskon > 0 ? formatRupiah(item.diskon) : ''}"
                           placeholder="Rp 0" onchange="setDiskon(${i}, this.value)" title="Diskon (Rp)"
                           oninput="this.value = this.value ? formatRupiah(parseRupiah(this.value)) : ''">
                    <div class="cart-subtotal">${formatRupiah(item.subtotal)}</div>
                    <button class="cart-remove" onclick="removeFromCart(${i})" title="Hapus">❌</button>
                </div>
            `;
        }).join('');
    }

    document.getElementById('cartCount').textContent = cart.length + ' item';
    updateSummary();
}

function updateSummary() {
    const subtotal = cart.reduce((sum, i) => sum + (i.is_resep ? i.subtotal : (i.harga * i.qty)), 0);
    const totalDiskon = cart.reduce((sum, i) => sum + (i.is_resep ? 0 : i.diskon), 0);
    const total = subtotal - totalDiskon;

    document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
    document.getElementById('diskonDisplay').textContent = formatRupiah(totalDiskon);
    document.getElementById('totalDisplay').textContent = formatRupiah(total);

    hitungKembalian();
}

// ══════════════════════════════════════════════════════════════
// TIPE HARGA TOGGLE
// ══════════════════════════════════════════════════════════════
function setTipeHarga(tipe) {
    tipeHarga = tipe;
    document.getElementById('btnUmum').classList.toggle('active', tipe === 'umum');
    document.getElementById('btnResep').classList.toggle('active', tipe === 'resep');

    // Toggle sections
    document.getElementById('pelangganSection').style.display = tipe === 'umum' ? 'block' : 'none';
    document.getElementById('pasienSection').style.display = tipe === 'resep' ? 'block' : 'none';
    document.getElementById('resepCard').style.display = tipe === 'resep' ? 'block' : 'none';
}

// ══════════════════════════════════════════════════════════════
// METODE BAYAR
// ══════════════════════════════════════════════════════════════
function setMetode(metode) {
    metodeBayar = metode;
    document.getElementById('btnCash').classList.toggle('active', metode === 'cash');
    document.getElementById('btnNonCash').classList.toggle('active', metode === 'non-cash');
}

// ══════════════════════════════════════════════════════════════
// FORMAT BAYAR INPUT
// ══════════════════════════════════════════════════════════════
function formatBayarInput() {
    const input = document.getElementById('bayarInput');
    const raw = parseInt(String(input.value).replace(/[^0-9]/g, ''), 10) || 0;
    const posFromEnd = input.value.length - input.selectionStart;
    input.value = raw > 0 ? formatRupiah(raw) : '';
    const newPos = Math.max(0, input.value.length - posFromEnd);
    input.setSelectionRange(newPos, newPos);
}

// ══════════════════════════════════════════════════════════════
// KEMBALIAN
// ══════════════════════════════════════════════════════════════
function hitungKembalian() {
    const total = cart.reduce((sum, i) => sum + (Number(i.subtotal) || 0), 0);
    const bayarStr = document.getElementById('bayarInput').value;
    const bayar = parseInt(String(bayarStr).replace(/[^0-9]/g, ''), 10) || 0;
    const kembalian = bayar - total;

    const el = document.getElementById('kembalianDisplay');
    el.textContent = formatRupiah(Math.max(0, kembalian));
    el.style.color = kembalian < 0 ? 'var(--coral)' : 'var(--teal)';
}

// ══════════════════════════════════════════════════════════════
// PROSES BAYAR
// ══════════════════════════════════════════════════════════════
function prosesBayar() {
    if (cart.length === 0) {
        showNotif('danger', '⚠️ Keranjang masih kosong!');
        return;
    }

    const total = cart.reduce((sum, i) => sum + (Number(i.subtotal) || 0), 0);
    const bayar = parseInt(String(document.getElementById('bayarInput').value).replace(/[^0-9]/g, ''), 10) || 0;

    if (metodeBayar === 'cash' && bayar < total) {
        showNotif('danger', `⚠️ Pembayaran kurang! Total: ${formatRupiah(total)}, Bayar: ${formatRupiah(bayar)}`);
        return;
    }

    // Non-cash: bayar = total
    const finalBayar = metodeBayar === 'non-cash' ? total : bayar;

    // Build items: expand resep bundles into individual items for backend
    let allItems = [];
    let pasienData = { nama: null, telp: null, alamat: null };
    let hasResep = false;

    cart.forEach(item => {
        if (item.is_resep) {
            hasResep = true;
            pasienData.nama = item.pasien_nama;
            pasienData.telp = item.pasien_telp;
            pasienData.alamat = item.pasien_alamat;
            // Expand resep items individually
            item.resep_items.forEach(r => {
                allItems.push({
                    barang_id: r.barang_id,
                    qty: r.qty,
                    harga: r.harga,
                    diskon: r.diskon,
                    is_resep_item: true,
                });
            });
        } else {
            allItems.push({
                barang_id: item.barang_id,
                qty: item.qty,
                harga: item.harga,
                diskon: item.diskon,
                is_resep_item: false,
            });
        }
    });

    const payload = {
        tipe_harga: 'umum',
        pelanggan: document.getElementById('pelanggan').value || null,
        pasien_nama: pasienData.nama,
        pasien_telp: pasienData.telp,
        pasien_alamat: pasienData.alamat,
        has_resep: hasResep,
        metode_bayar: metodeBayar,
        bayar: finalBayar,
        items: allItems,
    };

    fetch('{{ route("kasir.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showStruk(data.transaksi_id, data.no_nota, data.has_minus_stok);
            cart = [];
            resepBundles = [];
            renderCart();
            document.getElementById('bayarInput').value = '';
            document.getElementById('pelanggan').value = '';
            document.getElementById('pasienNama').value = '';
            document.getElementById('pasienTelp').value = '';
            document.getElementById('pasienAlamat').value = '';
            showNotif('success', '✓ ' + data.message);
        } else {
            showNotif('danger', '⚠️ ' + data.message);
        }
    })
    .catch(err => {
        showNotif('danger', '⚠️ Terjadi kesalahan');
        console.error(err);
    });
}

// ══════════════════════════════════════════════════════════════
// STRUK MODAL
// ══════════════════════════════════════════════════════════════
function showStruk(transaksiId, noNota, hasMinusStok) {
    const modal = document.getElementById('strukModal');
    const content = document.getElementById('strukContent');

    const warningHtml = hasMinusStok ? `
        <div style="background:#FEF3CD; border:1px solid #F0AD4E; border-radius:8px; padding:10px; margin-bottom:16px; font-size:12px; color:#856404;">
            ⚠️ <strong>Perhatian:</strong> Transaksi ini mengakibatkan stok minus pada beberapa barang. Segera lakukan restok.
        </div>
    ` : '';

    content.innerHTML = `
        <div style="text-align:center; padding:20px;">
            <div style="font-size:48px; margin-bottom:12px;">✅</div>
            <h2 style="font-family:'Caveat',cursive; font-size:28px; margin-bottom:8px;">Transaksi Berhasil!</h2>
            <p style="color:var(--muted); margin-bottom:20px;">No. Nota: <strong>${noNota}</strong></p>
            ${warningHtml}
            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                <a href="/kasir/struk/${transaksiId}" target="_blank" class="btn btn-primary">🧾 Lihat Struk</a>
                <a href="/kasir/struk-pdf/${transaksiId}" class="btn btn-ghost">📄 Download PDF</a>
                <button onclick="closeStruk()" class="btn btn-ghost">✕ Tutup</button>
            </div>
        </div>
    `;
    modal.classList.add('show');
}

function closeStruk() {
    document.getElementById('strukModal').classList.remove('show');
    barcodeInput.focus();
}

// ══════════════════════════════════════════════════════════════
// NOTIFIKASI
// ══════════════════════════════════════════════════════════════
function showNotif(type, msg) {
    const el = document.getElementById('scanNotif');
    el.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    el.textContent = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

// ══════════════════════════════════════════════════════════════
// PASIEN AUTOCOMPLETE
// ══════════════════════════════════════════════════════════════
let pasienTimeout = null;
const pasienNamaInput = document.getElementById('pasienNama');
const pasienResults = document.getElementById('pasienResults');

pasienNamaInput.addEventListener('input', function() {
    clearTimeout(pasienTimeout);
    const val = this.value.trim();
    if (val.length < 2) {
        pasienResults.classList.remove('show');
        return;
    }

    pasienTimeout = setTimeout(() => {
        fetch(`{{ route('api.pasien.search') }}?q=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(items => {
                if (items.length === 0) {
                    pasienResults.classList.remove('show');
                } else {
                    // Gunakan DOM manipulation yang aman (mencegah XSS)
                    pasienResults.innerHTML = '';
                    items.forEach(p => {
                        const div = document.createElement('div');
                        div.className = 'pasien-item';
                        div.addEventListener('click', () => selectPasien(p));

                        const nameDiv = document.createElement('div');
                        nameDiv.className = 'pasien-name';
                        nameDiv.textContent = p.pasien_nama;

                        const infoDiv = document.createElement('div');
                        infoDiv.className = 'pasien-info';
                        infoDiv.textContent = '📞 ' + (p.pasien_telp || '-') + ' | 📍 ' + ((p.pasien_alamat || '-').substring(0, 50));

                        div.appendChild(nameDiv);
                        div.appendChild(infoDiv);
                        pasienResults.appendChild(div);
                    });
                    pasienResults.classList.add('show');
                }
            });
    }, 300);
});

function selectPasien(pasien) {
    document.getElementById('pasienNama').value = pasien.pasien_nama || '';
    document.getElementById('pasienTelp').value = pasien.pasien_telp || '';
    document.getElementById('pasienAlamat').value = pasien.pasien_alamat || '';
    pasienResults.classList.remove('show');
}

document.addEventListener('click', function(e) {
    if (!pasienResults.contains(e.target) && e.target !== pasienNamaInput) {
        pasienResults.classList.remove('show');
    }
});

// ══════════════════════════════════════════════════════════════
// UTILITY
// ══════════════════════════════════════════════════════════════
function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function parseRupiah(str) {
    return parseInt(String(str).replace(/[^0-9]/g, ''), 10) || 0;
}

// Focus barcode on load
barcodeInput.focus();
</script>
@endpush
