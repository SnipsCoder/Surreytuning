<?php

namespace App\Models;

use App\Enums\FuelType;
use Illuminate\Database\Eloquent\Model;

class VehicleStat extends Model
{
    protected $fillable = [
        'make',
        'model',
        'year_from',
        'year_to',
        'generation',
        'engine',
        'fuel',
        'bhp_before',
        'bhp_after',
        'torque_before_nm',
        'torque_after_nm',
        'stage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fuel' => FuelType::class,
            'bhp_before' => 'decimal:2',
            'bhp_after' => 'decimal:2',
            'torque_before_nm' => 'decimal:2',
            'torque_after_nm' => 'decimal:2',
        ];
    }
}
