<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->adminUser = User::query()->create([
            'name' => 'Admin Tester',
            'email' => 'admin.tester@example.com',
            'password' => 'Password!123',
            'primary_role' => 'Admin',
        ]);
        $this->actingAs($this->adminUser);
    }

    public function test_create_user_page_is_accessible(): void
    {
        $this->get('/dashboard/user/create')
            ->assertOk()
            ->assertSee('USER · Create User');
    }

    public function test_user_onboarding_creates_user_and_audit_log(): void
    {
        $response = $this->post('/dashboard/user/create', [
            'full_name' => 'Budi Santoso',
            'email' => 'budi@corp.test',
            'phone' => '+6281234567890',
            'password' => 'Password!123',
            'password_confirmation' => 'Password!123',
            'profile_picture' => UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg'),
            'enable_2fa' => '1',
            'security_questions' => [
                ['question' => 'Nama ibu kandung?', 'answer' => 'Aminah'],
            ],
            'primary_role' => 'Editor',
            'department' => 'Production',
            'assigned_projects' => ['project-satu'],
            'employment_status' => 'full-time',
            'contract_start_at' => now()->toDateString(),
            'contract_end_at' => now()->addMonth()->toDateString(),
            'auto_suspend_on_expiry' => '1',
            'send_welcome_email' => '0',
            'notify_slack_whatsapp' => '0',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'email' => 'budi@corp.test',
            'name' => 'Budi Santoso',
            'primary_role' => 'Editor',
            'department' => 'Production',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.onboarding.created',
            'target_type' => 'user',
        ]);
    }

    public function test_activation_link_activates_user(): void
    {
        $token = str_repeat('a', 64);
        $user = User::query()->create([
            'name' => 'Activation User',
            'email' => 'activate@corp.test',
            'password' => 'password',
            'activation_token_hash' => hash('sha256', $token),
            'activation_token_expires_at' => now()->addHours(24),
        ]);

        $this->get('/activate/'.$user->id.'/'.$token)
            ->assertRedirect('/login');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_be_updated_from_management(): void
    {
        $user = User::query()->create([
            'name' => 'Lama',
            'email' => 'lama@corp.test',
            'password' => 'password',
        ]);

        $this->post('/dashboard/user/'.$user->id.'/update', [
            'full_name' => 'Nama Baru',
            'email' => 'baru@corp.test',
            'phone' => '+6281211111111',
            'primary_role' => 'Admin',
            'department' => 'Operations',
            'employment_status' => 'full-time',
            'contract_start_at' => now()->toDateString(),
            'contract_end_at' => now()->addMonths(3)->toDateString(),
            'auto_suspend_on_expiry' => '1',
            'assigned_projects' => ['project-a'],
        ])->assertRedirect('/dashboard/user');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'baru@corp.test',
            'primary_role' => 'Admin',
        ]);
    }

    public function test_user_can_be_suspended_and_unsuspended(): void
    {
        $user = User::query()->create([
            'name' => 'Suspend Me',
            'email' => 'suspend@corp.test',
            'password' => 'password',
        ]);

        $this->post('/dashboard/user/'.$user->id.'/toggle-suspend')->assertRedirect('/dashboard/user');
        $this->assertNotNull($user->fresh()->suspended_at);

        $this->post('/dashboard/user/'.$user->id.'/toggle-suspend')->assertRedirect('/dashboard/user');
        $this->assertNull($user->fresh()->suspended_at);
    }

    public function test_user_password_can_be_reset_from_management(): void
    {
        Mail::fake();
        $user = User::query()->create([
            'name' => 'Reset Me',
            'email' => 'reset@corp.test',
            'password' => 'password',
        ]);

        $this->post('/dashboard/user/'.$user->id.'/reset-password')
            ->assertRedirect('/dashboard/user');

        $this->assertTrue((bool) $user->fresh()->force_password_change);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.management.password_reset',
            'target_id' => $user->id,
        ]);
    }

    public function test_non_admin_cannot_access_user_management_routes(): void
    {
        $this->post('/dashboard/user/create', [
            'full_name' => 'Forbidden User',
            'email' => 'forbidden@corp.test',
            'phone' => '+6281111111111',
            'password' => 'Password!123',
            'password_confirmation' => 'Password!123',
            'profile_picture' => UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg'),
            'primary_role' => 'Editor',
            'department' => 'Production',
            'employment_status' => 'full-time',
        ])->assertStatus(302);

        $nonAdmin = User::query()->where('email', 'forbidden@corp.test')->firstOrFail();
        $this->actingAs($nonAdmin);

        $this->get('/dashboard/user')->assertStatus(403);
        $this->get('/dashboard/user/create')->assertStatus(403);
        $this->get('/dashboard/user/role')->assertStatus(403);
    }

    public function test_guest_is_redirected_when_accessing_user_management_routes(): void
    {
        Auth::logout();

        $this->get('/dashboard/user')->assertRedirect('/');
        $this->get('/dashboard/user/create')->assertRedirect('/');
        $this->get('/dashboard/user/role')->assertRedirect('/');
    }
}
