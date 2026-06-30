<?php

namespace App\Events;

use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealerApplicationApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly DealerApplication $application,
        public readonly Dealer $dealer,
        public readonly User $user,
    ) {
    }
}
