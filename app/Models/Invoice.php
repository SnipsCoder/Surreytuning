<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    protected $fillable = [
        'dealer_id',
        'user_id',
        'invoice_number',
        'description',
        'amount_net',
        'vat_amount',
        'amount_gross',
        'type',
        'related_id',
        'related_type',
        'status',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_net' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'amount_gross' => 'decimal:2',
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'paid_at' => 'datetime',
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

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
