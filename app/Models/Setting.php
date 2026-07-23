<?php

namespace App\Models;

use App\Casts\EncryptedSafe;
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
        'portal_logo',
        'invoice_header',
        'theme_colour',
        'brand_name',
        'support_email',
        'dealer_auto_onboard',
        'terms_and_conditions',
        'stripe_public_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'evc_account_number',
        'evc_password',
        'paypal_client_id',
        'paypal_secret',
        'whatsapp_business_number',
        'fuel_types',
    ];

    /**
     * Default fuel types used when none have been configured yet.
     */
    public const DEFAULT_FUEL_TYPES = ['Petrol', 'Diesel', 'Electric', 'Hybrid'];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'dealer_auto_onboard' => 'boolean',
            'fuel_types' => 'array',
            // Payment secrets are encrypted at rest.
            'stripe_secret_key' => EncryptedSafe::class,
            'stripe_webhook_secret' => EncryptedSafe::class,
            'evc_password' => EncryptedSafe::class,
            'paypal_secret' => EncryptedSafe::class,
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
                'fuel_types' => static::DEFAULT_FUEL_TYPES,
            ]);
        }

        return static::$instance;
    }

    public static function clearCache(): void
    {
        static::$instance = null;
    }

    /**
     * The masked tail of a secret for display (e.g. "••••••ab12"), or null when
     * unset. Never renders the whole secret to the browser.
     */
    public function maskedSecret(string $attribute): ?string
    {
        $value = $this->{$attribute};

        if (blank($value)) {
            return null;
        }

        return '••••••'.substr($value, -4);
    }

    /**
     * The owner-managed list of fuel types a dealer can pick on the New File
     * Request form. Falls back to the built-in defaults when unset (e.g. before
     * the settings migration has seeded the column).
     *
     * @return array<int, string>
     */
    public static function fuelTypes(): array
    {
        try {
            $types = static::get()->fuel_types;
        } catch (\Throwable $e) {
            return static::DEFAULT_FUEL_TYPES;
        }

        return is_array($types) && count($types) > 0
            ? array_values($types)
            : static::DEFAULT_FUEL_TYPES;
    }

    /**
     * Resolve the brand / product name shown throughout the portal.
     * Falls back to the configured app name, then a generic default,
     * so a freshly white-labelled tenant never leaks "Laravel" or a
     * previous owner's name.
     */
    public function resolveBrandName(): string
    {
        if (filled($this->brand_name)) {
            return $this->brand_name;
        }

        $appName = config('app.name');

        if (filled($appName) && $appName !== 'Laravel') {
            return $appName;
        }

        return 'Dealer Portal';
    }

    /**
     * Resolve the public support email address.
     * Falls back to the configured mail "from" address.
     */
    public function resolveSupportEmail(): ?string
    {
        if (filled($this->support_email)) {
            return $this->support_email;
        }

        return config('mail.from.address');
    }

    /**
     * Resolve the brand colour as a hex string. Emails cannot use CSS
     * variables reliably, so they inline this value directly.
     */
    public function resolveBrandColour(): string
    {
        return filled($this->theme_colour) ? $this->theme_colour : '#e63012';
    }

    /**
     * Static convenience for the resolved brand name, safe to call from
     * Blade layouts before a request-scoped instance exists.
     */
    public static function brandName(): string
    {
        try {
            return (static::first() ?? new static)->resolveBrandName();
        } catch (\Throwable $e) {
            $appName = config('app.name');

            return (filled($appName) && $appName !== 'Laravel') ? $appName : 'Dealer Portal';
        }
    }

    /**
     * Static convenience for the resolved support email.
     */
    public static function supportEmail(): ?string
    {
        try {
            return (static::first() ?? new static)->resolveSupportEmail();
        } catch (\Throwable $e) {
            return config('mail.from.address');
        }
    }

    /**
     * Static convenience for the resolved brand colour, safe to call from
     * email templates before a request-scoped instance exists.
     */
    public static function brandColour(): string
    {
        try {
            return (static::first() ?? new static)->resolveBrandColour();
        } catch (\Throwable $e) {
            return '#e63012';
        }
    }
}
