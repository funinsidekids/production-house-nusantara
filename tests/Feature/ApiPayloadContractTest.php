<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use App\Models\LandingSetting;
use App\Models\VideoAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPayloadContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_payload_contract(): void
    {
        HeroSlide::query()->create([
            'title' => 'Slide A',
            'caption' => 'Caption A',
            'video_url' => 'cms/slider-video/original/a.mp4',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        HeroSlide::query()->create([
            'title' => 'Slide B',
            'caption' => 'Caption B',
            'video_url' => 'cms/slider-video/original/b.mp4',
            'is_active' => false,
            'sort_order' => 2,
        ]);
        VideoAsset::query()->create([
            'title' => 'Asset A',
            'source_path' => 'cms/videos/original/a.mp4',
            'status' => 'ready',
            'duration_seconds' => 65,
            'meta' => [],
        ]);
        VideoAsset::query()->create([
            'title' => 'Asset B',
            'source_path' => 'cms/videos/original/b.mp4',
            'status' => 'processing',
            'duration_seconds' => 22,
            'meta' => [],
        ]);

        $response = $this->getJson('/api/admin/dashboard');
        $response->assertOk();
        $response->assertJsonStructure([
            'kpi' => ['totalSlides', 'totalAssets', 'activeSlides', 'readyAssets'],
            'kpiTrends' => ['totalSlides', 'totalAssets', 'activeSlides', 'readyAssets'],
            'revenueSeries' => [['month', 'slides', 'assets', 'ready']],
            'activities' => [['message', 'time']],
        ]);
        $response->assertJsonPath('kpi.totalSlides', 2);
        $response->assertJsonPath('kpi.totalAssets', 2);
        $response->assertJsonPath('kpi.activeSlides', 1);
        $response->assertJsonPath('kpi.readyAssets', 1);

        $series = $response->json('revenueSeries');
        $this->assertIsArray($series);
        $this->assertCount(12, $series);
        foreach ($series as $point) {
            $this->assertIsString($point['month']);
            $this->assertIsInt($point['slides']);
            $this->assertIsInt($point['assets']);
            $this->assertIsInt($point['ready']);
        }
    }

    public function test_landing_content_payload_contract_and_normalization(): void
    {
        HeroSlide::query()->create([
            'title' => 'Hero Relative',
            'caption' => 'Hero caption',
            'video_url' => 'cms/slider-video/original/hero-relative.mp4',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_portfolio_payload'],
            ['value' => json_encode([
                'layout' => ['portfolio_title' => 'Custom Portfolio'],
                'projects' => [
                    [
                        'title' => 'Project X',
                        'category' => 'Commercial',
                        'video_embed' => '',
                        'featured_image' => 'cms/portfolio/featured/x.jpg',
                        'gallery_images' => ['cms/portfolio/gallery/x-1.jpg'],
                        'description' => 'Desc',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        $response = $this->getJson('/api/landing/content');
        $response->assertOk();
        $response->assertHeader('X-API-Version', '2026-03-enterprise-v2');
        $response->assertHeader('X-Gateway-Ready', 'true');
        $response->assertJsonStructure([
            'heroSlides',
            'landingContent',
            'sliderConfig',
            'landingSections' => ['portfolio_items', 'why_items', 'team_members'],
            'portfolioCategories',
            'modular' => ['hero', 'services', 'portfolio', 'team', 'workflow', 'contact', 'footer'],
            'meta' => ['apiVersion', 'gateway'],
        ]);
        $heroSlides = $response->json('heroSlides');
        $this->assertNotEmpty($heroSlides);
        $this->assertArrayNotHasKey('title', $heroSlides[0]);
        $this->assertArrayNotHasKey('caption', $heroSlides[0]);

        $modularSlides = (array) $response->json('modular.hero.slides');
        $this->assertNotEmpty($modularSlides);
        $this->assertArrayNotHasKey('title', $modularSlides[0]);
        $this->assertArrayNotHasKey('caption', $modularSlides[0]);
        $this->assertArrayHasKey('videoUrl', $modularSlides[0]);

        $portfolioItems = $response->json('landingSections.portfolio_items');
        $this->assertNotEmpty($portfolioItems);
        $firstMediaUrl = (string) ($portfolioItems[0]['media_url'] ?? '');
        $this->assertStringStartsWith('/storage/', $firstMediaUrl);
        $this->assertSame('Custom Portfolio', $response->json('landingSections.portfolio_title'));
    }

    public function test_admin_module_payload_and_data_integrity_crud(): void
    {
        $show = $this->getJson('/api/admin/module/media/video-library');
        $show->assertOk();
        $show->assertJsonStructure([
            'section',
            'item',
            'title',
            'columns',
            'formFields',
            'sectionDescription',
            'itemDescription',
            'filters' => ['search', 'status'],
            'sorting' => ['sortBy', 'sortDir', 'options'],
            'pagination' => ['page', 'perPage', 'total', 'lastPage'],
            'searchKeys',
            'statusOptions',
            'rows',
        ]);

        $store = $this->postJson('/api/admin/module/media/video-library', [
            'title' => 'Custom Media Entry',
            'status' => 'active',
            'owner' => 'QA Team',
            'note' => 'Initial note',
        ]);
        $store->assertCreated();
        $store->assertJsonPath('message', 'Entry berhasil disimpan');
        $entryId = (string) $store->json('entry.id');
        $this->assertStringStartsWith('custom-', $entryId);

        $update = $this->putJson("/api/admin/module/media/video-library/{$entryId}", [
            'title' => 'Custom Media Entry Updated',
            'status' => 'approved',
            'owner' => 'QA Team 2',
            'note' => 'Updated note',
        ]);
        $update->assertOk();
        $update->assertJsonPath('message', 'Entry berhasil diupdate');

        $search = $this->getJson('/api/admin/module/media/video-library?search=Updated');
        $search->assertOk();
        $rows = $search->json('rows');
        $this->assertNotEmpty($rows);
        $this->assertTrue(collect($rows)->contains(fn (array $row): bool => ($row['id'] ?? '') === $entryId));

        $delete = $this->deleteJson("/api/admin/module/media/video-library/{$entryId}");
        $delete->assertOk();
        $delete->assertJsonPath('message', 'Entry berhasil dihapus');

        $notFoundDelete = $this->deleteJson("/api/admin/module/media/video-library/{$entryId}");
        $notFoundDelete->assertNotFound();
    }
}
