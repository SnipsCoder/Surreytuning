<?php

namespace App\Events;

use App\Models\FileRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileRequestSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public FileRequest $fileRequest) {}
}
