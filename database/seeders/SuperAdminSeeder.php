<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('SUPERADMIN_EMAIL', 'Steveandriebagus@gmail.com');
        $username = (string) env('SUPERADMIN_USERNAME', 'Makutharama');
        $password = (string) env('SUPERADMIN_PASSWORD', 'Mestakung@085');

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->fill([
            'name' => $user->name ?: 'Superadmin',
            'username' => $user->username ?: $username,
            'password' => Hash::make($password),
            'primary_role' => 'Superadmin',
            'role_slugs' => ['superadmin'],
            'status' => User::STATUS_ACTIVE,
            'approval_status' => User::APPROVAL_APPROVED,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ]);
        $user->save();

        // Pastikan akun lain tidak ada yang menyandang role superadmin.
        DB::table('users')->where('id', '!=', $user->id)
            ->whereRaw("LOWER(COALESCE(primary_role,'')) = 'superadmin'")
            ->update(['primary_role' => 'User']);
    }
}
