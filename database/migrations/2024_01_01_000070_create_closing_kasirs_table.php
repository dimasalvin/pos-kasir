<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closing_kasirs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('shift', ['pagi', 'malam']);
            $table->integer('jumlah_resep')->default(0);        // R/
            $table->integer('jumlah_hv')->default(0);            // HV (non-resep)
            $table->decimal('pendapatan_resep', 15, 2)->default(0);
            $table->decimal('pendapatan_hv', 15, 2)->default(0);
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('non_tunai', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);         // total_pendapatan - non_tunai (tunai)
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->unique(['tanggal', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closing_kasirs');
    }
};
