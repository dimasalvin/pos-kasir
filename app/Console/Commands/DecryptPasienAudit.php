<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Command untuk decrypt & export data pasien untuk keperluan audit.
 *
 * Usage:
 *   php artisan pasien:decrypt-audit                    # Semua data
 *   php artisan pasien:decrypt-audit --dari=2024-01-01  # Filter tanggal
 *   php artisan pasien:decrypt-audit --id=123           # Transaksi spesifik
 */
class DecryptPasienAudit extends Command
{
    protected $signature = 'pasien:decrypt-audit
                            {--dari= : Tanggal mulai (YYYY-MM-DD)}
                            {--sampai= : Tanggal akhir (YYYY-MM-DD)}
                            {--id= : ID transaksi spesifik}';

    protected $description = 'Decrypt data pasien untuk keperluan audit';

    public function handle(): int
    {
        $query = DB::table('transaksis')
            ->whereNotNull('pasien_nama')
            ->select('id', 'no_nota', 'tanggal', 'pasien_nama', 'pasien_telp', 'pasien_alamat');

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        if ($dari = $this->option('dari')) {
            $query->where('tanggal', '>=', $dari);
        }

        if ($sampai = $this->option('sampai')) {
            $query->where('tanggal', '<=', $sampai);
        }

        $rows = $query->orderBy('tanggal', 'desc')->limit(100)->get();

        if ($rows->isEmpty()) {
            $this->warn('Tidak ada data pasien ditemukan.');
            return Command::SUCCESS;
        }

        $tableData = [];
        foreach ($rows as $row) {
            $telp = $row->pasien_telp;
            $alamat = $row->pasien_alamat;

            // Decrypt jika terenkripsi
            try {
                if ($telp && str_starts_with($telp, 'eyJ')) {
                    $telp = Crypt::decryptString($telp);
                }
            } catch (\Exception $e) {
                $telp = '[DECRYPT ERROR]';
            }

            try {
                if ($alamat && str_starts_with($alamat, 'eyJ')) {
                    $alamat = Crypt::decryptString($alamat);
                }
            } catch (\Exception $e) {
                $alamat = '[DECRYPT ERROR]';
            }

            $tableData[] = [
                $row->id,
                $row->no_nota,
                $row->tanggal,
                $row->pasien_nama,
                $telp,
                $alamat,
            ];
        }

        $this->table(
            ['ID', 'No Nota', 'Tanggal', 'Nama Pasien', 'Telp', 'Alamat'],
            $tableData
        );

        $this->info("Total: " . count($tableData) . " record (max 100)");

        return Command::SUCCESS;
    }
}
