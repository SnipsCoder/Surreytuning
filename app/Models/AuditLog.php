<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'amount',
        'reason',
        'metadata',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Record an audit entry for a sensitive action. Captures the acting user and
     * request IP automatically so callers only supply the action-specific data.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function record(string $action, ?User $actor = null, ?Model $subject = null, ?float $amount = null, ?string $reason = null, array $metadata = []): self
    {
        return static::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'amount' => $amount,
            'reason' => $reason,
            'metadata' => $metadata ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
