<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = [
        'kode_barang',
        'barcode',
        'nama_barang',
        'satuan',
        'kategori_id',
        'supplier_id',
        'harga_beli',
        'harga_jual_umum',
        'harga_jual_resep',
        'stok',
        'stok_minimum',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli'       => 'decimal:2',
            'harga_jual_umum'  => 'decimal:2',
            'harga_jual_resep' => 'decimal:2',
        ];
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function transaksiDetails()
    {
        return $this->hasMany(TransaksiDetail::class);
    }

    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    /**
     * Cek apakah stok di bawah minimum
     */
    public function isStokRendah(): bool
    {
        return $this->stok < $this->stok_minimum;
    }

    /**
     * Scope: barang dengan stok rendah
     */
    public function scopeStokRendah($query)
    {
        return $query->whereColumn('stok', '<', 'stok_minimum');
    }
}
