<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'qty',
        'harga_beli',
        'diskon_tipe',
        'diskon',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'diskon'     => 'decimal:2',
            'subtotal'   => 'decimal:2',
        ];
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
