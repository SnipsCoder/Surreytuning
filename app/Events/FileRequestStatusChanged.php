<?php

namespace App\Events;

use App\Enums\FileRequestStatus;
use App\Models\FileRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileRequestStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FileRequest $fileRequest,
        public FileRequestStatus $oldStatus,
    ) {}
}
