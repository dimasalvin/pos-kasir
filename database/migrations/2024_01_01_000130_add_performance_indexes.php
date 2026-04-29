<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transaksis — tabel paling sering di-query untuk laporan
        Schema::table('transaksis', function (Blueprint $table) {
            $table->index(['tanggal', 'metode_bayar', 'created_at'], 'idx_transaksis_tanggal_metode_created');
            $table->index('pasien_nama', 'idx_transaksis_pasien_nama');
        });

        // Transaksi Details — pencarian nama obat
        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->index('nama_barang', 'idx_transaksi_details_nama_barang');
        });

        // Pembelians — filter tanggal
        Schema::table('pembelians', function (Blueprint $table) {
            $table->index('tanggal', 'idx_pembelians_tanggal');
        });

        // Barangs — pencarian nama
        Schema::table('barangs', function (Blueprint $table) {
            $table->index('nama_barang', 'idx_barangs_nama_barang');
            $table->index(['stok', 'stok_minimum'], 'idx_barangs_stok_minimum');
        });

        // Laporan Kas — filter tanggal
        Schema::table('laporan_kas', function (Blueprint $table) {
            $table->index('tanggal', 'idx_laporan_kas_tanggal');
        });

        // Stock Opnames — filter tanggal
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->index('tanggal', 'idx_stock_opnames_tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropIndex('idx_transaksis_tanggal_metode_created');
            $table->dropIndex('idx_transaksis_pasien_nama');
        });
        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->dropIndex('idx_transaksi_details_nama_barang');
        });
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropIndex('idx_pembelians_tanggal');
        });
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropIndex('idx_barangs_nama_barang');
            $table->dropIndex('idx_barangs_stok_minimum');
        });
        Schema::table('laporan_kas', function (Blueprint $table) {
            $table->dropIndex('idx_laporan_kas_tanggal');
        });
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropIndex('idx_stock_opnames_tanggal');
        });
    }
};
