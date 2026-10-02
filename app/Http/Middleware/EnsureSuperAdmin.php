<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(Route::has('login') ? route('login') : '/');
        }

        if (! $user->isSuperAdmin()) {
            abort(403, 'Akses khusus Superadmin.');
        }

        return $next($request);
    }
}
