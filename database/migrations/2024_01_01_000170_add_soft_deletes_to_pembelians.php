<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah soft delete pada pembelians agar data tidak hilang permanen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
