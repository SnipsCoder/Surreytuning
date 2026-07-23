<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FileRequest;
use App\Models\User;

class FileRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Owner, UserRole::Tuner], true);
    }

    public function view(User $user, FileRequest $fileRequest): bool
    {
        if (in_array($user->role, [UserRole::Owner, UserRole::Tuner], true)) {
            return true;
        }

        return $user->dealer_id === $fileRequest->dealer_id;
    }

    public function respond(User $user, FileRequest $fileRequest): bool
    {
        return in_array($user->role, [UserRole::Owner, UserRole::Tuner], true);
    }

    public function addCharge(User $user, FileRequest $fileRequest): bool
    {
        return in_array($user->role, [UserRole::Owner, UserRole::Tuner], true);
    }

    public function addCredit(User $user, FileRequest $fileRequest): bool
    {
        return $user->role === UserRole::Owner;
    }

    public function void(User $user, FileRequest $fileRequest): bool
    {
        return $user->role === UserRole::Owner;
    }

    public function viewInternalNotes(User $user): bool
    {
        return in_array($user->role, [UserRole::Owner, UserRole::Tuner], true);
    }
}
