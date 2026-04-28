<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'login_token',
    ];

    protected $hidden = [
        'password',
        'login_token',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }
}
