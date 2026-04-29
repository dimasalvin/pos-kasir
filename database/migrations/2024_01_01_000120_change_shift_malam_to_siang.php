<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah 'siang' ke enum dulu (pagi, malam, siang)
        DB::statement("ALTER TABLE closing_kasirs MODIFY COLUMN shift ENUM('pagi', 'malam', 'siang') NOT NULL");

        // Update data existing: malam → siang
        DB::table('closing_kasirs')->where('shift', 'malam')->update(['shift' => 'siang']);

        // Hapus 'malam' dari enum (pagi, siang)
        DB::statement("ALTER TABLE closing_kasirs MODIFY COLUMN shift ENUM('pagi', 'siang') NOT NULL");
    }

    public function down(): void
    {
        DB::table('closing_kasirs')->where('shift', 'siang')->update(['shift' => 'malam']);
        DB::statement("ALTER TABLE closing_kasirs MODIFY COLUMN shift ENUM('pagi', 'malam') NOT NULL");
    }
};
