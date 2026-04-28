<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->decimal('kredit', 15, 2)->default(0);   // uang keluar
            $table->decimal('debit', 15, 2)->default(0);     // uang masuk
            $table->date('tanggal_transaksi')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        // Saldo awal kas
        Schema::create('saldo_kas', function (Blueprint $table) {
            $table->id();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_kas');
        Schema::dropIfExists('laporan_kas');
    }
};
