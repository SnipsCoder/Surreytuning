<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $type,
        public readonly ?string $paymentIntentId,
        public readonly array $context = [],
    ) {
    }
}
