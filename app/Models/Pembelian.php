<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $fillable = [
        'no_faktur',
        'tanggal',
        'supplier_id',
        'total',
        'diskon',
        'grand_total',
        'keterangan',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'     => 'date',
            'total'       => 'decimal:2',
            'diskon'      => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PembelianDetail::class);
    }

    /**
     * Generate nomor faktur otomatis
     */
    public static function generateNoFaktur(): string
    {
        $prefix = 'PB-' . date('Ymd');
        $last = static::where('no_faktur', 'like', $prefix . '%')
            ->orderBy('no_faktur', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->no_faktur, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
