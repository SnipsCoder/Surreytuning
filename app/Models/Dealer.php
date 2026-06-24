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
        'slave_credit_balance',
        'evc_credit_balance',
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
            'slave_credit_balance' => 'decimal:2',
            'evc_credit_balance' => 'decimal:2',
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

    public function slaveCreditTransactions(): HasMany
    {
        return $this->hasMany(SlaveCreditTransaction::class);
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
}
