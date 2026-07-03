<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedStripeEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'event_id',
        'type',
    ];
}
