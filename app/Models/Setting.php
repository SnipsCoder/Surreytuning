<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    const CREATED_AT = null;

    protected $fillable = [
        'invoice_address',
        'returns_address',
        'vat_number',
        'vat_rate',
        'company_number',
        'bcc_invoice_email',
        'invoice_start_number',
        'invoice_reference_prefix',
        'logo_light',
        'logo_dark',
        'login_background',
        'theme_colour',
        'dealer_auto_onboard',
        'terms_and_conditions',
        'stripe_public_key',
        'stripe_secret_key',
        'evc_account_number',
        'evc_password',
        'whatsapp_business_number',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'dealer_auto_onboard' => 'boolean',
        ];
    }

    protected static ?self $instance = null;

    public static function get(): self
    {
        if (static::$instance === null) {
            static::$instance = static::firstOrCreate(['id' => 1], [
                'vat_rate' => 20.00,
                'invoice_start_number' => 10000,
                'invoice_reference_prefix' => 'INV',
                'theme_colour' => '#e63012',
            ]);
        }

        return static::$instance;
    }

    public static function clearCache(): void
    {
        static::$instance = null;
    }
}
