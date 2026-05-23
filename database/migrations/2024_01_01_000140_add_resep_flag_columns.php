<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom has_resep di transaksis dan is_resep_item di transaksi_details.
     * Semua transaksi sekarang tipe 'umum', tapi bisa mengandung item resep.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->boolean('has_resep')->default(false)->after('tipe_harga');
        });

        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->boolean('is_resep_item')->default(false)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('has_resep');
        });

        Schema::table('transaksi_details', function (Blueprint $table) {
            $table->dropColumn('is_resep_item');
        });
    }
};
