<?php

namespace App\Events;

use App\Models\FileRequest;
use Illuminate\Foundation\Events\Dispatchable;

class FileRequestSubmitted
{
    use Dispatchable;

    public function __construct(public FileRequest $fileRequest)
    {
    }
}
