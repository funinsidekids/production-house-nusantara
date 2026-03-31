<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->text('caption')->nullable()->after('title');
            $table->string('cta_text')->nullable()->after('video_url');
            $table->string('cta_url')->nullable()->after('cta_text');
            $table->unsignedSmallInteger('duration_seconds')->default(7)->after('sort_order');
            $table->decimal('overlay_opacity', 3, 2)->default(0.78)->after('duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn(['caption', 'cta_text', 'cta_url', 'duration_seconds', 'overlay_opacity']);
        });
    }
};
