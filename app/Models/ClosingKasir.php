<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosingKasir extends Model
{
    protected $fillable = [
        'tanggal',
        'shift',
        'jumlah_resep',
        'jumlah_hv',
        'pendapatan_resep',
        'pendapatan_hv',
        'total_pendapatan',
        'non_tunai',
        'total',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'           => 'date',
            'pendapatan_resep'  => 'decimal:2',
            'pendapatan_hv'     => 'decimal:2',
            'total_pendapatan'  => 'decimal:2',
            'non_tunai'         => 'decimal:2',
            'total'             => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung closing dari data transaksi
     */
    public static function hitungDariTransaksi(string $tanggal, string $shift): array
    {
        $query = Transaksi::whereDate('tanggal', $tanggal);

        // Shift pagi: 07:00 - 13:59, Shift siang: 14:00 - 21:00
        if ($shift === 'pagi') {
            $query->whereTime('created_at', '>=', '07:00:00')
                  ->whereTime('created_at', '<', '14:00:00');
        } else {
            $query->whereTime('created_at', '>=', '14:00:00')
                  ->whereTime('created_at', '<=', '21:00:00');
        }

        // Single aggregate query — no PHP-level filtering
        $result = $query->selectRaw('
            SUM(CASE WHEN tipe_harga = "resep" THEN 1 ELSE 0 END) as jumlah_resep,
            SUM(CASE WHEN tipe_harga = "umum" THEN 1 ELSE 0 END) as jumlah_hv,
            SUM(CASE WHEN tipe_harga = "resep" THEN grand_total ELSE 0 END) as pendapatan_resep,
            SUM(CASE WHEN tipe_harga = "umum" THEN grand_total ELSE 0 END) as pendapatan_hv,
            SUM(grand_total) as total_pendapatan,
            SUM(CASE WHEN metode_bayar = "non-cash" THEN grand_total ELSE 0 END) as non_tunai
        ')->first();

        $totalPendapatan = $result->total_pendapatan ?? 0;
        $nonTunai = $result->non_tunai ?? 0;

        return [
            'jumlah_resep'     => (int) ($result->jumlah_resep ?? 0),
            'jumlah_hv'        => (int) ($result->jumlah_hv ?? 0),
            'pendapatan_resep' => $result->pendapatan_resep ?? 0,
            'pendapatan_hv'    => $result->pendapatan_hv ?? 0,
            'total_pendapatan' => $totalPendapatan,
            'non_tunai'        => $nonTunai,
            'total'            => $totalPendapatan - $nonTunai,
        ];
    }
}
