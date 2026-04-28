# 💊 Kasir POS

Aplikasi **Point of Sale (POS)** berbasis **Laravel 12** & **MySQL** untuk apotek/toko obat. Dilengkapi fitur kasir dengan barcode scanner, multi harga (resep/non-resep), manajemen stok, stock opname, closing kasir per shift, dan laporan kas.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)

---

## ✨ Fitur Lengkap

| Modul | Fitur |
|-------|-------|
| **🔐 Auth** | Login, logout, role (Admin/Kasir), **single login** (1 akun = 1 perangkat) |
| **📦 Barang** | CRUD, barcode, multi harga (umum & resep), kategori warna, notifikasi stok rendah |
| **🏭 Supplier** | CRUD supplier, jatuh tempo |
| **🛒 Kasir** | Scan barcode, pencarian manual, keranjang interaktif, toggle resep/non-resep, cash/non-cash, cetak & download struk PDF |
| **📥 Pembelian** | Input pembelian multi item, stok otomatis bertambah, auto no. faktur |
| **📋 Stock Opname** | Input stok fisik, hitung selisih otomatis, riwayat opname |
| **🔒 Closing Kasir** | Rekap per shift (pagi/malam), jumlah R/ & HV, pendapatan, non-tunai, cetak |
| **💰 Laporan Kas** | Pencatatan kredit/debit, saldo awal & akhir, cetak |
| **📊 Laporan** | Penjualan (cash/non-cash), stok (nilai inventori), pembelian |
| **💱 Format Rupiah** | Semua input harga otomatis format `Rp 10,000` |

---

## 🔧 Requirements

- PHP ≥ 8.2
- Composer
- MySQL 8.0+

---

## 🚀 Instalasi

```bash
# 1. Clone repository
git clone https://github.com/dimasalvin/kasir2.git
cd kasir2

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Buat database MySQL
#    Buka MySQL lalu jalankan: CREATE DATABASE kasir_pos;

# 5. Sesuaikan .env (jika perlu)
#    DB_DATABASE=kasir_pos
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Migrasi & seed data awal
php artisan migrate --seed

# 7. Jalankan
php artisan serve
```

Buka **http://localhost:8000** di browser.

---

## 👤 Akun Default

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@kasirpos.com` | `admin123` |
| Kasir | `kasir@kasirpos.com` | `kasir123` |

> **Single Login** — Satu akun hanya bisa aktif di satu perangkat. Login di perangkat lain akan memutus sesi sebelumnya.

---

## 🗂️ Struktur Database

```
users               → id, name, email, password, role, login_token
kategoris           → id, nama, warna
suppliers           → id, nama, alamat, no_telp, jatuh_tempo
barangs             → id, kode_barang, barcode, nama_barang, satuan,
                       kategori_id, supplier_id, harga_beli,
                       harga_jual_umum, harga_jual_resep, stok, stok_minimum
pembelians          → id, no_faktur, tanggal, supplier_id, total, diskon,
                       grand_total, keterangan, user_id
pembelian_details   → id, pembelian_id, barang_id, qty, harga_beli, diskon, subtotal
transaksis          → id, no_nota, tanggal, pelanggan, tipe_harga, total,
                       diskon, grand_total, bayar, kembalian, metode_bayar, user_id
transaksi_details   → id, transaksi_id, barang_id, nama_barang, qty,
                       harga, diskon, subtotal
stock_opnames       → id, tanggal, barang_id, stok_sistem, stok_fisik,
                       selisih, keterangan, user_id
closing_kasirs      → id, tanggal, shift, jumlah_resep, jumlah_hv,
                       pendapatan_resep, pendapatan_hv, total_pendapatan,
                       non_tunai, total, user_id
laporan_kas         → id, tanggal, keterangan, kredit, debit,
                       tanggal_transaksi, user_id
saldo_kas           → id, saldo
```

---

## 📁 Struktur Project

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php          # Login, logout, single login
│   │   ├── DashboardController.php     # Statistik & grafik
│   │   ├── BarangController.php        # CRUD barang + API barcode
│   │   ├── SupplierController.php      # CRUD supplier
│   │   ├── KasirController.php         # Transaksi kasir + struk PDF
│   │   ├── PembelianController.php     # Input pembelian
│   │   ├── StockOpnameController.php   # Stock opname
│   │   ├── ClosingKasirController.php  # Closing per shift
│   │   ├── LaporanKasController.php    # Laporan kas kredit/debit
│   │   └── LaporanController.php       # Laporan penjualan/stok/pembelian
│   └── Middleware/
│       ├── RoleMiddleware.php          # Proteksi akses berdasarkan role
│       └── SingleLoginMiddleware.php   # Satu akun = satu perangkat
├── Models/
│   ├── User.php            ├── Transaksi.php
│   ├── Barang.php          ├── TransaksiDetail.php
│   ├── Kategori.php        ├── StockOpname.php
│   ├── Supplier.php        ├── ClosingKasir.php
│   ├── Pembelian.php       ├── LaporanKas.php
│   └── PembelianDetail.php └── SaldoKas.php
└── Providers/
    └── AppServiceProvider.php

resources/views/
├── layouts/dashboard.blade.php     # Layout utama (sidebar, topbar, CSS)
├── auth/login.blade.php
├── dashboard/index.blade.php
├── kasir/          (index, struk, struk-pdf)
├── barang/         (index, create, edit)
├── supplier/       (index, create, edit)
├── pembelian/      (index, create, show)
├── stock-opname/   (index, create, show)
├── closing-kasir/  (index, create, cetak)
├── laporan-kas/    (index, create, cetak)
└── laporan/        (penjualan, stok, pembelian)
```

---

## 🔄 Flow Aplikasi

```
Login ──► Dashboard
            │
            ├── Admin: Kelola Barang, Supplier, Kategori
            │
            ├── Admin: Input Pembelian ──► Stok +
            │
            ├── Kasir/Admin: Scan Barcode ──► Keranjang
            │       │
            │       ├── Toggle Resep / Non Resep
            │       ├── Input Qty, Diskon
            │       ├── Pilih Cash / Non-Cash
            │       └── Bayar ──► Stok − ──► Cetak Struk
            │
            ├── Admin: Closing Kasir (per shift)
            │
            ├── Admin: Stock Opname ──► Update Stok
            │
            ├── Admin: Laporan Kas (kredit/debit/saldo)
            │
            └── Admin: Laporan (Penjualan, Stok, Pembelian)
```

---

## ⚙️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8.0
- **Frontend**: Blade + Vanilla JS (tanpa framework CSS/JS tambahan)
- **PDF**: barryvdh/laravel-dompdf
- **Chart**: Chart.js 4

---

## 📝 License

MIT License
