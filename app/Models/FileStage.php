<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileStage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'vehicle_type',
        'price_net',
        'vat_applicable',
        'turnaround_hours',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_type' => \App\Enums\VehicleType::class,
            'price_net' => 'decimal:2',
            'vat_applicable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function fileRequests(): HasMany
    {
        return $this->hasMany(FileRequest::class);
    }

    public function fileOptions(): HasMany
    {
        return $this->hasMany(FileOption::class);
    }
}
