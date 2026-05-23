<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Command untuk encrypt data pasien yang sudah ada (migrasi data lama).
 * Jalankan SEKALI setelah deploy migration 000160.
 *
 * Usage: php artisan pasien:encrypt-existing
 */
class EncryptExistingPasienData extends Command
{
    protected $signature = 'pasien:encrypt-existing';
    protected $description = 'Encrypt data pasien_telp & pasien_alamat yang masih plaintext';

    public function handle(): int
    {
        $rows = DB::table('transaksis')
            ->whereNotNull('pasien_telp')
            ->orWhereNotNull('pasien_alamat')
            ->select('id', 'pasien_telp', 'pasien_alamat')
            ->get();

        $encrypted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $updates = [];

            // Cek apakah sudah terenkripsi (encrypted string dimulai dengan 'eyJ')
            if ($row->pasien_telp && !str_starts_with($row->pasien_telp, 'eyJ')) {
                $updates['pasien_telp'] = Crypt::encryptString($row->pasien_telp);
            }

            if ($row->pasien_alamat && !str_starts_with($row->pasien_alamat, 'eyJ')) {
                $updates['pasien_alamat'] = Crypt::encryptString($row->pasien_alamat);
            }

            if (!empty($updates)) {
                DB::table('transaksis')->where('id', $row->id)->update($updates);
                $encrypted++;
            } else {
                $skipped++;
            }
        }

        $this->info("Selesai. Encrypted: {$encrypted}, Skipped (sudah encrypted): {$skipped}");

        return Command::SUCCESS;
    }
}
