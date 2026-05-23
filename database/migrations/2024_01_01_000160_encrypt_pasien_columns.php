<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perbesar kolom pasien_telp dan pasien_alamat untuk menampung data terenkripsi.
 * Data terenkripsi lebih panjang dari plaintext.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->text('pasien_telp')->nullable()->change();
            $table->text('pasien_alamat')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->string('pasien_telp')->nullable()->change();
            $table->string('pasien_alamat', 500)->nullable()->change();
        });
    }
};
