<?php

namespace App\Models;

use App\Enums\SlaveCreditTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaveCreditTransaction extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'dealer_id',
        'user_id',
        'file_request_id',
        'type',
        'amount',
        'reason',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'type' => SlaveCreditTransactionType::class,
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fileRequest(): BelongsTo
    {
        return $this->belongsTo(FileRequest::class);
    }
}
