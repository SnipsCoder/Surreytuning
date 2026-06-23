<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOrder extends Model
{
    protected $fillable = [
        'dealer_id',
        'user_id',
        'product_id',
        'quantity',
        'unit_price_net',
        'vat_amount',
        'total_gross',
        'payment_method',
        'stripe_payment_intent_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_net' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_gross' => 'decimal:2',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
