<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (! Schema::hasColumn('users', 'username')) {
                $t->string('username', 100)->nullable()->unique();
            }
            if (! Schema::hasColumn('users', 'approval_status')) {
                // pending | approved | rejected
                $t->string('approval_status', 20)->default('approved');
            }
            if (! Schema::hasColumn('users', 'approved_by')) {
                $t->foreignId('approved_by')->nullable();
            }
            if (! Schema::hasColumn('users', 'approved_at')) {
                $t->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'rejection_reason')) {
                $t->string('rejection_reason')->nullable();
            }
        });

        // Backfill username dari prefix email agar akun lama tetap bisa login via username.
        DB::table('users')->whereNull('username')->orderBy('id')->each(function ($user) {
            $base = \Illuminate\Support\Str::slug(preg_replace('/@.*$/', '', (string) $user->email) ?: 'user'.$user->id) ?: ('user'.$user->id);
            $candidate = $base;
            $i = 1;
            while (DB::table('users')->where('username', $candidate)->where('id', '!=', $user->id)->exists()) {
                $candidate = $base.++$i;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            foreach (['username', 'approval_status', 'approved_by', 'approved_at', 'rejection_reason'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
