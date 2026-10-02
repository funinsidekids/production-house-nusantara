<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            if (! Schema::hasColumn('users', 'status')) {
                $t->string('status', 30)->default('active'); // active|suspended|deleted
            }
            if (! Schema::hasColumn('users', 'phone_verified_at')) {
                $t->timestamp('phone_verified_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $t->timestamp('last_login_at')->nullable();
            }
        });

        // password nullable (aplikasi awal tanpa password)
        if (Schema::hasColumn('users', 'password')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('password')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['status', 'phone_verified_at', 'last_login_at']);
        });
    }
};
