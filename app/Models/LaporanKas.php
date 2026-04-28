<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKas extends Model
{
    protected $table = 'laporan_kas';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'kredit',
        'debit',
        'tanggal_transaksi',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'            => 'date',
            'tanggal_transaksi'  => 'date',
            'kredit'             => 'decimal:2',
            'debit'              => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
