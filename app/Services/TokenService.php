<?php

namespace App\Services;

use App\Models\ApiAccessToken;
use App\Models\DeviceSession;
use App\Models\User;
use Illuminate\Support\Str;

class TokenService
{
    public const DEFAULT_TTL_DAYS = 30;

    /**
     * Issue a new bearer token. Only the SHA-256 hash is stored server-side.
     *
     * @return array{token: string, token_model: ApiAccessToken}
     */
    public function issue(User $user, string $name = 'android', ?string $deviceId = null, int $ttlDays = self::DEFAULT_TTL_DAYS): array
    {
        $plain = 'lot_'.Str::random(48);

        $model = ApiAccessToken::create([
            'user_id' => $user->id,
            'name' => $name,
            'token_hash' => hash('sha256', $plain),
            'device_id' => $deviceId,
            'abilities' => ['*'],
            'expires_at' => now()->addDays($ttlDays),
        ]);

        if ($deviceId) {
            DeviceSession::updateOrCreate(
                ['user_id' => $user->id, 'device_id' => $deviceId],
                [
                    'platform' => 'android',
                    'app_version' => request()->header('X-App-Version'),
                    'token_identifier' => substr($plain, 0, 12).'…',
                    'last_seen_at' => now(),
                ],
            );
        }

        return ['token' => $plain, 'token_model' => $model];
    }

    public function revoke(ApiAccessToken $token): void
    {
        $token->delete();
    }

    public function revokeAllForUser(User $user, ?int $exceptId = null): void
    {
        $user->apiTokens()->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->delete();
    }
}
