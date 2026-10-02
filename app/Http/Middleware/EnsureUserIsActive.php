<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan akun yang login masih berstatus aktif & disetujui.
 * Akun pending/rejected/suspended otomatis di-logout dengan pesan yang jelas.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->canLogin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $target = Route::has('login') ? route('login') : '/';

            return redirect($target)->withErrors([
                'email' => 'Akun Anda belum disetujui atau tidak aktif. Silakan hubungi Superadmin.',
            ]);
        }

        return $next($request);
    }
}
