<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticeboard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by_user_id',
        'title',
        'body',
        'priority',
        'show_from',
        'show_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'show_from' => 'date',
            'show_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('show_from')->orWhere('show_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('show_until')->orWhere('show_until', '>=', $today);
            });
    }
}
