<?php

namespace App\Events;

use App\Models\FileRequest;
use Illuminate\Foundation\Events\Dispatchable;

class FileRequestStatusChanged
{
    use Dispatchable;

    public function __construct(public FileRequest $fileRequest)
    {
    }
}
