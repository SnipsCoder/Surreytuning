<?php

namespace App\Events;

use App\Models\FileRequestMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessagePosted
{
    use Dispatchable, SerializesModels;

    public function __construct(public FileRequestMessage $message) {}
}
