<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === UserRole::Owner) {
            return true;
        }

        return $user->dealer_id === $invoice->dealer_id;
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Owner;
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Owner;
    }
}
