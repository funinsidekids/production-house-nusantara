<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Landing page = halaman login (User / Admin / Superadmin).
     */
    public function landing(): View
    {
        if (Auth::check()) {
            return redirect($this->homeFor(Auth::user()));
        }

        $safeCount = function (string $table, ?string $statusColumn = null, ?string $statusValue = null): int {
            try {
                $q = DB::table($table);
                if ($statusColumn && $statusValue) {
                    $q->where($statusColumn, $statusValue);
                }

                return $q->count();
            } catch (\Throwable) {
                return 0;
            }
        };

        $stats = [
            'subjects' => $safeCount('subjects', 'status', 'active'),
            'materials' => $safeCount('materials', 'status', 'published'),
            'questions' => $safeCount('questions', 'status', 'published'),
            'tryouts' => $safeCount('tryouts', 'status', 'published'),
        ];

        return view('landing', compact('stats'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identity' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        // Rate limit: 5 percobaan per menit per IP+identity.
        $throttleKey = Str::lower('login|'.$request->ip().'|'.$credentials['identity']);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'identity' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        $identity = trim($credentials['identity']);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower($identity)])
            ->orWhereRaw('LOWER(username) = ?', [Str::lower($identity)])
            ->first();

        if (! $user || ! $user->password || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            AuditLogger::log($user?->id, 'auth.login_failed', 'Percobaan login gagal');

            throw ValidationException::withMessages([
                'identity' => 'Email/username atau password salah.',
            ]);
        }

        if (! $user->canLogin()) {
            RateLimiter::hit($throttleKey, 60);
            $msg = match (true) {
                strtolower((string) $user->approval_status) === User::APPROVAL_PENDING => 'Akun Anda masih menunggu persetujuan Superadmin.',
                strtolower((string) $user->approval_status) === User::APPROVAL_REJECTED => 'Pendaftaran akun Anda ditolak. Hubungi Superadmin untuk informasi lebih lanjut.',
                default => 'Akun Anda tidak aktif. Hubungi Superadmin.',
            };
            AuditLogger::log($user->id, 'auth.login_blocked', 'Login diblokir: '.$msg);

            throw ValidationException::withMessages(['identity' => $msg]);
        }

        RateLimiter::clear($throttleKey);

        // Session fixation protection.
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        AuditLogger::log($user->id, 'auth.login', 'Login berhasil sebagai '.$user->normalizedRole());

        return redirect()->intended($this->homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            AuditLogger::log($user->id, 'auth.logout', 'Logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda telah keluar.');
    }

    /**
     * Halaman registrasi manual (butuh persetujuan superadmin).
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'username' => ['required', 'string', 'min:4', 'max:60', 'alpha_dash', Rule::unique('users', 'username')],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => Str::lower($data['username']),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'status' => User::STATUS_ACTIVE,
            'primary_role' => User::ROLE_USER,
            'approval_status' => User::APPROVAL_PENDING,
        ]);

        AuditLogger::log($user->id, 'auth.register', 'Pendaftaran baru menunggu persetujuan');

        return redirect()->route('home')->withErrors([
            'identity' => 'Pendaftaran berhasil. Akun Anda akan aktif setelah disetujui oleh Superadmin.',
        ]);
    }

    private function homeFor(User $user): string
    {
        return match ($user->normalizedRole()) {
            User::ROLE_SUPERADMIN => route('superadmin.dashboard'),
            User::ROLE_ADMIN => route('admin.dashboard'),
            default => route('user.dashboard'),
        };
    }
}
