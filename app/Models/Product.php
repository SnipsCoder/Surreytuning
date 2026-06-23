<?php

namespace App\Models;

use App\Enums\ProductPaymentType;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_net',
        'vat_applicable',
        'payment_type',
        'stock',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_net' => 'decimal:2',
            'vat_applicable' => 'boolean',
            'payment_type' => ProductPaymentType::class,
            'stock' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
