<?php

namespace App\Models;

use App\Enums\PortalStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalStatus extends Model
{
    protected $table = 'portal_status';

    protected $fillable = [
        'status',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PortalStatusEnum::class,
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['status' => PortalStatusEnum::Available]);
    }
}
