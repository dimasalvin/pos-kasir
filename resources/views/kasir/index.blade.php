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
    {{-- Left: Product Search --}}
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

        {{-- Cart Table --}}
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
        {{-- Tipe Harga + Pelanggan/Pasien --}}
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

        {{-- Summary + Payment (digabung 1 card) --}}
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
        {{-- Filled by JS --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── State ──
let cart = [];
let tipeHarga = 'umum';
let metodeBayar = 'cash';
let searchTimeout = null;

// ── Barcode / Search Input ──
const barcodeInput = document.getElementById('barcodeInput');
const searchResults = document.getElementById('searchResults');

barcodeInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (!val) return;

        // Cari barang berdasarkan kode/nama, ambil exact match kode_barang jika ada
        fetch(`{{ route('api.barang.search') }}?q=${encodeURIComponent(val)}`)
            .then(r => r.json())
            .then(items => {
                if (items.length > 0) {
                    const exact = items.find(b => b.kode_barang.toLowerCase() === val.toLowerCase());
                    const barang = exact || items[0];
                    addToCart(barang);
                    barcodeInput.value = '';
                    searchResults.classList.remove('show');
                    showNotif('success', `✓ ${barang.nama_barang} ditambahkan`);
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
                    searchResults.innerHTML = items.map(b => `
                        <div class="search-item" onclick='addToCartFromSearch(${JSON.stringify(b)})'>
                            <div class="name">${b.nama_barang}</div>
                            <div class="info">${b.kode_barang} | Stok: ${b.stok} | ${formatRupiah(tipeHarga === 'resep' ? b.harga_jual_resep : b.harga_jual_umum)}</div>
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

function addToCartFromSearch(barang) {
    addToCart(barang);
    barcodeInput.value = '';
    searchResults.classList.remove('show');
    barcodeInput.focus();
}

// ── Cart Logic ──
function addToCart(barang) {
    const existing = cart.find(c => c.barang_id === barang.id);
    if (existing) {
        existing.qty++;
        existing.subtotal = (existing.harga * existing.qty) - existing.diskon;
    } else {
        const hargaUmum = Number(barang.harga_jual_umum) || 0;
        const hargaResep = Number(barang.harga_jual_resep) || 0;
        const harga = tipeHarga === 'resep' ? hargaResep : hargaUmum;
        cart.push({
            barang_id: barang.id,
            nama_barang: barang.nama_barang,
            harga: harga,
            harga_umum: hargaUmum,
            harga_resep: hargaResep,
            qty: 1,
            diskon: 0,
            stok: Number(barang.stok) || 0,
            subtotal: harga,
        });
    }
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty < 1) cart[index].qty = 1;
    recalcItem(index);
    renderCart();
}

function setQty(index, val) {
    cart[index].qty = Math.max(1, parseInt(val) || 1);
    recalcItem(index);
    renderCart();
}

function setDiskon(index, rawVal) {
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
    const empty = document.getElementById('cartEmpty');

    if (cart.length === 0) {
        container.innerHTML = '<div id="cartEmpty" style="text-align:center; color:var(--muted); padding:40px;">Keranjang kosong. Cari barang untuk menambahkan ke keranjang.</div>';
    } else {
        container.innerHTML = cart.map((item, i) => `
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
        `).join('');
    }

    document.getElementById('cartCount').textContent = cart.length + ' item';
    updateSummary();
}

function updateSummary() {
    const subtotal = cart.reduce((sum, i) => sum + (i.harga * i.qty), 0);
    const totalDiskon = cart.reduce((sum, i) => sum + i.diskon, 0);
    const total = subtotal - totalDiskon;

    document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
    document.getElementById('diskonDisplay').textContent = formatRupiah(totalDiskon);
    document.getElementById('totalDisplay').textContent = formatRupiah(total);

    hitungKembalian();
}

// ── Tipe Harga Toggle ──
function setTipeHarga(tipe) {
    tipeHarga = tipe;
    document.getElementById('btnUmum').classList.toggle('active', tipe === 'umum');
    document.getElementById('btnResep').classList.toggle('active', tipe === 'resep');

    // Toggle section pelanggan / data pasien
    document.getElementById('pelangganSection').style.display = tipe === 'umum' ? 'block' : 'none';
    document.getElementById('pasienSection').style.display = tipe === 'resep' ? 'block' : 'none';

    // Update harga di cart
    cart.forEach(item => {
        item.harga = tipe === 'resep' ? item.harga_resep : item.harga_umum;
        item.subtotal = (item.harga * item.qty) - item.diskon;
        if (item.subtotal < 0) item.subtotal = 0;
    });
    renderCart();
}

// ── Metode Bayar ──
function setMetode(metode) {
    metodeBayar = metode;
    document.getElementById('btnCash').classList.toggle('active', metode === 'cash');
    document.getElementById('btnNonCash').classList.toggle('active', metode === 'non-cash');
}

// ── Format Bayar Input ──
function formatBayarInput() {
    const input = document.getElementById('bayarInput');
    // Ambil hanya digit dari input
    const raw = parseInt(String(input.value).replace(/[^0-9]/g, ''), 10) || 0;
    // Simpan posisi cursor dari kanan
    const posFromEnd = input.value.length - input.selectionStart;
    input.value = raw > 0 ? formatRupiah(raw) : '';
    // Restore cursor dari kanan
    const newPos = Math.max(0, input.value.length - posFromEnd);
    input.setSelectionRange(newPos, newPos);
}

// ── Kembalian ──
function hitungKembalian() {
    const total = cart.reduce((sum, i) => sum + (Number(i.subtotal) || 0), 0);
    const bayarStr = document.getElementById('bayarInput').value;
    const bayar = parseInt(String(bayarStr).replace(/[^0-9]/g, ''), 10) || 0;
    const kembalian = bayar - total;

    const el = document.getElementById('kembalianDisplay');
    el.textContent = formatRupiah(Math.max(0, kembalian));
    el.style.color = kembalian < 0 ? 'var(--coral)' : 'var(--teal)';
}

// ── Proses Bayar ──
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

    // Validasi data pasien jika resep
    if (tipeHarga === 'resep') {
        const nama = document.getElementById('pasienNama').value.trim();
        const telp = document.getElementById('pasienTelp').value.trim();
        const alamat = document.getElementById('pasienAlamat').value.trim();

        if (!nama) {
            showNotif('danger', '⚠️ Nama pasien wajib diisi untuk resep!');
            document.getElementById('pasienNama').focus();
            return;
        }
        if (!telp) {
            showNotif('danger', '⚠️ No. telepon pasien wajib diisi untuk resep!');
            document.getElementById('pasienTelp').focus();
            return;
        }
        if (!alamat) {
            showNotif('danger', '⚠️ Alamat pasien wajib diisi untuk resep!');
            document.getElementById('pasienAlamat').focus();
            return;
        }
    }

    // Non-cash: bayar = total
    const finalBayar = metodeBayar === 'non-cash' ? total : bayar;

    const payload = {
        tipe_harga: tipeHarga,
        pelanggan: tipeHarga === 'umum' ? (document.getElementById('pelanggan').value || null) : null,
        pasien_nama: tipeHarga === 'resep' ? document.getElementById('pasienNama').value.trim() : null,
        pasien_telp: tipeHarga === 'resep' ? document.getElementById('pasienTelp').value.trim() : null,
        pasien_alamat: tipeHarga === 'resep' ? document.getElementById('pasienAlamat').value.trim() : null,
        metode_bayar: metodeBayar,
        bayar: finalBayar,
        items: cart.map(i => ({
            barang_id: i.barang_id,
            qty: i.qty,
            harga: i.harga,
            diskon: i.diskon,
        })),
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
            showStruk(data.transaksi_id, data.no_nota);
            cart = [];
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

// ── Struk ──
function showStruk(transaksiId, noNota) {
    const modal = document.getElementById('strukModal');
    const content = document.getElementById('strukContent');

    content.innerHTML = `
        <div style="text-align:center; padding:20px;">
            <div style="font-size:48px; margin-bottom:12px;">✅</div>
            <h2 style="font-family:'Caveat',cursive; font-size:28px; margin-bottom:8px;">Transaksi Berhasil!</h2>
            <p style="color:var(--muted); margin-bottom:20px;">No. Nota: <strong>${noNota}</strong></p>
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

// ── Notifikasi ──
function showNotif(type, msg) {
    const el = document.getElementById('scanNotif');
    el.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger');
    el.textContent = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

// ── Pasien Autocomplete ──
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
                    pasienResults.innerHTML = items.map(p => `
                        <div class="pasien-item" onclick='selectPasien(${JSON.stringify(p)})'>
                            <div class="pasien-name">${p.pasien_nama}</div>
                            <div class="pasien-info">📞 ${p.pasien_telp || '-'} | 📍 ${(p.pasien_alamat || '-').substring(0, 50)}</div>
                        </div>
                    `).join('');
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

// Focus barcode on load
barcodeInput.focus();
</script>
@endpush
