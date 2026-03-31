<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteCmsPagesController extends Controller
{
    private const MAX_REVISIONS = 10;

    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key')->all();
        $payload = $this->decodePayload((string) ($settings['cms_pages_payload'] ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) ($settings['cms_pages_revisions'] ?? '')));
        $sectionVisibility = $this->sectionVisibilityMap($payload['section_visibility'] ?? null);

        return view('content.dashboard.website-cms-pages', [
            'form' => [
                'editor_mode' => (string) ($payload['editor_mode'] ?? 'wysiwyg'),
                'wysiwyg_engine' => (string) ($payload['wysiwyg_engine'] ?? 'tinymce'),
                'homepage_template' => (string) ($payload['homepage']['template'] ?? 'full-width'),
                'homepage_modules_json' => $this->prettyJson($payload['homepage']['modules'] ?? $this->defaultHomepageModules()),
                'section_services_enabled' => $sectionVisibility['services'],
                'section_portfolio_enabled' => $sectionVisibility['portfolio'],
                'section_why_enabled' => $sectionVisibility['why_us'],
                'section_testimonials_enabled' => $sectionVisibility['testimonials'],
                'section_process_enabled' => $sectionVisibility['process'],
                'section_team_enabled' => $sectionVisibility['team'],
                'section_contact_enabled' => $sectionVisibility['contact'],
                'about_story_timeline_json' => $this->prettyJson($payload['about']['story_timeline'] ?? $this->defaultAboutTimeline()),
                'about_vision' => (string) ($payload['about']['vision'] ?? ''),
                'about_mission' => (string) ($payload['about']['mission'] ?? ''),
                'about_awards_json' => $this->prettyJson($payload['about']['awards'] ?? $this->defaultAwards()),
                'about_clients_json' => $this->prettyJson($payload['about']['clients'] ?? $this->defaultClients()),
                'services_categories_json' => $this->prettyJson($payload['services']['categories'] ?? $this->defaultServiceCategories()),
                'process_pipeline_json' => $this->prettyJson($payload['process']['pipeline'] ?? $this->defaultPipeline()),
                'process_expectations_json' => $this->prettyJson($payload['process']['expectations'] ?? $this->defaultExpectations()),
                'faq_categories_json' => $this->prettyJson($payload['faq']['categories'] ?? $this->defaultFaqCategories()),
                'faq_items_json' => $this->prettyJson($payload['faq']['items'] ?? $this->defaultFaqItems()),
                'privacy_content_html' => (string) ($payload['legal']['privacy_html'] ?? ''),
                'terms_content_html' => (string) ($payload['legal']['terms_html'] ?? ''),
                'legal_version_history_json' => $this->prettyJson($payload['legal']['version_history'] ?? $this->defaultLegalHistory()),
                'custom_pages_json' => $this->prettyJson($payload['custom_pages'] ?? []),
                'custom_page_layout' => (string) ($payload['custom_page_layout'] ?? 'full-width'),
                'custom_css' => (string) ($payload['custom_css'] ?? ''),
                'block_editor_data_json' => $this->prettyJson($payload['block_editor_data'] ?? []),
                'publish_at' => (string) ($payload['publish_at'] ?? ''),
                'password_protection_enabled' => (bool) ($payload['password_protection_enabled'] ?? false),
                'preview_password' => (string) ($payload['preview_password'] ?? ''),
            ],
            'revisions' => $revisions,
            'revisionCount' => count($revisions),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'editor_mode' => ['required', 'in:wysiwyg,block'],
            'wysiwyg_engine' => ['required', 'in:tinymce,ckeditor,plain,directus'],
            'homepage_template' => ['required', 'in:full-width,boxed'],
            'homepage_modules_json' => ['nullable', 'string'],
            'section_services_enabled' => ['nullable', 'boolean'],
            'section_portfolio_enabled' => ['nullable', 'boolean'],
            'section_why_enabled' => ['nullable', 'boolean'],
            'section_testimonials_enabled' => ['nullable', 'boolean'],
            'section_process_enabled' => ['nullable', 'boolean'],
            'section_team_enabled' => ['nullable', 'boolean'],
            'section_contact_enabled' => ['nullable', 'boolean'],
            'about_story_timeline_json' => ['nullable', 'string'],
            'about_vision' => ['nullable', 'string'],
            'about_mission' => ['nullable', 'string'],
            'about_awards_json' => ['nullable', 'string'],
            'about_clients_json' => ['nullable', 'string'],
            'services_categories_json' => ['nullable', 'string'],
            'process_pipeline_json' => ['nullable', 'string'],
            'process_expectations_json' => ['nullable', 'string'],
            'faq_categories_json' => ['nullable', 'string'],
            'faq_items_json' => ['nullable', 'string'],
            'privacy_content_html' => ['nullable', 'string'],
            'terms_content_html' => ['nullable', 'string'],
            'legal_version_history_json' => ['nullable', 'string'],
            'custom_pages_json' => ['nullable', 'string'],
            'custom_page_layout' => ['required', 'in:full-width,sidebar'],
            'custom_css' => ['nullable', 'string'],
            'block_editor_data_json' => ['nullable', 'string'],
            'publish_at' => ['nullable', 'date'],
            'password_protection_enabled' => ['nullable', 'boolean'],
            'preview_password' => ['nullable', 'string', 'max:120'],
            'action' => ['nullable', 'in:save,restore'],
            'restore_revision_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $action = (string) ($data['action'] ?? 'save');
        $currentPayload = $this->decodePayload((string) (LandingSetting::query()->where('key', 'cms_pages_payload')->value('value') ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) (LandingSetting::query()->where('key', 'cms_pages_revisions')->value('value') ?? '')));

        if ($action === 'restore') {
            $index = (int) ($data['restore_revision_index'] ?? -1);
            if ($index >= 0 && isset($revisions[$index]['payload']) && is_array($revisions[$index]['payload'])) {
                $restorePayload = $revisions[$index]['payload'];
                LandingSetting::query()->updateOrCreate(
                    ['key' => 'cms_pages_payload'],
                    ['value' => json_encode($restorePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
                );

                return redirect()
                    ->route('dashboard-website-cms-pages')
                    ->with('success', 'Revision halaman berhasil dipulihkan.');
            }

            return redirect()
                ->route('dashboard-website-cms-pages')
                ->with('success', 'Revision tidak ditemukan.');
        }

        $payload = [
            'editor_mode' => $data['editor_mode'],
            'wysiwyg_engine' => $data['wysiwyg_engine'],
            'homepage' => [
                'template' => $data['homepage_template'],
                'modules' => $this->safeJsonArray($data['homepage_modules_json'] ?? '[]'),
            ],
            'section_visibility' => [
                'services' => $request->boolean('section_services_enabled'),
                'portfolio' => $request->boolean('section_portfolio_enabled'),
                'why_us' => $request->boolean('section_why_enabled'),
                'testimonials' => $request->boolean('section_testimonials_enabled'),
                'process' => $request->boolean('section_process_enabled'),
                'team' => $request->boolean('section_team_enabled'),
                'contact' => $request->boolean('section_contact_enabled'),
            ],
            'about' => [
                'story_timeline' => $this->safeJsonArray($data['about_story_timeline_json'] ?? '[]'),
                'vision' => (string) ($data['about_vision'] ?? ''),
                'mission' => (string) ($data['about_mission'] ?? ''),
                'awards' => $this->safeJsonArray($data['about_awards_json'] ?? '[]'),
                'clients' => $this->safeJsonArray($data['about_clients_json'] ?? '[]'),
            ],
            'services' => [
                'categories' => $this->safeJsonArray($data['services_categories_json'] ?? '[]'),
            ],
            'process' => [
                'pipeline' => $this->safeJsonArray($data['process_pipeline_json'] ?? '[]'),
                'expectations' => $this->safeJsonArray($data['process_expectations_json'] ?? '[]'),
            ],
            'faq' => [
                'categories' => $this->safeJsonArray($data['faq_categories_json'] ?? '[]'),
                'items' => $this->safeJsonArray($data['faq_items_json'] ?? '[]'),
            ],
            'legal' => [
                'privacy_html' => (string) ($data['privacy_content_html'] ?? ''),
                'terms_html' => (string) ($data['terms_content_html'] ?? ''),
                'version_history' => $this->safeJsonArray($data['legal_version_history_json'] ?? '[]'),
            ],
            'custom_pages' => $this->safeJsonArray($data['custom_pages_json'] ?? '[]'),
            'custom_page_layout' => $data['custom_page_layout'],
            'custom_css' => (string) ($data['custom_css'] ?? ''),
            'block_editor_data' => $this->safeJsonArray($data['block_editor_data_json'] ?? '[]'),
            'publish_at' => (string) ($data['publish_at'] ?? ''),
            'password_protection_enabled' => $request->boolean('password_protection_enabled'),
            'preview_password' => (string) ($data['preview_password'] ?? ''),
            'updated_at' => now()->toDateTimeString(),
        ];

        if (! empty($currentPayload)) {
            array_unshift($revisions, [
                'saved_at' => now()->toDateTimeString(),
                'summary' => 'Autosave before update',
                'payload' => $currentPayload,
            ]);
            $revisions = $this->trimRevisions($revisions);
        }

        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_pages_payload'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_pages_revisions'],
            ['value' => json_encode($revisions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()
            ->route('dashboard-website-cms-pages')
            ->with('success', 'Halaman statis berhasil diperbarui.');
    }

    private function decodePayload(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function decodeRevisions(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function safeJsonArray(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function prettyJson(array $value): string
    {
        return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function trimRevisions(array $revisions): array
    {
        return array_values(array_slice($revisions, 0, self::MAX_REVISIONS));
    }

    private function sectionVisibilityMap(mixed $value): array
    {
        $defaults = [
            'services' => true,
            'portfolio' => true,
            'why_us' => true,
            'testimonials' => true,
            'process' => true,
            'team' => true,
            'contact' => true,
        ];
        if (! is_array($value)) {
            return $defaults;
        }

        foreach ($defaults as $key => $default) {
            if (array_key_exists($key, $value)) {
                $defaults[$key] = (bool) $value[$key];
            }
        }

        return $defaults;
    }

    private function defaultHomepageModules(): array
    {
        return ['Hero', 'Services', 'Portfolio Preview', 'About', 'CTA'];
    }

    private function defaultAboutTimeline(): array
    {
        return [
            ['year' => '2019', 'title' => 'Studio Founded', 'description' => 'Production House Nusantara resmi beroperasi.'],
            ['year' => '2021', 'title' => 'National Campaigns', 'description' => 'Menangani proyek kampanye lintas kota.'],
            ['year' => '2024', 'title' => 'End-to-End Pipeline', 'description' => 'Membuka layanan full stack production.'],
        ];
    }

    private function defaultAwards(): array
    {
        return [
            ['name' => 'Best Commercial Craft', 'year' => '2023'],
            ['name' => 'Creative Production Excellence', 'year' => '2024'],
        ];
    }

    private function defaultClients(): array
    {
        return ['Client A', 'Client B', 'Client C', 'Client D'];
    }

    private function defaultServiceCategories(): array
    {
        return [
            ['name' => 'Film Production', 'icon' => '🎬', 'description' => 'Feature, short, branded film', 'pricing_range' => '25jt - 250jt'],
            ['name' => 'Commercial/Advertisement', 'icon' => '📺', 'description' => 'TV/Digital ads', 'pricing_range' => '15jt - 200jt'],
            ['name' => 'Documentary', 'icon' => '🎥', 'description' => 'Brand & social documentary', 'pricing_range' => '20jt - 180jt'],
            ['name' => 'Content Creation', 'icon' => '📱', 'description' => 'Social shortform campaign', 'pricing_range' => '8jt - 80jt'],
            ['name' => 'Post-Production', 'icon' => '🖥️', 'description' => 'Editing, color, audio', 'pricing_range' => '5jt - 100jt'],
            ['name' => 'Equipment Rental', 'icon' => '📦', 'description' => 'Camera, lighting, grip', 'pricing_range' => '1jt - 25jt/hari'],
        ];
    }

    private function defaultPipeline(): array
    {
        return [
            ['stage' => 'Pre-Production', 'title' => 'Concepting & Planning'],
            ['stage' => 'Production', 'title' => 'Shooting Execution'],
            ['stage' => 'Post-Production', 'title' => 'Edit, Color, Delivery'],
        ];
    }

    private function defaultExpectations(): array
    {
        return [
            ['stage' => 'Pre-Production', 'expectation' => 'Brief alignment, script, shotlist'],
            ['stage' => 'Production', 'expectation' => 'On-set supervision and quality control'],
            ['stage' => 'Post-Production', 'expectation' => 'Revision rounds and final master'],
        ];
    }

    private function defaultFaqCategories(): array
    {
        return ['General', 'Pricing', 'Production Timeline', 'Rights & Usage'];
    }

    private function defaultFaqItems(): array
    {
        return [
            ['category' => 'General', 'question' => 'Berapa lama proses produksi?', 'answer' => 'Bergantung kompleksitas, rata-rata 2-6 minggu.'],
            ['category' => 'Pricing', 'question' => 'Apakah bisa custom budget?', 'answer' => 'Ya, paket fleksibel sesuai scope dan objective.'],
        ];
    }

    private function defaultLegalHistory(): array
    {
        return [
            ['version' => 'v1.0', 'date' => now()->toDateString(), 'notes' => 'Initial policy publish'],
        ];
    }
}
