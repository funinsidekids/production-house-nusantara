<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard instalasi:
 *  - Jika aplikasi belum diinstal (storage/app/installed.json tidak ada),
 *    semua request diarahkan ke halaman installer.
 *  - Jika koneksi database gagal (mis. .env terhapus / kredensial salah,
 *    sehingga Laravel jatuh ke default root@127.0.0.1 tanpa password),
 *    tampilkan halaman perbaikan yang ramah — BUKAN stack trace 500.
 */
class CheckInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install') || $request->is('install/*') || $request->is('up')) {
            return $next($request);
        }

        if (!is_file(storage_path('app/installed.json'))) {
            return redirect('/install');
        }

        try {
            \DB::connection()->getPdo();
        } catch (QueryException $e) {
            return response()->view('errors.db-connection', [], 503)
                ->header('Retry-After', '60');
        }

        return $next($request);
    }
}
