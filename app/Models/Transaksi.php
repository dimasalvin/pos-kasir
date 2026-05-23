<?php

namespace App\Models;

use App\Casts\EncryptedString;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $fillable = [
        'no_nota',
        'tanggal',
        'pelanggan',
        'pasien_nama',
        'pasien_telp',
        'pasien_alamat',
        'tipe_harga',
        'has_resep',
        'has_minus_stok',
        'total',
        'diskon',
        'grand_total',
        'bayar',
        'kembalian',
        'metode_bayar',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'        => 'date',
            'has_resep'      => 'boolean',
            'has_minus_stok' => 'boolean',
            'total'          => 'decimal:2',
            'diskon'         => 'decimal:2',
            'grand_total'    => 'decimal:2',
            'bayar'          => 'decimal:2',
            'kembalian'      => 'decimal:2',
            'pasien_telp'    => EncryptedString::class,
            'pasien_alamat'  => EncryptedString::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransaksiDetail::class);
    }

    /**
     * Generate nomor nota otomatis (harus dipanggil dalam DB::transaction)
     */
    public static function generateNoNota(): string
    {
        $prefix = 'INV-' . date('Ymd');
        $last = static::where('no_nota', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('no_nota', 'desc')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->no_nota, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
