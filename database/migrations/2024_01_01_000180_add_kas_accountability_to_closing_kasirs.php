<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom untuk akuntabilitas kas di closing kasir:
 * - uang_fisik: jumlah uang tunai yang dihitung fisik di laci
 * - selisih: uang_fisik - total (seharusnya tunai)
 * - keterangan: catatan jika ada selisih
 * - modal_awal: uang modal di awal shift
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('closing_kasirs', function (Blueprint $table) {
            $table->decimal('modal_awal', 15, 2)->default(0)->after('total');
            $table->decimal('uang_fisik', 15, 2)->nullable()->after('modal_awal');
            $table->decimal('selisih', 15, 2)->nullable()->after('uang_fisik');
            $table->text('keterangan')->nullable()->after('selisih');
        });
    }

    public function down(): void
    {
        Schema::table('closing_kasirs', function (Blueprint $table) {
            $table->dropColumn(['modal_awal', 'uang_fisik', 'selisih', 'keterangan']);
        });
    }
};
