<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaults = [
            ['name' => 'Superuser', 'email' => 'Funinsidekids@gmail.com'],
            ['name' => 'Bagus Tri Cahyo Rahino', 'email' => 'steveandriebagus@gmail.com'],
        ];

        foreach ($defaults as $defaultUser) {
            $payload = [
                'name' => $defaultUser['name'],
                'password' => Hash::make((string) env('DEFAULT_SUPERUSER_PASSWORD', 'ChangeMeNow!123')),
                'email_verified_at' => now(),
            ];
            if (Schema::hasColumn('users', 'primary_role')) {
                $payload['primary_role'] = 'Admin';
            }
            if (Schema::hasColumn('users', 'permissions_payload')) {
                $payload['permissions_payload'] = ['*'];
            }
            if (Schema::hasColumn('users', 'department')) {
                $payload['department'] = 'Operations';
            }
            if (Schema::hasColumn('users', 'employment_status')) {
                $payload['employment_status'] = 'full-time';
            }
            if (Schema::hasColumn('users', 'auto_suspend_on_expiry')) {
                $payload['auto_suspend_on_expiry'] = false;
            }
            if (Schema::hasColumn('users', 'suspended_at')) {
                $payload['suspended_at'] = null;
            }

            User::query()->updateOrCreate(
                ['email' => $defaultUser['email']],
                $payload
            );
        }
    }
}
