<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $redirect = in_array($request->user()->role, [UserRole::DealerOwner, UserRole::DealerUser], true)
            ? '/my/dashboard'
            : '/dashboard';

        if ($request->user()->hasVerifiedEmail()) {
            return redirect($redirect.'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect($redirect.'?verified=1');
    }
}
