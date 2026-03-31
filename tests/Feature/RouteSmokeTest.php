<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public static function getRoutesProvider(): array
    {
        return [
            ['GET', '/'],
            ['GET', '/about'],
            ['GET', '/services'],
            ['GET', '/faq'],
            ['GET', '/blog'],
            ['GET', '/privacy-policy'],
            ['GET', '/terms'],
            ['GET', '/dashboard'],
            ['GET', '/dashboard/website-cms/general'],
            ['GET', '/dashboard/website-cms/logo'],
            ['GET', '/dashboard/website-cms/header'],
            ['GET', '/dashboard/website-cms/slider-video'],
            ['GET', '/dashboard/website-cms/pages'],
            ['GET', '/dashboard/website-cms/portfolio'],
            ['GET', '/dashboard/website-cms/blog-news'],
            ['GET', '/api/admin/dashboard'],
            ['GET', '/api/landing/content'],
            ['GET', '/sitemap-blog.xml'],
        ];
    }

    public static function postRoutesProvider(): array
    {
        return [
            ['/contact', ['name' => 'Tester', 'email' => 'tester@example.com', 'message' => 'Smoke test']],
            ['/dashboard/website-cms/general', []],
            ['/dashboard/website-cms/logo', []],
            ['/dashboard/website-cms/header', []],
            ['/dashboard/website-cms/slider-video', []],
            ['/dashboard/website-cms/pages', []],
            ['/dashboard/website-cms/portfolio', []],
            ['/dashboard/website-cms/blog-news', []],
            ['/dashboard/media/videos/upload', []],
            ['/dashboard/media/videos/999999/retry', []],
        ];
    }

    #[DataProvider('getRoutesProvider')]
    public function test_get_routes_do_not_return_server_error(string $method, string $uri): void
    {
        $response = $this->call($method, $uri);
        $status = $response->getStatusCode();

        $this->assertFalse(
            $status >= 500,
            "Expected no server error for [{$method}] {$uri}, got {$status}."
        );
    }

    #[DataProvider('postRoutesProvider')]
    public function test_post_routes_do_not_return_server_error(string $uri, array $payload): void
    {
        $response = $this->post($uri, $payload);
        $status = $response->getStatusCode();

        $this->assertFalse(
            $status >= 500,
            "Expected no server error for [POST] {$uri}, got {$status}."
        );
    }
}
