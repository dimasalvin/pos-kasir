<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'nama_barang',
        'qty',
        'harga',
        'diskon',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga'    => 'decimal:2',
            'diskon'   => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
