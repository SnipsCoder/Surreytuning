<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WinolsBundle extends Model
{
    protected $fillable = [
        'name',
        'credits',
        'price_net',
        'is_active',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price_net' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(EvcCreditTransaction::class);
    }
}
