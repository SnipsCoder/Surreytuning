<?php

namespace App\Models;

use App\Enums\TuningToolCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'category' => TuningToolCategory::class,
            'is_active' => 'boolean',
        ];
    }

    public function fileRequests(): HasMany
    {
        return $this->hasMany(FileRequest::class);
    }
}
