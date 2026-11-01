<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $user?->loadMissing('business');

        if (! $user || ! $user->business || ! $user->is_active || ! $user->business->is_active) {
            abort(403, 'An active business account is required.');
        }

        return $next($request);
    }
}
