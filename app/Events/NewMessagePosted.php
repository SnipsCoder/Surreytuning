<?php

namespace App\Events;

use App\Models\FileRequestMessage;
use Illuminate\Foundation\Events\Dispatchable;

class NewMessagePosted
{
    use Dispatchable;

    public function __construct(public FileRequestMessage $message)
    {
    }
}
