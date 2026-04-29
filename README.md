# 💊 Kasir POS

Aplikasi **Point of Sale (POS)** berbasis **Laravel 12** & **MySQL** untuk apotek/toko obat. Dilengkapi fitur kasir dengan pencarian barang, multi harga (resep/non-resep), data pasien untuk resep, manajemen stok, stock opname, closing kasir per shift, dan laporan lengkap.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)

---

## ✨ Fitur Lengkap

| Modul | Fitur |
|-------|-------|
| **🔐 Auth** | Login, logout, role (Admin/Kasir), **single login** (1 akun = 1 perangkat) |
| **📦 Barang** | CRUD, multi harga (umum & resep), kategori warna, notifikasi stok rendah |
| **🏭 Supplier** | CRUD supplier, jatuh tempo |
| **🛒 Kasir** | Pencarian barang (kode/nama), keranjang interaktif, toggle resep/non-resep, **data pasien wajib untuk resep** (nama, telp, alamat + autocomplete), cash/non-cash, cetak & download struk PDF |
| **📥 Pembelian** | Input pembelian multi item, diskon per item (Rp/%), stok otomatis bertambah, auto no. faktur |
| **📋 Stock Opname** | Input stok fisik, hitung selisih otomatis, riwayat opname |
| **🔒 Closing Kasir** | Rekap per shift (pagi 07:00-13:59 / siang 14:00-21:00), jumlah R/ & HV, pendapatan, non-tunai, **hitung ulang dari data terbaru**, cetak |
| **💰 Laporan Kas** | Pencatatan kredit/debit, saldo awal & akhir, cetak |
| **📊 Laporan** | Penjualan (filter: tanggal, nama obat dengan autocomplete, shift, metode bayar), riwayat penjualan per obat, stok (nilai inventori), pembelian |
| **💱 Format Rupiah** | Semua input harga otomatis format `Rp 10,000` |
| **⚡ Optimasi** | Database indexes, SQL aggregate queries, eager loading, pre-fetch batch queries |

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
php artisan serve --host=0.0.0.0 --port=8080
```

Buka **http://localhost:8000** di browser desktop/mobile di jaringan yang sama.

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
barangs             → id, kode_barang, nama_barang, satuan,
                       kategori_id, supplier_id, harga_beli,
                       harga_jual_umum, harga_jual_resep, stok, stok_minimum
                       📌 Index: nama_barang, (stok, stok_minimum)
pembelians          → id, no_faktur, tanggal, supplier_id, total, diskon,
                       grand_total, keterangan, user_id
                       📌 Index: tanggal
pembelian_details   → id, pembelian_id, barang_id, qty, harga_beli,
                       diskon_tipe, diskon, subtotal
transaksis          → id, no_nota, tanggal, pelanggan, pasien_nama,
                       pasien_telp, pasien_alamat, tipe_harga, total,
                       diskon, grand_total, bayar, kembalian, metode_bayar, user_id
                       📌 Index: (tanggal, metode_bayar, created_at), pasien_nama
transaksi_details   → id, transaksi_id, barang_id, nama_barang, qty,
                       harga, diskon, subtotal
                       📌 Index: nama_barang
stock_opnames       → id, tanggal, barang_id, stok_sistem, stok_fisik,
                       selisih, keterangan, user_id
                       📌 Index: tanggal
closing_kasirs      → id, tanggal, shift(pagi/siang), jumlah_resep, jumlah_hv,
                       pendapatan_resep, pendapatan_hv, total_pendapatan,
                       non_tunai, total, user_id
                       📌 Unique: (tanggal, shift)
laporan_kas         → id, tanggal, keterangan, kredit, debit,
                       tanggal_transaksi, user_id
                       📌 Index: tanggal
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
│   │   ├── BarangController.php        # CRUD barang + API search
│   │   ├── SupplierController.php      # CRUD supplier
│   │   ├── KasirController.php         # Transaksi kasir + struk PDF + API pasien
│   │   ├── PembelianController.php     # Input pembelian
│   │   ├── StockOpnameController.php   # Stock opname
│   │   ├── ClosingKasirController.php  # Closing per shift + hitung ulang
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
            ├── Kasir/Admin: Cari Barang (kode/nama) ──► Keranjang
            │       │
            │       ├── Toggle Non Resep / Resep
            │       ├── Non Resep: Pelanggan (opsional)
            │       ├── Resep: Data Pasien wajib (nama, telp, alamat)
            │       │           + autocomplete dari data sebelumnya
            │       ├── Input Qty, Diskon per item
            │       ├── Pilih Cash / Non-Cash
            │       └── Bayar ──► Stok − ──► Cetak Struk
            │
            ├── Admin: Closing Kasir (per shift pagi/siang)
            │       └── Hitung ulang dari data transaksi terbaru
            │
            ├── Admin: Stock Opname ──► Update Stok
            │
            ├── Admin: Laporan Kas (kredit/debit/saldo)
            │
            └── Admin: Laporan
                    ├── Penjualan (filter: tanggal, obat, shift, metode)
                    │       └── Riwayat penjualan per obat
                    ├── Stok (nilai inventori, stok rendah)
                    └── Pembelian
```

---

## ⚙️ Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8.0
- **Frontend**: Blade + Vanilla JS (tanpa framework CSS/JS tambahan)
- **PDF**: barryvdh/laravel-dompdf
- **Chart**: Chart.js 4

---

## ⚡ Optimasi Performa

Aplikasi dioptimasi untuk menangani data ribuan transaksi:

- **Database Indexes** — Composite & single index pada kolom yang sering di-query (tanggal, nama_barang, metode_bayar, dll)
- **SQL Aggregate** — Closing kasir & ringkasan laporan menggunakan `SUM(CASE WHEN ...)` dalam 1 query, bukan load semua data ke PHP
- **Query Deduplication** — Filter yang sama menggunakan `clone()` pada base query, bukan duplikasi kode
- **Eager Loading** — Relasi di-load dengan `with()` untuk menghindari N+1 query
- **Batch Pre-fetch** — Loop yang butuh data barang menggunakan `whereIn()` sekali, bukan `find()` per iterasi
- **Selective Columns** — Dropdown & API hanya mengambil kolom yang dibutuhkan
- **Sidebar Scroll Persist** — Posisi scroll sidebar disimpan di localStorage

---

## 📝 License

MIT License
