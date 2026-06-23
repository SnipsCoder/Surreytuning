<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;

class DealerApplication extends Model
{
    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'country',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }
}
