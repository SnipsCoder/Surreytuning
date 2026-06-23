<?php

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRequestMessage extends Model
{
    protected $fillable = [
        'file_request_id',
        'sender_user_id',
        'type',
        'body',
        'is_internal',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
            'is_internal' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function fileRequest(): BelongsTo
    {
        return $this->belongsTo(FileRequest::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
