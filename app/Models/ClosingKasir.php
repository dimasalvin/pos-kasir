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

        // Shift pagi: 00:00 - 14:59, Shift malam: 15:00 - 23:59
        if ($shift === 'pagi') {
            $query->whereTime('created_at', '<', '15:00:00');
        } else {
            $query->whereTime('created_at', '>=', '15:00:00');
        }

        $transaksis = $query->get();

        $jumlahResep = $transaksis->where('tipe_harga', 'resep')->count();
        $jumlahHv = $transaksis->where('tipe_harga', 'umum')->count();
        $pendapatanResep = $transaksis->where('tipe_harga', 'resep')->sum('grand_total');
        $pendapatanHv = $transaksis->where('tipe_harga', 'umum')->sum('grand_total');
        $totalPendapatan = $pendapatanResep + $pendapatanHv;
        $nonTunai = $transaksis->where('metode_bayar', 'non-cash')->sum('grand_total');
        $total = $totalPendapatan - $nonTunai;

        return [
            'jumlah_resep'     => $jumlahResep,
            'jumlah_hv'        => $jumlahHv,
            'pendapatan_resep' => $pendapatanResep,
            'pendapatan_hv'    => $pendapatanHv,
            'total_pendapatan' => $totalPendapatan,
            'non_tunai'        => $nonTunai,
            'total'            => $total,
        ];
    }
}
