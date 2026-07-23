<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsOwnerUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        if (! in_array($request->user()->role, [UserRole::Owner, UserRole::Technician, UserRole::Tuner], true)) {
            abort(403);
        }

        return $next($request);
    }
}
