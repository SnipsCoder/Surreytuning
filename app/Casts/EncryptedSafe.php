<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts a value at rest and decrypts it on read. Unlike the built-in
 * "encrypted" cast, a value that fails to decrypt (legacy plaintext, or data
 * encrypted under a rotated APP_KEY) is treated as unset rather than throwing —
 * so a stale key just reads as null and can be re-entered, never 500ing a page.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EncryptedSafe implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return [$key => null];
        }

        return [$key => Crypt::encryptString((string) $value)];
    }
}
