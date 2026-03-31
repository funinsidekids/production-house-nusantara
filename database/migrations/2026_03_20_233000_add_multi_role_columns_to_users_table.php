<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role_slugs')) {
                $table->json('role_slugs')->nullable()->after('primary_role');
            }
            if (! Schema::hasColumn('users', 'role_assignment_contexts')) {
                $table->json('role_assignment_contexts')->nullable()->after('role_slugs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropColumns = [];
            if (Schema::hasColumn('users', 'role_slugs')) {
                $dropColumns[] = 'role_slugs';
            }
            if (Schema::hasColumn('users', 'role_assignment_contexts')) {
                $dropColumns[] = 'role_assignment_contexts';
            }
            if (count($dropColumns) > 0) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
