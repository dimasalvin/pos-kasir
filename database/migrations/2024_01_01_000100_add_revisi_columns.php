<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah data pasien di transaksi (untuk resep)
        Schema::table('transaksis', function (Blueprint $table) {
            $table->string('pasien_nama')->nullable()->after('pelanggan');
            $table->string('pasien_telp')->nullable()->after('pasien_nama');
            $table->text('pasien_alamat')->nullable()->after('pasien_telp');
        });

        // Tambah tipe diskon di pembelian_details (persen/rupiah)
        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->enum('diskon_tipe', ['rupiah', 'persen'])->default('rupiah')->after('harga_beli');
        });

        // Tambah tipe diskon di pembelians (persen/rupiah)
        Schema::table('pembelians', function (Blueprint $table) {
            $table->enum('diskon_tipe', ['rupiah', 'persen'])->default('rupiah')->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn(['pasien_nama', 'pasien_telp', 'pasien_alamat']);
        });

        Schema::table('pembelian_details', function (Blueprint $table) {
            $table->dropColumn('diskon_tipe');
        });

        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('diskon_tipe');
        });
    }
};
