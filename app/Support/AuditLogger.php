<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Record an important user/admin action. Never store secrets here.
     */
    public static function log(?User $user, string $action, ?string $targetType = null, int|string|null $targetId = null, array $meta = []): void
    {
        try {
            DB::table('audit_logs')->insert([
                'actor_user_id' => $user?->id,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'metadata' => json_encode(array_merge(
                    ['ip' => request()?->ip()],
                    $meta,
                )),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('audit_log_write_failed', ['action' => $action, 'error' => $e->getMessage()]);
        }
    }
}
