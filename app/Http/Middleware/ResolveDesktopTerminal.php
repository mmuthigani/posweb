<?php

namespace App\Http\Middleware;

use App\Models\PosTerminal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ResolveDesktopTerminal
{
    public function handle(Request $request, Closure $next): Response
    {
        $machineId = $request->input('machine_id');
        $clientId = $request->input('client_id');
        $bearerToken = $request->bearerToken();

        $terminal = PosTerminal::query()
            ->where('machine_id', $machineId)
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->first();

        if (
            ! $terminal
            || ($bearerToken && (
                ! $terminal->api_token_hash
                || ! Hash::check($bearerToken, $terminal->api_token_hash)
            ))
        ) {
            if ($bearerToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terminal credentials are invalid or the terminal is not registered.',
                ], 401);
            }

            return $next($request);
        }

        $terminal->forceFill(['last_seen_at' => now()])->save();
        $request->attributes->set('pos_terminal', $terminal);

        return $next($request);
    }
}
