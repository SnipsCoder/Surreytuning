<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuningTool extends Model
{
    protected $fillable = [
        'name',
        'category',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
