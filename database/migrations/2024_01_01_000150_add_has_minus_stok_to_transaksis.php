<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom has_minus_stok untuk menandai transaksi yang mengakibatkan stok minus.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->boolean('has_minus_stok')->default(false)->after('has_resep');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('has_minus_stok');
        });
    }
};
