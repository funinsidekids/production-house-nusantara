<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_assets', function (Blueprint $table) {
            $table->string('webm_path')->nullable()->after('source_path');
            $table->string('mp4_path')->nullable()->after('webm_path');
            $table->string('thumb_path')->nullable()->after('mp4_path');
            $table->string('error_message')->nullable()->after('status');
            $table->timestamp('converted_at')->nullable()->after('duration_seconds');
            $table->string('context')->nullable()->after('title');
            $table->index(['status', 'created_at']);
            $table->index(['context', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('video_assets', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['context', 'created_at']);
            $table->dropColumn([
                'webm_path',
                'mp4_path',
                'thumb_path',
                'error_message',
                'converted_at',
                'context',
            ]);
        });
    }
};
