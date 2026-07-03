<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDealerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $dealer = auth()->user()->dealer;

        if (! $dealer || $dealer->status->value === 'suspended') {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect('/login')->withErrors(['email' => 'Your account has been suspended. Please contact us.']);
        }

        if ($dealer->status->value === 'pending') {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect('/login')->withErrors(['email' => 'Your account is pending approval.']);
        }

        return $next($request);
    }
}
