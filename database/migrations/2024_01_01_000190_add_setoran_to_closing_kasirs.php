<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom setoran di closing kasir.
 * Setoran = uang yang diambil pemilik selama shift (opsional).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('closing_kasirs', function (Blueprint $table) {
            $table->decimal('setoran', 15, 2)->default(0)->after('uang_fisik');
        });
    }

    public function down(): void
    {
        Schema::table('closing_kasirs', function (Blueprint $table) {
            $table->dropColumn('setoran');
        });
    }
};
