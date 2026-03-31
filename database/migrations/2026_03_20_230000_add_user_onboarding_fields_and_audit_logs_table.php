<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('profile_picture_path')->nullable()->after('phone');
            $table->boolean('force_password_change')->default(true)->after('password');
            $table->string('two_factor_secret')->nullable()->after('force_password_change');
            $table->json('security_questions')->nullable()->after('two_factor_secret');
            $table->string('primary_role', 80)->nullable()->after('security_questions');
            $table->string('department', 80)->nullable()->after('primary_role');
            $table->json('assigned_projects')->nullable()->after('department');
            $table->string('employment_status', 40)->nullable()->after('assigned_projects');
            $table->date('contract_start_at')->nullable()->after('employment_status');
            $table->date('contract_end_at')->nullable()->after('contract_start_at');
            $table->boolean('auto_suspend_on_expiry')->default(true)->after('contract_end_at');
            $table->timestamp('suspended_at')->nullable()->after('auto_suspend_on_expiry');
            $table->json('permissions_payload')->nullable()->after('suspended_at');
            $table->foreignId('onboarded_by')->nullable()->after('permissions_payload')->constrained('users')->nullOnDelete();
            $table->string('activation_token_hash')->nullable()->after('onboarded_by');
            $table->timestamp('activation_token_expires_at')->nullable()->after('activation_token_hash');
            $table->timestamp('welcome_email_sent_at')->nullable()->after('activation_token_expires_at');
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->string('target_type', 120)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('onboarded_by');
            $table->dropColumn([
                'phone',
                'profile_picture_path',
                'force_password_change',
                'two_factor_secret',
                'security_questions',
                'primary_role',
                'department',
                'assigned_projects',
                'employment_status',
                'contract_start_at',
                'contract_end_at',
                'auto_suspend_on_expiry',
                'suspended_at',
                'permissions_payload',
                'activation_token_hash',
                'activation_token_expires_at',
                'welcome_email_sent_at',
            ]);
        });
    }
};
