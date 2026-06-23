<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WinolsBundle extends Model
{
    protected $fillable = [
        'name',
        'credits',
        'price_net',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_net' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
