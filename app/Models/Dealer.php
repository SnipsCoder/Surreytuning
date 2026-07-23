<?php

namespace App\Models;

use App\Enums\DealerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'country',
        'invoice_address',
        'file_credit_balance',
        'evc_credit_balance',
        'evc_number',
        'discount_percentage',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'terms_accepted_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'file_credit_balance' => 'decimal:2',
            'evc_credit_balance' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'status' => DealerStatus::class,
            'approved_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function fileRequests(): HasMany
    {
        return $this->hasMany(FileRequest::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function fileCreditTransactions(): HasMany
    {
        return $this->hasMany(FileCreditTransaction::class);
    }

    public function evcCreditTransactions(): HasMany
    {
        return $this->hasMany(EvcCreditTransaction::class);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(User::class)->where('is_primary_contact', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', DealerStatus::Approved);
    }

    /**
     * Apply this dealer's discount to a net price.
     *
     * The discount reduces the money the dealer pays; it never changes the
     * quantity of credits or goods they receive. Returns the discounted net,
     * rounded to 2 decimal places.
     */
    public function discountedPrice(float $net): float
    {
        $discount = (float) $this->discount_percentage;

        if ($discount <= 0) {
            return round($net, 2);
        }

        $discount = min($discount, 100);

        return round($net * (1 - $discount / 100), 2);
    }
}
