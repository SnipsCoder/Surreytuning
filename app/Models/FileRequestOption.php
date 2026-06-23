<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRequestOption extends Model
{
    protected $fillable = [
        'file_request_id',
        'file_option_id',
        'price_net',
    ];

    protected function casts(): array
    {
        return [
            'price_net' => 'decimal:2',
        ];
    }

    public function fileRequest(): BelongsTo
    {
        return $this->belongsTo(FileRequest::class);
    }

    public function fileOption(): BelongsTo
    {
        return $this->belongsTo(FileOption::class);
    }
}
