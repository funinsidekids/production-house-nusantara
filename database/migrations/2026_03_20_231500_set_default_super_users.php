<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultUsers = [
            [
                'name' => 'Superuser',
                'email' => 'Funinsidekids@gmail.com',
            ],
            [
                'name' => 'Bagus Tri Cahyo Rahino',
                'email' => 'steveandriebagus@gmail.com',
            ],
        ];

        $hasRoleColumns = Schema::hasColumn('users', 'primary_role')
            && Schema::hasColumn('users', 'permissions_payload')
            && Schema::hasColumn('users', 'department')
            && Schema::hasColumn('users', 'employment_status')
            && Schema::hasColumn('users', 'auto_suspend_on_expiry')
            && Schema::hasColumn('users', 'suspended_at');

        foreach ($defaultUsers as $defaultUser) {
            $now = now();
            $existingUser = DB::table('users')->where('email', $defaultUser['email'])->first();
            $payload = [
                'name' => $defaultUser['name'],
                'email_verified_at' => $now,
                'updated_at' => $now,
            ];
            if ($existingUser === null) {
                $payload['password'] = Hash::make((string) env('DEFAULT_SUPERUSER_PASSWORD', 'ChangeMeNow!123'));
                $payload['created_at'] = $now;
            }
            if ($hasRoleColumns) {
                $payload['primary_role'] = 'Admin';
                $payload['permissions_payload'] = json_encode(['*'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $payload['department'] = 'Operations';
                $payload['employment_status'] = 'full-time';
                $payload['auto_suspend_on_expiry'] = false;
                $payload['suspended_at'] = null;
            }

            DB::table('users')->updateOrInsert(
                ['email' => $defaultUser['email']],
                $payload
            );
        }
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', ['Funinsidekids@gmail.com', 'steveandriebagus@gmail.com'])
            ->delete();
    }
};
