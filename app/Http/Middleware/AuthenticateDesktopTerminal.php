<?php

namespace App\Http\Middleware;

use App\Models\PosTerminal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDesktopTerminal
{
    public function handle(Request $request, Closure $next): Response
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'string', 'max:100'],
            'client_id' => ['required', 'string', 'max:100'],
        ]);

        $terminal = PosTerminal::query()
            ->where('machine_id', $validated['machine_id'])
            ->where('is_active', true)
            ->first();

        if (! $terminal || ! $terminal->api_token_hash || ! $request->bearerToken()) {
            return response()->json(['message' => 'A registered terminal and bearer token are required.'], 401);
        }

        if (
            ! hash_equals((string) $terminal->client_id, $validated['client_id'])
            || ! Hash::check($request->bearerToken(), $terminal->api_token_hash)
        ) {
            return response()->json(['message' => 'Terminal credentials are invalid.'], 401);
        }

        $request->attributes->set('pos_terminal', $terminal);

        return $next($request);
    }
}
