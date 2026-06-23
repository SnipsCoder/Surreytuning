<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtcCode extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'file_request_id',
        'code',
        'description',
    ];

    public function fileRequest(): BelongsTo
    {
        return $this->belongsTo(FileRequest::class);
    }
}
