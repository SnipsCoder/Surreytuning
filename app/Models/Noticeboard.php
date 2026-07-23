<?php

namespace App\Models;

use App\Enums\NoticePriority;
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
        'show_on_front_page',
    ];

    protected function casts(): array
    {
        return [
            'priority' => NoticePriority::class,
            'show_from' => 'date',
            'show_until' => 'date',
            'is_active' => 'boolean',
            'show_on_front_page' => 'boolean',
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
            })
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 WHEN 'low' THEN 3 ELSE 4 END");
    }

    /**
     * Currently-visible notices flagged to pin as a prominent "out of office"
     * banner on the dealer dashboard. Builds on scopeActive() so the same
     * show_from/show_until scheduling applies.
     */
    public function scopeFrontPage($query)
    {
        return $query->active()->where('show_on_front_page', true);
    }
}
