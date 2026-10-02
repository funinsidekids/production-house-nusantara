<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    /**
     * Tulis catatan audit ke tabel audit_logs.
     * Tidak pernah melempar exception ke flow utama & tidak menyimpan data sensitif.
     */
    public static function log(?int $userId, string $action, string $description, array $meta = []): void
    {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }

            // Buang key sensitif dari meta (password, token, api key, otp, secret).
            $meta = array_filter(
                $meta,
                fn ($v, $k) => ! preg_match('/password|token|secret|api_key|apikey|otp/i', (string) $k),
                ARRAY_FILTER_USE_BOTH
            );

            $columns = self::columns();
            $payload = ['created_at' => now(), 'updated_at' => now()];

            if (in_array('actor_user_id', $columns, true)) {
                $payload['actor_user_id'] = $userId;
            }
            if (in_array('user_id', $columns, true)) {
                $payload['user_id'] = $userId;
            }
            if (in_array('action', $columns, true)) {
                $payload['action'] = mb_substr($action, 0, 120);
            }
            if (in_array('event', $columns, true)) {
                $payload['event'] = mb_substr($action, 0, 120);
            }
            if (in_array('description', $columns, true)) {
                $payload['description'] = mb_substr($description, 0, 490);
            }
            if (in_array('metadata', $columns, true)) {
                $payload['metadata'] = json_encode([
                    'message' => mb_substr($description, 0, 490),
                    'ip' => request()->ip(),
                    'data' => $meta,
                ], JSON_UNESCAPED_UNICODE);
            }

            DB::table('audit_logs')->insert($payload);
        } catch (\Throwable) {
            // Kegagalan audit tidak boleh merusak aksi utama.
        }
    }

    /** @return array<int,string> */
    private static function columns(): array
    {
        static $cache = null;

        if ($cache === null) {
            try {
                $cache = Schema::getColumnListing('audit_logs');
            } catch (\Throwable) {
                $cache = [];
            }
        }

        return $cache;
    }
}
