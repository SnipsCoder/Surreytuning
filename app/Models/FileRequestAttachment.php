<?php

namespace App\Models;

use App\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRequestAttachment extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'file_request_id',
        'message_id',
        'uploader_user_id',
        'attachment_type',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size_bytes',
        'mime_type',
        'first_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'attachment_type' => AttachmentType::class,
            'first_downloaded_at' => 'datetime',
        ];
    }

    public function fileRequest(): BelongsTo
    {
        return $this->belongsTo(FileRequest::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(FileRequestMessage::class, 'message_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_user_id');
    }
}
