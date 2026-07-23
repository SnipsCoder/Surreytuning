<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Global 2FA kill-switch (reversible via TWO_FACTOR_ENABLED env flag)
        if (! config('auth.two_factor_enabled', true)) {
            return $next($request);
        }

        // Never intercept 2FA routes themselves (avoids redirect loops)
        if ($request->routeIs('two-factor.*', 'logout', 'password.*')) {
            return $next($request);
        }

        $isOwnerTeam = in_array($user->role, [UserRole::Owner, UserRole::Technician, UserRole::Tuner]);

        // Owner/technician: 2FA is mandatory — force setup if not confirmed
        if ($isOwnerTeam && ! $user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.setup')
                ->with('info', 'You must set up two-factor authentication before accessing the portal.');
        }

        // Any user with 2FA confirmed: require challenge verification this session
        if ($user->two_factor_confirmed_at && ! session('two_factor_verified')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
