<?php

namespace Tests\Feature;

use App\Models\LandingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardSubmenuIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public static function pagesProvider(): array
    {
        return [
            ['/dashboard', 'Production House Nusantara Dashboard'],
            ['/dashboard/website-cms/general', 'WEBSITE CMS · General'],
            ['/dashboard/website-cms/logo', 'WEBSITE CMS · Logo'],
            ['/dashboard/website-cms/header', 'WEBSITE CMS · Header'],
            ['/dashboard/website-cms/slider-video', 'WEBSITE CMS · Slider Video'],
            ['/dashboard/website-cms/pages', 'WEBSITE CMS · Pages'],
            ['/dashboard/website-cms/portfolio', 'WEBSITE CMS · Portfolio'],
            ['/dashboard/website-cms/blog-news', 'WEBSITE CMS · Blog / News'],
            ['/dashboard/store/product', 'STORE · Product'],
            ['/dashboard/store/orders', 'STORE · Orders'],
            ['/dashboard/user', 'USER · Management'],
            ['/dashboard/user/create', 'USER · Create User'],
            ['/dashboard/user/role', 'USER · Role'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_test_seed'],
            ['value' => 'ready']
        );
        $admin = User::query()->create([
            'name' => 'Admin Tester',
            'email' => 'admin.tester@example.com',
            'password' => 'Password!123',
            'primary_role' => 'Admin',
        ]);
        $this->actingAs($admin);
    }

    #[DataProvider('pagesProvider')]
    public function test_dashboard_pages_contain_expected_business_markers(string $uri, string $marker): void
    {
        $response = $this->get($uri);
        $response->assertOk();
        $response->assertSee($marker);
    }

    public function test_dashboard_analytics_has_queue_monitor_components(): void
    {
        $response = $this->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Queue Conversion Monitor');
        $response->assertSee('Uploaded Queue');
        $response->assertSee('Processing Queue');
        $response->assertSee('Failed Queue');
    }
}
