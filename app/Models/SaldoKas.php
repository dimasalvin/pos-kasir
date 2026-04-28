<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoKas extends Model
{
    protected $table = 'saldo_kas';

    protected $fillable = ['saldo'];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
        ];
    }

    /**
     * Ambil saldo saat ini (singleton row)
     */
    public static function getSaldo(): float
    {
        $row = static::first();
        return $row ? (float) $row->saldo : 0;
    }

    /**
     * Set saldo
     */
    public static function setSaldo(float $saldo): void
    {
        $row = static::first();
        if ($row) {
            $row->update(['saldo' => $saldo]);
        } else {
            static::create(['saldo' => $saldo]);
        }
    }
}
