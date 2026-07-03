<?php

namespace App\Events;

use App\Models\DealerApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealerApplicationRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly DealerApplication $application) {}
}
