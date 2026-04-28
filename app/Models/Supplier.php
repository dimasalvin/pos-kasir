<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'nama',
        'alamat',
        'no_telp',
        'jatuh_tempo',
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class);
    }
}
