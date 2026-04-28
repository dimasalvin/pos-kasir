<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ClosingKasirController;
use App\Http\Controllers\LaporanKasController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Kasir (Admin & Kasir) ──
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/kasir/store', [KasirController::class, 'store'])->name('kasir.store');
    Route::get('/kasir/struk/{transaksi}', [KasirController::class, 'struk'])->name('kasir.struk');
    Route::get('/kasir/struk-pdf/{transaksi}', [KasirController::class, 'strukPdf'])->name('kasir.struk-pdf');

    // ── API Barang (untuk kasir) ──
    Route::get('/api/barang/barcode', [BarangController::class, 'findByBarcode'])->name('api.barang.barcode');
    Route::get('/api/barang/search', [BarangController::class, 'search'])->name('api.barang.search');

    // ── Admin Only Routes ──
    Route::middleware('role:admin')->group(function () {

        // Master Barang
        Route::resource('barang', BarangController::class);

        // Master Supplier
        Route::resource('supplier', SupplierController::class);

        // Pembelian
        Route::resource('pembelian', PembelianController::class)->except(['edit', 'update']);

        // Stock Opname
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');
        Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('stock-opname.create');
        Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');
        Route::get('/stock-opname/{stockOpname}', [StockOpnameController::class, 'show'])->name('stock-opname.show');

        // Closing Kasir
        Route::get('/closing-kasir', [ClosingKasirController::class, 'index'])->name('closing-kasir.index');
        Route::get('/closing-kasir/create', [ClosingKasirController::class, 'create'])->name('closing-kasir.create');
        Route::post('/closing-kasir/preview', [ClosingKasirController::class, 'preview'])->name('closing-kasir.preview');
        Route::post('/closing-kasir', [ClosingKasirController::class, 'store'])->name('closing-kasir.store');
        Route::delete('/closing-kasir/{closingKasir}', [ClosingKasirController::class, 'destroy'])->name('closing-kasir.destroy');
        Route::get('/closing-kasir/cetak', [ClosingKasirController::class, 'cetak'])->name('closing-kasir.cetak');

        // Laporan Kas
        Route::get('/laporan-kas', [LaporanKasController::class, 'index'])->name('laporan-kas.index');
        Route::get('/laporan-kas/create', [LaporanKasController::class, 'create'])->name('laporan-kas.create');
        Route::post('/laporan-kas', [LaporanKasController::class, 'store'])->name('laporan-kas.store');
        Route::delete('/laporan-kas/{laporanKa}', [LaporanKasController::class, 'destroy'])->name('laporan-kas.destroy');
        Route::post('/laporan-kas/saldo', [LaporanKasController::class, 'updateSaldo'])->name('laporan-kas.update-saldo');
        Route::get('/laporan-kas/cetak', [LaporanKasController::class, 'cetak'])->name('laporan-kas.cetak');

        // Laporan
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
            Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
            Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        });
    });
});
