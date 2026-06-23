<?php

namespace App\Models;

use App\Enums\EvcCreditTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvcCreditTransaction extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'dealer_id',
        'user_id',
        'winols_bundle_id',
        'type',
        'amount',
        'reason',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'type' => EvcCreditTransactionType::class,
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

    public function winolsBundle(): BelongsTo
    {
        return $this->belongsTo(WinolsBundle::class);
    }
}
