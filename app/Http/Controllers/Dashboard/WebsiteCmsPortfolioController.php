<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use App\Support\VideoConversionEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WebsiteCmsPortfolioController extends Controller
{
    private const MAX_REVISIONS = 10;

    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key')->all();
        $payload = $this->decodePayload((string) ($settings['cms_portfolio_payload'] ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) ($settings['cms_portfolio_revisions'] ?? '')));

        return view('content.dashboard.website-cms-portfolio', [
            'form' => [
                'projects_json' => $this->prettyJson($payload['projects'] ?? $this->defaultProjects()),
                'projects_form' => $this->normalizeProjectsForForm($payload['projects'] ?? $this->defaultProjects()),
                'categories_json' => $this->prettyJson($payload['categories'] ?? $this->defaultCategories()),
                'display_grid_columns' => (int) ($payload['display']['grid_columns'] ?? 3),
                'display_grid_mode' => (string) ($payload['display']['grid_mode'] ?? 'cinematic'),
                'display_enable_ajax_filter' => (bool) ($payload['display']['enable_ajax_filter'] ?? true),
                'display_sort_default' => (string) ($payload['display']['sort_default'] ?? 'newest'),
                'display_hover_effect' => (string) ($payload['display']['hover_effect'] ?? 'overlay'),
                'display_load_strategy' => (string) ($payload['display']['load_strategy'] ?? 'none'),
                'display_items_per_page' => (int) ($payload['display']['items_per_page'] ?? 8),
                'single_enable_video_fullscreen' => (bool) ($payload['single']['enable_video_fullscreen'] ?? true),
                'single_enable_related' => (bool) ($payload['single']['enable_related'] ?? true),
                'single_enable_share' => (bool) ($payload['single']['enable_share'] ?? true),
                'single_enable_download_press_kit' => (bool) ($payload['single']['enable_download_press_kit'] ?? true),
                'single_enable_prev_next' => (bool) ($payload['single']['enable_prev_next'] ?? true),
                'advanced_password_protected' => (bool) ($payload['advanced']['password_protected'] ?? false),
                'advanced_portfolio_password' => (string) ($payload['advanced']['portfolio_password'] ?? ''),
                'advanced_enable_draft_share' => (bool) ($payload['advanced']['enable_draft_share'] ?? true),
                'advanced_enable_lightbox' => (bool) ($payload['advanced']['enable_lightbox'] ?? true),
                'advanced_enable_before_after' => (bool) ($payload['advanced']['enable_before_after'] ?? true),
                'portfolio_section_title' => (string) ($payload['layout']['portfolio_title'] ?? ($settings['portfolio_title'] ?? 'INFINITE GALERY')),
                'layout_sections_json' => $this->prettyJson($payload['layout']['sections'] ?? $this->defaultLayoutSections()),
                'layout_concept_notes' => (string) ($payload['layout']['concept_notes'] ?? ''),
                'recent_projects_limit' => (int) ($payload['workflow']['recent_projects_limit'] ?? 6),
                'recent_projects_mode' => (string) ($payload['workflow']['recent_projects_mode'] ?? 'newest'),
            ],
            'revisions' => $revisions,
            'revisionCount' => count($revisions),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'projects_json' => ['nullable', 'string'],
            'projects_form' => ['nullable', 'array'],
            'projects_form.*.title' => ['nullable', 'string', 'max:200'],
            'projects_form.*.slug' => ['nullable', 'string', 'max:200'],
            'projects_form.*.client_name' => ['nullable', 'string', 'max:200'],
            'projects_form.*.category' => ['nullable', 'string', 'max:80'],
            'projects_form.*.year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'projects_form.*.featured_image' => ['nullable', 'string', 'max:2000'],
            'projects_form.*.featured_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'projects_form.*.video_embed' => ['nullable', 'string', 'max:2000'],
            'projects_form.*.video_file' => ['nullable', 'file', 'mimetypes:video/*', 'max:512000'],
            'projects_form.*.description' => ['nullable', 'string'],
            'projects_form.*.services_used_text' => ['nullable', 'string'],
            'projects_form.*.duration' => ['nullable', 'string', 'max:20'],
            'projects_form.*.testimonial' => ['nullable', 'string'],
            'projects_form.*.popular_score' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'projects_form.*.is_draft' => ['nullable', 'boolean'],
            'projects_form.*.password_protected' => ['nullable', 'boolean'],
            'projects_form.*.password' => ['nullable', 'string', 'max:120'],
            'projects_form.*.shareable_draft_link' => ['nullable', 'string', 'max:2000'],
            'projects_form.*.press_kit_pdf' => ['nullable', 'string', 'max:2000'],
            'projects_form.*.press_kit_file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'projects_form.*.challenge' => ['nullable', 'string'],
            'projects_form.*.solution' => ['nullable', 'string'],
            'projects_form.*.result' => ['nullable', 'string'],
            'projects_form.*.gallery_images_text' => ['nullable', 'string'],
            'projects_form.*.gallery_files' => ['nullable', 'array'],
            'projects_form.*.gallery_files.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'projects_form.*.awards_text' => ['nullable', 'string'],
            'projects_form.*.team_credits_text' => ['nullable', 'string'],
            'projects_form.*.before_image' => ['nullable', 'string', 'max:2000'],
            'projects_form.*.after_image' => ['nullable', 'string', 'max:2000'],
            'categories_json' => ['nullable', 'string'],
            'display_grid_columns' => ['required', 'integer', 'min:2', 'max:6'],
            'display_grid_mode' => ['required', 'in:basic,masonry,hover,filterable,video,card,cinematic,responsive,uniform'],
            'display_enable_ajax_filter' => ['nullable', 'boolean'],
            'display_sort_default' => ['required', 'in:newest,popular,alphabetical'],
            'display_hover_effect' => ['required', 'in:overlay,lift,zoom'],
            'display_load_strategy' => ['required', 'in:none,load_more,infinite,pagination'],
            'display_items_per_page' => ['required', 'integer', 'min:1', 'max:30'],
            'single_enable_video_fullscreen' => ['nullable', 'boolean'],
            'single_enable_related' => ['nullable', 'boolean'],
            'single_enable_share' => ['nullable', 'boolean'],
            'single_enable_download_press_kit' => ['nullable', 'boolean'],
            'single_enable_prev_next' => ['nullable', 'boolean'],
            'advanced_password_protected' => ['nullable', 'boolean'],
            'advanced_portfolio_password' => ['nullable', 'string', 'max:120'],
            'advanced_enable_draft_share' => ['nullable', 'boolean'],
            'advanced_enable_lightbox' => ['nullable', 'boolean'],
            'advanced_enable_before_after' => ['nullable', 'boolean'],
            'portfolio_section_title' => ['nullable', 'string', 'max:220'],
            'layout_sections_json' => ['nullable', 'string'],
            'layout_concept_notes' => ['nullable', 'string'],
            'recent_projects_limit' => ['required', 'integer', 'min:1', 'max:20'],
            'recent_projects_mode' => ['required', 'in:newest,popular,alphabetical'],
            'action' => ['nullable', 'in:save,restore'],
            'restore_revision_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $action = (string) ($data['action'] ?? 'save');
        $currentPayload = $this->decodePayload((string) (LandingSetting::query()->where('key', 'cms_portfolio_payload')->value('value') ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) (LandingSetting::query()->where('key', 'cms_portfolio_revisions')->value('value') ?? '')));

        if ($action === 'restore') {
            $index = (int) ($data['restore_revision_index'] ?? -1);
            if ($index >= 0 && isset($revisions[$index]['payload']) && is_array($revisions[$index]['payload'])) {
                $restorePayload = $revisions[$index]['payload'];
                LandingSetting::query()->updateOrCreate(
                    ['key' => 'cms_portfolio_payload'],
                    ['value' => json_encode($restorePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
                );

                return redirect()
                    ->route('dashboard-website-cms-portfolio')
                    ->with('success', 'Revision portfolio berhasil dipulihkan.');
            }

            return redirect()
                ->route('dashboard-website-cms-portfolio')
                ->with('success', 'Revision tidak ditemukan.');
        }

        try {
            $projectsFromForm = $this->parseProjectsFromForm($request, $request->input('projects_form', []));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'projects_form' => 'Konversi video project gagal. Pastikan FFmpeg/GStreamer/HandBrake aktif di server.',
            ]);
        }

        $payload = [
            'projects' => $projectsFromForm !== [] ? $projectsFromForm : $this->safeJsonArray($data['projects_json'] ?? '[]'),
            'categories' => $this->safeJsonArray($data['categories_json'] ?? '[]'),
            'display' => [
                'grid_columns' => (int) $data['display_grid_columns'],
                'grid_mode' => $data['display_grid_mode'] === 'uniform' ? 'basic' : $data['display_grid_mode'],
                'enable_ajax_filter' => $request->boolean('display_enable_ajax_filter'),
                'sort_default' => $data['display_sort_default'],
                'hover_effect' => $data['display_hover_effect'],
                'load_strategy' => (string) $data['display_load_strategy'],
                'items_per_page' => (int) $data['display_items_per_page'],
            ],
            'single' => [
                'enable_video_fullscreen' => $request->boolean('single_enable_video_fullscreen'),
                'enable_related' => $request->boolean('single_enable_related'),
                'enable_share' => $request->boolean('single_enable_share'),
                'enable_download_press_kit' => $request->boolean('single_enable_download_press_kit'),
                'enable_prev_next' => $request->boolean('single_enable_prev_next'),
            ],
            'advanced' => [
                'password_protected' => $request->boolean('advanced_password_protected'),
                'portfolio_password' => (string) ($data['advanced_portfolio_password'] ?? ''),
                'enable_draft_share' => $request->boolean('advanced_enable_draft_share'),
                'enable_lightbox' => $request->boolean('advanced_enable_lightbox'),
                'enable_before_after' => $request->boolean('advanced_enable_before_after'),
            ],
            'layout' => [
                'portfolio_title' => (string) ($data['portfolio_section_title'] ?? 'INFINITE GALERY'),
                'sections' => $this->safeJsonArray($data['layout_sections_json'] ?? '[]'),
                'concept_notes' => (string) ($data['layout_concept_notes'] ?? ''),
            ],
            'workflow' => [
                'recent_projects_limit' => (int) $data['recent_projects_limit'],
                'recent_projects_mode' => $data['recent_projects_mode'],
            ],
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
            ['key' => 'cms_portfolio_payload'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_portfolio_revisions'],
            ['value' => json_encode($revisions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        LandingSetting::query()->updateOrCreate(
            ['key' => 'portfolio_title'],
            ['value' => (string) ($data['portfolio_section_title'] ?? 'INFINITE GALERY')]
        );
        LandingSetting::query()->updateOrCreate(
            ['key' => 'portfolio_items'],
            ['value' => json_encode($this->mapProjectsToLegacyPortfolioItems($payload['projects']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()
            ->route('dashboard-website-cms-portfolio')
            ->with('success', 'Portfolio CMS berhasil diperbarui.');
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

    private function normalizeProjectsForForm(array $projects): array
    {
        return collect($projects)
            ->filter(fn ($project): bool => is_array($project))
            ->map(function (array $project): array {
                $servicesUsed = collect($project['services_used'] ?? [])->map(fn ($item): string => (string) $item)->filter()->implode(', ');
                $galleryImages = collect($project['gallery_images'] ?? [])->map(fn ($item): string => (string) $item)->filter()->implode(PHP_EOL);
                $awards = collect($project['awards'] ?? [])->map(fn ($item): string => (string) $item)->filter()->implode(PHP_EOL);
                $teamCredits = collect($project['team_credits'] ?? [])
                    ->map(function ($item): string {
                        if (is_array($item)) {
                            return trim((string) ($item['role'] ?? '')).':'.trim((string) ($item['name'] ?? ''));
                        }

                        return (string) $item;
                    })
                    ->filter()
                    ->implode(PHP_EOL);

                return [
                    'title' => (string) ($project['title'] ?? ''),
                    'slug' => (string) ($project['slug'] ?? ''),
                    'client_name' => (string) ($project['client_name'] ?? ''),
                    'category' => (string) ($project['category'] ?? 'Commercial'),
                    'year' => (string) ($project['year'] ?? ''),
                    'featured_image' => (string) ($project['featured_image'] ?? ''),
                    'video_embed' => (string) ($project['video_embed'] ?? ''),
                    'description' => (string) ($project['description'] ?? ''),
                    'services_used_text' => $servicesUsed,
                    'duration' => (string) ($project['duration'] ?? ''),
                    'testimonial' => (string) ($project['client_testimonial'] ?? ''),
                    'popular_score' => (string) ($project['popular_score'] ?? '0'),
                    'is_draft' => (bool) ($project['is_draft'] ?? false),
                    'password_protected' => (bool) ($project['password_protected'] ?? false),
                    'password' => (string) ($project['password'] ?? ''),
                    'shareable_draft_link' => (string) ($project['shareable_draft_link'] ?? ''),
                    'press_kit_pdf' => (string) ($project['press_kit_pdf'] ?? ''),
                    'challenge' => (string) ($project['case_study']['challenge'] ?? ''),
                    'solution' => (string) ($project['case_study']['solution'] ?? ''),
                    'result' => (string) ($project['case_study']['result'] ?? ''),
                    'gallery_images_text' => $galleryImages,
                    'awards_text' => $awards,
                    'team_credits_text' => $teamCredits,
                    'before_image' => (string) ($project['before_after']['before'] ?? ''),
                    'after_image' => (string) ($project['before_after']['after'] ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    private function parseProjectsFromForm(Request $request, $projects): array
    {
        if (! is_array($projects)) {
            return [];
        }

        return collect($projects)
            ->filter(fn ($project): bool => is_array($project))
            ->map(function (array $project, int $index) use ($request): array {
                $title = trim((string) ($project['title'] ?? ''));
                $slug = trim((string) ($project['slug'] ?? ''));
                $category = trim((string) ($project['category'] ?? 'Commercial'));
                $clientName = trim((string) ($project['client_name'] ?? ''));
                $year = (int) ($project['year'] ?? 0);
                $featuredImage = trim((string) ($project['featured_image'] ?? ''));
                $videoEmbed = trim((string) ($project['video_embed'] ?? ''));
                $description = trim((string) ($project['description'] ?? ''));
                $duration = trim((string) ($project['duration'] ?? ''));
                $testimonial = trim((string) ($project['testimonial'] ?? ''));
                $popularScore = (int) ($project['popular_score'] ?? 0);
                $isDraft = filter_var($project['is_draft'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $passwordProtected = filter_var($project['password_protected'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $password = trim((string) ($project['password'] ?? ''));
                $shareableDraftLink = trim((string) ($project['shareable_draft_link'] ?? ''));
                $pressKitPdf = trim((string) ($project['press_kit_pdf'] ?? ''));
                $challenge = trim((string) ($project['challenge'] ?? ''));
                $solution = trim((string) ($project['solution'] ?? ''));
                $result = trim((string) ($project['result'] ?? ''));
                $beforeImage = trim((string) ($project['before_image'] ?? ''));
                $afterImage = trim((string) ($project['after_image'] ?? ''));

                $servicesUsed = collect(explode(',', (string) ($project['services_used_text'] ?? '')))
                    ->map(fn (string $value): string => trim($value))
                    ->filter()
                    ->values()
                    ->all();
                $galleryImages = collect(preg_split('/\r\n|\r|\n/', (string) ($project['gallery_images_text'] ?? '')))
                    ->map(fn (string $value): string => trim($value))
                    ->filter()
                    ->values()
                    ->all();
                $awards = collect(preg_split('/\r\n|\r|\n/', (string) ($project['awards_text'] ?? '')))
                    ->map(fn (string $value): string => trim($value))
                    ->filter()
                    ->values()
                    ->all();
                $teamCredits = collect(preg_split('/\r\n|\r|\n/', (string) ($project['team_credits_text'] ?? '')))
                    ->map(fn (string $value): string => trim($value))
                    ->filter()
                    ->map(function (string $line): array {
                        [$role, $name] = array_pad(explode(':', $line, 2), 2, '');

                        return [
                            'role' => trim($role),
                            'name' => trim($name),
                        ];
                    })
                    ->filter(fn (array $credit): bool => $credit['role'] !== '' || $credit['name'] !== '')
                    ->values()
                    ->all();

                $featuredImageUpload = $request->file("projects_form.$index.featured_image_file");
                if ($featuredImageUpload instanceof UploadedFile) {
                    $featuredImage = $featuredImageUpload->store('cms/portfolio/featured', 'public');
                }

                $videoUpload = $request->file("projects_form.$index.video_file");
                if ($videoUpload instanceof UploadedFile) {
                    $asset = app(VideoConversionEngine::class)->registerUpload(
                        $videoUpload,
                        'cms/portfolio/videos',
                        'portfolio',
                        $title !== '' ? $title : ($slug !== '' ? $slug : 'portfolio-video'),
                        ['setting_key' => 'cms_portfolio_payload']
                    );
                    $videoEmbed = (string) $asset->source_path;
                }

                $galleryFileUploads = $request->file("projects_form.$index.gallery_files", []);
                if (is_array($galleryFileUploads)) {
                    foreach ($galleryFileUploads as $file) {
                        if ($file instanceof UploadedFile) {
                            $galleryImages[] = $file->store('cms/portfolio/gallery', 'public');
                        }
                    }
                }

                $pressKitUpload = $request->file("projects_form.$index.press_kit_file");
                if ($pressKitUpload instanceof UploadedFile) {
                    $pressKitPdf = $pressKitUpload->store('cms/portfolio/press-kit', 'public');
                }

                return [
                    'slug' => $slug !== '' ? Str::slug($slug) : Str::slug($title),
                    'title' => $title,
                    'client_name' => $clientName,
                    'category' => $category !== '' ? $category : 'Commercial',
                    'year' => $year > 0 ? $year : (int) now()->format('Y'),
                    'featured_image' => $featuredImage,
                    'gallery_images' => $galleryImages,
                    'video_embed' => $videoEmbed,
                    'description' => $description,
                    'services_used' => $servicesUsed,
                    'team_credits' => $teamCredits,
                    'duration' => $duration,
                    'awards' => $awards,
                    'client_testimonial' => $testimonial,
                    'case_study' => [
                        'challenge' => $challenge,
                        'solution' => $solution,
                        'result' => $result,
                    ],
                    'popular_score' => max(0, $popularScore),
                    'is_draft' => (bool) $isDraft,
                    'shareable_draft_link' => $shareableDraftLink,
                    'password_protected' => (bool) $passwordProtected,
                    'password' => $password,
                    'press_kit_pdf' => $pressKitPdf,
                    'before_after' => [
                        'before' => $beforeImage,
                        'after' => $afterImage,
                    ],
                ];
            })
            ->filter(fn (array $project): bool => trim($project['title']) !== '')
            ->values()
            ->all();
    }

    private function mapProjectsToLegacyPortfolioItems(array $projects): array
    {
        return collect($projects)
            ->filter(fn ($project): bool => is_array($project))
            ->map(function (array $project): array {
                $category = (string) ($project['category'] ?? 'General');
                $videoUrl = (string) ($project['video_embed'] ?? '');
                $thumbnail = (string) ($project['featured_image'] ?? '');
                $mediaType = $videoUrl !== '' ? 'video' : 'photo';
                $mediaUrl = $videoUrl !== '' ? $videoUrl : $thumbnail;
                if ($mediaUrl === '') {
                    $mediaUrl = (string) collect($project['gallery_images'] ?? [])->filter()->first();
                }
                if ($thumbnail === '') {
                    $thumbnail = $mediaUrl;
                }

                return [
                    'title' => (string) ($project['title'] ?? 'Untitled Project'),
                    'category' => $category,
                    'media_type' => $mediaType,
                    'source_type' => Str::startsWith($mediaUrl, ['http://', 'https://']) ? 'url' : 'upload',
                    'media_url' => $mediaUrl,
                    'social_url' => '',
                    'thumbnail_url' => $thumbnail,
                    'description' => (string) ($project['description'] ?? ''),
                    'video_url' => $videoUrl,
                    'detail_url' => '#contact',
                    'size' => 'normal',
                ];
            })
            ->values()
            ->all();
    }

    private function defaultProjects(): array
    {
        return [
            [
                'slug' => 'brand-film-nusantara-kopi',
                'title' => 'Brand Film · Nusantara Kopi',
                'client_name' => 'Nusantara Kopi',
                'category' => 'Commercial',
                'year' => 2025,
                'featured_image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1400&q=80',
                'gallery_images' => [],
                'video_embed' => 'https://www.youtube.com/embed/Scxs7L0vhZ4',
                'description' => 'Kampanye brand story untuk positioning premium coffee chain.',
                'services_used' => ['Production', 'Editing', 'Color Grading'],
                'team_credits' => [
                    ['role' => 'Director', 'name' => 'A. Pratama'],
                    ['role' => 'DP', 'name' => 'R. Wiratama'],
                    ['role' => 'Editor', 'name' => 'D. Raharjo'],
                ],
                'duration' => '01:30',
                'awards' => [],
                'client_testimonial' => 'Tim PHN sangat solid dari concept sampai delivery.',
                'case_study' => [
                    'challenge' => 'Brand awareness stagnan di urban market.',
                    'solution' => 'Narrative-led brand film + cutdown social assets.',
                    'result' => 'Engagement naik 2.4x dalam 30 hari.',
                ],
                'popular_score' => 78,
                'is_draft' => false,
                'shareable_draft_link' => '',
                'password_protected' => false,
                'password' => '',
                'press_kit_pdf' => '',
                'before_after' => ['before' => '', 'after' => ''],
            ],
        ];
    }

    private function defaultCategories(): array
    {
        return [
            ['slug' => 'commercial', 'name' => 'Commercial', 'thumbnail' => '', 'description' => 'Advertising and branded commercial projects', 'seo_title' => 'Commercial Portfolio', 'seo_description' => 'Showcase project commercial terbaru'],
            ['slug' => 'film', 'name' => 'Film', 'thumbnail' => '', 'description' => 'Film and narrative projects', 'seo_title' => 'Film Portfolio', 'seo_description' => 'Showcase project film'],
            ['slug' => 'doc', 'name' => 'Doc', 'thumbnail' => '', 'description' => 'Documentary projects', 'seo_title' => 'Documentary Portfolio', 'seo_description' => 'Showcase project documentary'],
            ['slug' => 'event', 'name' => 'Event', 'thumbnail' => '', 'description' => 'Event capture and recap', 'seo_title' => 'Event Portfolio', 'seo_description' => 'Showcase project event'],
            ['slug' => 'content', 'name' => 'Content', 'thumbnail' => '', 'description' => 'Content creation for digital', 'seo_title' => 'Content Portfolio', 'seo_description' => 'Showcase project content'],
        ];
    }

    private function defaultLayoutSections(): array
    {
        return [
            ['type' => 'hero', 'title' => 'Portfolio Hero', 'enabled' => true],
            ['type' => 'filters', 'title' => 'Category Filters', 'enabled' => true],
            ['type' => 'grid', 'title' => 'Project Grid', 'enabled' => true],
            ['type' => 'cta', 'title' => 'Work With Us CTA', 'enabled' => true],
        ];
    }
}
