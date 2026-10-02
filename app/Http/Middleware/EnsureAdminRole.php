<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            $target = Route::has('login') ? route('login') : '/';

            return redirect()->guest($target);
        }

        $role = strtolower(trim((string) (Auth::user()->primary_role ?? '')));
        if (! in_array($role, ['admin', 'superadmin'], true)) {
            abort(403);
        }

        return $next($request);
    }
}
