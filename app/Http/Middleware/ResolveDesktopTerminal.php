<?php

namespace App\Http\Middleware;

use App\Models\PosTerminal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveDesktopTerminal
{
    public function handle(Request $request, Closure $next): Response
    {
        $machineId = $request->input('machine_id');
        $clientId = $request->input('client_id');

        if (! is_string($machineId) || ! is_string($clientId)) {
            return $next($request);
        }

        $terminal = PosTerminal::query()
            ->where('machine_id', $machineId)
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->first();

        if (! $terminal) {
            return $next($request);
        }

        $terminal->forceFill(['last_seen_at' => now()])->save();
        $request->attributes->set('pos_terminal', $terminal);

        return $next($request);
    }
}
