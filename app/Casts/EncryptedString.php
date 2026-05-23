<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypted string cast — data dienkripsi saat simpan, didekripsi saat baca.
 *
 * Untuk decrypt langsung dari database (audit), gunakan:
 *   php artisan tinker
 *   Crypt::decryptString($encryptedValue);
 *
 * Atau buat artisan command khusus untuk audit.
 */
class EncryptedString implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Jika gagal decrypt (data lama belum terenkripsi), return as-is
            return $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString($value);
    }
}

