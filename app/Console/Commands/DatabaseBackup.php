<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Backup database MySQL ke file .sql.gz
 *
 * Setup di shared hosting Rumahweb:
 * 1. Upload project ke public_html atau subdomain
 * 2. Buat folder backup di LUAR public_html: ~/backups/
 * 3. Set cron job di cPanel:
 *    0 2 * * * cd /home/username/public_html && /usr/local/bin/php artisan db:backup
 *    (Jalan setiap jam 2 pagi)
 *
 * File backup disimpan di: storage/app/backups/ (atau path custom via .env)
 * Retensi: 7 hari (file lama otomatis dihapus)
 *
 * PENTING: Pastikan folder backup TIDAK bisa diakses publik!
 */
class DatabaseBackup extends Command
{
    protected $signature = 'db:backup {--path= : Custom backup path}';
    protected $description = 'Backup database MySQL ke file .sql.gz';

    public function handle(): int
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Tentukan path backup
        $backupPath = $this->option('path')
            ?? env('BACKUP_PATH')
            ?? storage_path('app/backups');

        // Buat folder jika belum ada
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Proteksi: tambah .htaccess agar tidak bisa diakses web
        $htaccess = $backupPath . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }

        $filename = "backup-{$dbName}-" . date('Y-m-d_His') . '.sql.gz';
        $filepath = $backupPath . '/' . $filename;

        // Jalankan mysqldump
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s | gzip > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($filepath)
        );

        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $error = implode("\n", $output);
            Log::error('Database backup gagal', ['error' => $error]);
            $this->error("Backup gagal: {$error}");
            return Command::FAILURE;
        }

        // Cek file valid (minimal 100 bytes)
        if (!file_exists($filepath) || filesize($filepath) < 100) {
            Log::error('Database backup file terlalu kecil atau tidak ada', ['path' => $filepath]);
            $this->error('Backup file tidak valid.');
            return Command::FAILURE;
        }

        $size = $this->formatBytes(filesize($filepath));
        Log::info('Database backup berhasil', [
            'file' => $filename,
            'size' => $size,
        ]);
        $this->info("Backup berhasil: {$filename} ({$size})");

        // Hapus backup lama (retensi 7 hari)
        $this->cleanOldBackups($backupPath, 7);

        return Command::SUCCESS;
    }

    /**
     * Hapus file backup yang lebih tua dari $days hari
     */
    private function cleanOldBackups(string $path, int $days): void
    {
        $threshold = time() - ($days * 86400);
        $files = glob($path . '/backup-*.sql.gz');

        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Dihapus {$deleted} backup lama (>{$days} hari).");
            Log::info("Backup cleanup: {$deleted} file dihapus");
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
