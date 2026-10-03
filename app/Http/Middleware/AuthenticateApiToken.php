<?php

namespace App\Http\Middleware;

use App\Models\ApiAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if (! $plain) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $token = ApiAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $plain))
            ->first();

        if (! $token || $token->isExpired() || ! $token->user || ! $token->user->isActiveAccount()) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid atau kedaluwarsa.'], 401);
        }

        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        // expose token model for controllers (logout / device tracking)
        $request->attributes->set('api_token', $token);
        auth()->setUser($token->user);

        return $next($request);
    }
}
