<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\ApiResponse;
use App\Services\TokenService;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TokenService $tokens) {}

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'phone' => preg_replace('/\s|-/', '', $data['phone']),
                'password' => ! empty($data['password']) ? $data['password'] : null,
                'status' => User::STATUS_ACTIVE,
            ]);

            $schoolId = $data['school_id'] ?? null;

            if (! $schoolId && ! empty($data['school_name'])) {
                $school = School::firstOrCreate(
                    ['name' => $data['school_name']],
                    ['type' => 'MA'],
                );
                $schoolId = $school->id;
            }

            StudentProfile::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'grade' => $data['grade'] ?? 'XII',
                'class_name' => $data['class_name'] ?? null,
                'phone' => $user->phone,
            ]);

            return $user;
        });

        AuditLogger::log($user, 'auth.register');

        ['token' => $plain] = $this->tokens->issue($user, deviceId: $data['device_id'] ?? null);

        return $this->created([
            'token' => $plain,
            'token_type' => 'Bearer',
            'expires_in' => now()->addDays(TokenService::DEFAULT_TTL_DAYS)->toIso8601String(),
            'user' => $this->userPayload($user),
        ], 'Registrasi berhasil.');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $phone = preg_replace('/\s|-/', '', $data['phone']);

        $user = User::where('phone', $phone)->first();

        if (! $user || ! $user->isActiveAccount()) {
            return $this->fail('Akun tidak ditemukan atau dinonaktifkan.', 401);
        }

        // If a password was ever set, it must match. Passwordless accounts may log in by phone only.
        if ($user->password !== null) {
            if (empty($data['password']) || ! Hash::check($data['password'], $user->password)) {
                return $this->fail('Kredensial tidak valid.', 401);
            }
        }

        $user->forceFill(['last_login_at' => now()])->save();
        AuditLogger::log($user, 'auth.login');

        ['token' => $plain] = $this->tokens->issue($user, deviceId: $data['device_id'] ?? null);

        return $this->ok([
            'token' => $plain,
            'token_type' => 'Bearer',
            'expires_in' => now()->addDays(TokenService::DEFAULT_TTL_DAYS)->toIso8601String(),
            'user' => $this->userPayload($user),
        ], 'Login berhasil.');
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        $token = $request->attributes->get('api_token');
        if ($token) {
            $this->tokens->revoke($token);
            AuditLogger::log($request->user(), 'auth.logout');
        }

        return $this->ok([], 'Logout berhasil.');
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing('studentProfile.school');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'status' => $user->status,
            'is_admin' => $user->isAdmin(),
            'profile' => $user->studentProfile ? [
                'school_id' => $user->studentProfile->school_id,
                'school_name' => $user->studentProfile->school?->name,
                'grade' => $user->studentProfile->grade,
                'class_name' => $user->studentProfile->class_name,
            ] : null,
        ];
    }
}
