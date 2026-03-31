<?php

namespace App\Http\Controllers;

use App\Models\HeroSlide;
use App\Models\LandingSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index()
    {
        $settings = $this->settingMap();
        $cmsGeneral = $this->cmsGeneralMap($settings);
        $cmsSocialLinks = $this->cmsSocialLinks($cmsGeneral);
        $headerConfig = $this->headerConfigMap($settings, $cmsGeneral, $cmsSocialLinks);
        $pagesPayload = $this->decodeCmsPagesPayload((string) ($settings['cms_pages_payload'] ?? ''));
        $sectionVisibility = $this->sectionVisibilityMap($pagesPayload['section_visibility'] ?? null);
        $portfolioPayload = $this->decodeCmsPortfolioPayload((string) ($settings['cms_portfolio_payload'] ?? ''));

        if ($cmsGeneral['maintenance_mode']) {
            return view('maintenance-public', [
                'cmsGeneral' => $cmsGeneral,
                'cmsSocialLinks' => $cmsSocialLinks,
                'headerConfig' => $headerConfig,
                'isInnerPage' => true,
            ]);
        }

        $slideMetaMap = $this->decodeSliderVideoMetaMap((string) ($settings['cms_slider_video_slide_meta'] ?? ''));

        try {
            $heroSlides = HeroSlide::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get([
                    'id',
                    'title',
                    'caption',
                    'video_url',
                    'cta_text',
                    'cta_url',
                    'sort_order',
                    'duration_seconds',
                    'overlay_opacity',
                ]);
        } catch (QueryException) {
            $heroSlides = new Collection;
        }

        if ($heroSlides->isEmpty()) {
            $heroSlides = new Collection([
                (object) [
                    'id' => null,
                    'title' => 'Cinematic Flow 1',
                    'caption' => null,
                    'video_url' => 'https://videos.pexels.com/video-files/853890/853890-hd_1920_1080_25fps.mp4',
                    'cta_text' => null,
                    'cta_url' => null,
                    'sort_order' => 1,
                    'duration_seconds' => 7,
                    'overlay_opacity' => 0.78,
                ],
                (object) [
                    'id' => null,
                    'title' => 'Cinematic Flow 2',
                    'caption' => null,
                    'video_url' => 'https://videos.pexels.com/video-files/3129957/3129957-hd_1920_1080_25fps.mp4',
                    'cta_text' => null,
                    'cta_url' => null,
                    'sort_order' => 2,
                    'duration_seconds' => 7,
                    'overlay_opacity' => 0.78,
                ],
            ]);
        }

        $heroSlides = $heroSlides->map(function ($slide) use ($slideMetaMap) {
            $slideId = isset($slide->id) ? (string) $slide->id : null;
            $meta = ($slideId !== null && isset($slideMetaMap[$slideId]) && is_array($slideMetaMap[$slideId])) ? $slideMetaMap[$slideId] : [];

            if (! empty($slide->video_url) && ! Str::startsWith($slide->video_url, ['http://', 'https://'])) {
                $slide->video_url = '/storage/'.ltrim((string) $slide->video_url, '/');
            }
            $slide->video_source = (string) ($meta['video_source'] ?? 'upload');
            $slide->external_url = (string) ($meta['external_url'] ?? '');
            $slide->tablet_video = $this->storagePathToUrl((string) ($meta['tablet_video'] ?? ''));
            $slide->poster_image = $this->storagePathToUrl((string) ($meta['poster_image'] ?? ''));
            $slide->mobile_fallback_image = $this->storagePathToUrl((string) ($meta['mobile_fallback_image'] ?? ''));
            $slide->mute_default = (bool) ($meta['mute_default'] ?? true);
            $slide->text_position = (string) ($meta['text_position'] ?? 'center');
            $slide->overlay_color = (string) ($meta['overlay_color'] ?? '#000000');
            $slide->text_animation = (string) ($meta['text_animation'] ?? 'fade-up');

            return $slide;
        });

        $landingContent = [
            'heading_text' => (($settings['heading_text'] ?? null) === 'A Moving Canvas of Color' || empty($settings['heading_text'] ?? null))
                ? 'PRODUCTION HOUSE NUSANTARA'
                : $settings['heading_text'],
            'tagline_text' => $settings['tagline_text'] ?? 'Authentic Heritage • Modern Vision • Cinematic Excellence',
            'lead_text' => ($settings['lead_text'] ?? '') === 'It glows with warmth, dissolves in color, and leaves only a trace of silence behind. What we call light is really time unfolding, a slow dance between fire and feeling.'
                ? ''
                : ($settings['lead_text'] ?? ''),
            'primary_btn_text' => $settings['primary_btn_text'] ?? 'Explore the Glow',
            'primary_btn_url' => $settings['primary_btn_url'] ?? '#services',
            'secondary_btn_text' => $settings['secondary_btn_text'] ?? 'Jelajahi Cerita',
            'secondary_btn_url' => $settings['secondary_btn_url'] ?? '#portfolio',
        ];

        $sliderConfig = [
            'autoplay_ms' => (int) ($settings['autoplay_ms'] ?? 7000),
            'transition_ms' => (int) ($settings['transition_ms'] ?? 1000),
            'show_controls' => ($settings['show_controls'] ?? '1') === '1',
            'auto_play' => ($settings['cms_slider_autoplay'] ?? ($settings['auto_play'] ?? '1')) === '1',
            'loop' => ($settings['cms_slider_loop'] ?? '1') === '1',
            'transition_effect' => (string) ($settings['cms_slider_transition_effect'] ?? 'fade'),
            'lazy_load' => ($settings['cms_slider_lazy_load'] ?? '1') === '1',
            'preload_critical' => ($settings['cms_slider_preload_critical'] ?? '1') === '1',
            'mobile_disable_video' => ($settings['cms_slider_mobile_disable_video'] ?? '1') === '1',
            'engine_mode' => (string) ($settings['cms_slider_engine_mode'] ?? 'sr7'),
            'parallax_strength' => (float) ($settings['parallax_strength'] ?? 0.06),
            'glow_intensity' => (float) ($settings['glow_intensity'] ?? 1),
        ];

        $landingSections = [
            'services_title' => $settings['services_title'] ?? 'Services',
            'services_items' => $this->decodeJsonSetting($settings, 'services_items', $this->defaultServices()),
            'portfolio_title' => $settings['portfolio_title'] ?? 'INFINITE GALERY',
            'showreel_title' => $settings['showreel_title'] ?? 'Main Showreel',
            'showreel_thumb_url' => $settings['showreel_thumb_url'] ?? 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?auto=format&fit=crop&w=1400&q=80',
            'showreel_video_url' => $settings['showreel_video_url'] ?? 'https://www.youtube.com/embed/Scxs7L0vhZ4',
            'portfolio_items' => $this->decodeJsonSetting($settings, 'portfolio_items', $this->defaultPortfolioItems()),
            'why_title' => $settings['why_title'] ?? 'Kenapa harus Production House Nusantara?',
            'why_items' => $this->decodeJsonSetting($settings, 'why_items', $this->defaultWhyItems()),
            'testimonial_title' => $settings['testimonial_title'] ?? 'Apa Kata Klien Kami',
            'testimonial_items' => $this->decodeJsonSetting($settings, 'testimonial_items', $this->defaultTestimonialItems()),
            'counter_projects' => (int) ($settings['counter_projects'] ?? 120),
            'counter_clients' => (int) ($settings['counter_clients'] ?? 50),
            'counter_years' => (int) ($settings['counter_years'] ?? 5),
            'team_title' => $settings['team_title'] ?? 'Creative Production Team',
            'team_members' => $this->decodeJsonSetting($settings, 'team_members', $this->defaultTeamMembers()),
            'process_title' => $settings['process_title'] ?? 'Workflow Produksi Profesional',
            'process_steps' => $this->decodeJsonSetting($settings, 'process_steps', $this->defaultProcessSteps()),
            'contact_title' => $settings['contact_title'] ?? 'Siap membuat karya visual berkelas?',
            'contact_text' => $settings['contact_text'] ?? 'Mari produksi bersama Production House Nusantara dan wujudkan visual story yang kuat, modern, dan berjiwa Nusantara.',
            'contact_button_primary_text' => $settings['contact_button_primary_text'] ?? 'Hubungi Kami',
            'contact_button_primary_url' => $settings['contact_button_primary_url'] ?? 'https://wa.me/6281234567890',
            'contact_button_whatsapp_text' => $settings['contact_button_whatsapp_text'] ?? 'WhatsApp',
            'contact_button_whatsapp_url' => $settings['contact_button_whatsapp_url'] ?? 'https://wa.me/6281234567890',
            'contact_button_project_text' => $settings['contact_button_project_text'] ?? 'Mulai Project',
            'contact_button_project_url' => $settings['contact_button_project_url'] ?? '#services',
            'contact_map_query' => $cmsGeneral['map_query'] !== '' ? $cmsGeneral['map_query'] : ($cmsGeneral['office_address'] !== '' ? $cmsGeneral['office_address'] : '-8.112972,112.311306'),
            'footer_brand_name' => $settings['footer_brand_name'] ?? 'Production House Nusantara',
            'footer_address' => $cmsGeneral['office_address'] !== '' ? $cmsGeneral['office_address'] : ($settings['footer_address'] ?? "8°06'46.7\"S 112°18'40.7\"E"),
            'footer_whatsapp' => $cmsGeneral['contact_phone'] !== '' ? $cmsGeneral['contact_phone'] : ($settings['footer_whatsapp'] ?? '+62 812-3456-7890'),
            'footer_email' => $cmsGeneral['contact_email'] !== '' ? $cmsGeneral['contact_email'] : ($settings['footer_email'] ?? 'halo@productionhousenusantara.com'),
            'footer_copyright' => $settings['footer_copyright'] ?? '© Production House Nusantara',
            'footer_social_links' => ! empty($cmsSocialLinks) ? $cmsSocialLinks : $this->decodeJsonSetting($settings, 'footer_social_links', $this->defaultFooterSocialLinks()),
            'footer_logo_url' => $this->resolveFooterLogoUrl($settings),
        ];

        $whatsappUrlFromCms = $this->toWhatsappUrl($cmsGeneral['contact_phone']);
        if ($whatsappUrlFromCms !== null) {
            $landingSections['contact_button_primary_url'] = $whatsappUrlFromCms;
            $landingSections['contact_button_whatsapp_url'] = $whatsappUrlFromCms;
        }

        $landingSections['why_items'] = collect($landingSections['why_items'])
            ->filter(fn (array $item): bool => trim((string) ($item['text'] ?? '')) !== '')
            ->values()
            ->all();

        if (count($landingSections['why_items']) < 8) {
            $landingSections['why_items'] = $this->defaultWhyItems();
        }

        $landingSections['testimonial_items'] = collect($landingSections['testimonial_items'])
            ->filter(fn (array $item): bool => trim((string) ($item['quote'] ?? '')) !== '')
            ->values()
            ->all();

        if (count($landingSections['testimonial_items']) < 3) {
            $landingSections['testimonial_items'] = $this->defaultTestimonialItems();
        }

        if (is_array($portfolioPayload['projects'] ?? null)) {
            $portfolioProjects = collect($portfolioPayload['projects'])->filter(fn ($item): bool => is_array($item))->values();
            $landingSections['portfolio_items'] = $this->mapPortfolioProjectsToLandingItems($portfolioProjects);
        }
        $landingSections['portfolio_title'] = (string) ($portfolioPayload['layout']['portfolio_title'] ?? ($settings['portfolio_title'] ?? 'INFINITE GALERY'));

        $landingSections['portfolio_items'] = collect($landingSections['portfolio_items'])
            ->map(function (array $item): array {
                $category = (string) ($item['category'] ?? 'General');
                $mediaType = (string) ($item['media_type'] ?? 'video');
                $sourceType = (string) ($item['source_type'] ?? 'url');
                $mediaUrl = (string) ($item['media_url'] ?? ($item['video_url'] ?? $item['thumbnail_url'] ?? ''));
                if ($mediaUrl !== '' && ! Str::startsWith($mediaUrl, ['http://', 'https://']) && ! Str::startsWith($mediaUrl, '/storage/')) {
                    $mediaUrl = '/storage/'.ltrim($mediaUrl, '/');
                }
                $thumbnailUrl = (string) ($item['thumbnail_url'] ?? '');
                if ($thumbnailUrl !== '' && ! Str::startsWith($thumbnailUrl, ['http://', 'https://']) && ! Str::startsWith($thumbnailUrl, '/storage/')) {
                    $thumbnailUrl = '/storage/'.ltrim($thumbnailUrl, '/');
                }

                $item['media_type'] = in_array($mediaType, ['video', 'photo'], true) ? $mediaType : 'video';
                $item['source_type'] = in_array($sourceType, ['url', 'upload', 'social'], true) ? $sourceType : 'url';
                $item['media_url'] = $mediaUrl;
                $item['thumbnail_url'] = $thumbnailUrl !== '' ? $thumbnailUrl : $mediaUrl;
                $item['category_slug'] = Str::slug($category) ?: 'general';
                $item['media_slug'] = $item['media_type'] === 'photo' ? 'photo' : 'video';

                return $item;
            })
            ->values()
            ->all();

        $landingSections['team_members'] = collect($landingSections['team_members'])
            ->map(function (array $member): array {
                $photo = (string) ($member['photo_url'] ?? '');
                if ($photo !== '' && ! Str::startsWith($photo, ['http://', 'https://'])) {
                    $member['photo_url'] = '/storage/'.ltrim($photo, '/');
                }

                return $member;
            })
            ->values()
            ->all();

        $portfolioCategories = collect($landingSections['portfolio_items'])
            ->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $portfolioDisplay = [
            'grid_mode' => (string) (($portfolioPayload['display']['grid_mode'] ?? 'cinematic') === 'uniform' ? 'basic' : ($portfolioPayload['display']['grid_mode'] ?? 'cinematic')),
            'grid_columns' => max(1, min(6, (int) ($portfolioPayload['display']['grid_columns'] ?? 4))),
            'enable_ajax_filter' => (bool) ($portfolioPayload['display']['enable_ajax_filter'] ?? true),
            'sort_default' => (string) ($portfolioPayload['display']['sort_default'] ?? 'newest'),
            'hover_effect' => (string) ($portfolioPayload['display']['hover_effect'] ?? 'overlay'),
            'load_strategy' => (string) ($portfolioPayload['display']['load_strategy'] ?? 'none'),
            'items_per_page' => max(1, min(30, (int) ($portfolioPayload['display']['items_per_page'] ?? 8))),
        ];

        return view('home', compact('heroSlides', 'landingContent', 'sliderConfig', 'landingSections', 'portfolioCategories', 'portfolioDisplay', 'cmsGeneral', 'headerConfig', 'sectionVisibility'))
            ->with('isInnerPage', false);
    }

    public function submitContact(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $setting = LandingSetting::query()->firstOrCreate(
                ['key' => 'landing_contact_messages'],
                ['value' => '[]']
            );

            $existing = collect(json_decode((string) $setting->value, true) ?: []);
            $entry = [
                'id' => (string) Str::ulid(),
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? '',
                'service' => $payload['service'],
                'message' => $payload['message'],
                'created_at' => now()->toDateTimeString(),
            ];

            $setting->update([
                'value' => json_encode(
                    $existing->prepend($entry)->take(1000)->values()->all(),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ),
            ]);
        } catch (QueryException) {
            return response()->json([
                'message' => 'Sistem sedang sibuk, silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'message' => 'Pesan berhasil dikirim. Tim kami akan segera menghubungi Anda.',
        ]);
    }

    public function about(Request $request): View|RedirectResponse
    {
        return $this->renderCmsStaticPage($request, 'about');
    }

    public function services(Request $request): View|RedirectResponse
    {
        return $this->renderCmsStaticPage($request, 'services');
    }

    public function faq(Request $request): View|RedirectResponse
    {
        return $this->renderCmsStaticPage($request, 'faq');
    }

    public function privacyPolicy(Request $request): View|RedirectResponse
    {
        return $this->renderCmsStaticPage($request, 'privacy-policy');
    }

    public function terms(Request $request): View|RedirectResponse
    {
        return $this->renderCmsStaticPage($request, 'terms');
    }

    public function blogIndex(Request $request): View|RedirectResponse
    {
        $settings = $this->settingMap();
        $cmsGeneral = $this->cmsGeneralMap($settings);
        $cmsSocialLinks = $this->cmsSocialLinks($cmsGeneral);
        $headerConfig = $this->headerConfigMap($settings, $cmsGeneral, $cmsSocialLinks);

        if ($cmsGeneral['maintenance_mode']) {
            return view('maintenance-public', [
                'cmsGeneral' => $cmsGeneral,
                'cmsSocialLinks' => $cmsSocialLinks,
                'headerConfig' => $headerConfig,
                'isInnerPage' => true,
            ]);
        }

        $payload = $this->decodeCmsBlogNewsPayload((string) ($settings['cms_blog_news_payload'] ?? ''));
        $layout = is_array($payload['layout'] ?? null) ? $payload['layout'] : [];
        $itemsPerPage = max(1, min(30, (int) ($layout['items_per_page'] ?? 9)));
        $page = max(1, (int) $request->query('page', 1));
        $viewMode = (string) $request->query('view', (string) ($layout['view_mode'] ?? 'grid'));
        if (! in_array($viewMode, ['grid', 'list'], true)) {
            $viewMode = 'grid';
        }
        $sort = (string) $request->query('sort', 'newest');
        if (! in_array($sort, ['newest', 'popular', 'alphabetical'], true)) {
            $sort = 'newest';
        }
        $query = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $tag = trim((string) $request->query('tag', ''));

        $posts = $this->normalizePublicBlogPosts($payload['posts'] ?? []);
        $posts = $posts->filter(function (array $post) use ($query, $category, $tag): bool {
            if ($query !== '') {
                $haystack = Str::lower(
                    (string) ($post['title'] ?? '').' '.
                    strip_tags((string) ($post['content_html'] ?? '')).' '.
                    implode(' ', $post['categories'] ?? []).' '.
                    implode(' ', $post['tags'] ?? [])
                );
                if (! Str::contains($haystack, Str::lower($query))) {
                    return false;
                }
            }
            if ($category !== '' && ! in_array($category, $post['categories'] ?? [], true)) {
                return false;
            }
            if ($tag !== '' && ! in_array($tag, $post['tags'] ?? [], true)) {
                return false;
            }

            return true;
        });

        $posts = match ($sort) {
            'alphabetical' => $posts->sortBy(fn (array $post): string => Str::lower((string) ($post['title'] ?? '')))->values(),
            'popular' => $posts->sortByDesc(fn (array $post): int => (int) ($post['popular_score'] ?? 0))->values(),
            default => $posts->sortByDesc(fn (array $post): int => strtotime((string) ($post['published_at'] ?? '1970-01-01')) ?: 0)->values(),
        };

        $total = $posts->count();
        $currentItems = $posts->slice(($page - 1) * $itemsPerPage, $itemsPerPage)->values();
        $paginator = new LengthAwarePaginator(
            $currentItems,
            $total,
            $itemsPerPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $featuredPosts = $posts->filter(fn (array $post): bool => (bool) ($post['is_featured'] ?? false))->take(3)->values();
        $popularPosts = $posts
            ->sortByDesc(fn (array $post): int => (int) ($post['popular_score'] ?? 0))
            ->take(5)
            ->values();

        $allCategories = $this->safeArrayOfScalars($payload['categories'] ?? [])
            ->merge($posts->flatMap(fn (array $post): array => $post['categories'] ?? []))
            ->map(fn ($item): string => is_array($item) ? (string) ($item['name'] ?? '') : (string) $item)
            ->filter()
            ->unique()
            ->values();
        $allTags = $this->safeArrayOfScalars($payload['tags'] ?? [])
            ->merge($posts->flatMap(fn (array $post): array => $post['tags'] ?? []))
            ->map(fn ($item): string => (string) $item)
            ->filter()
            ->unique()
            ->values();

        $seoTitle = $cmsGeneral['site_title'].' Blog';
        if ($query !== '') {
            $seoTitle = 'Hasil pencarian "'.$query.'" · '.$cmsGeneral['site_title'].' Blog';
        }
        $seoDescription = $cmsGeneral['site_description'] !== ''
            ? $cmsGeneral['site_description']
            : 'Blog Production House Nusantara: behind the scenes, tutorial, industry trends, dan update project terbaru.';
        $canonicalUrl = $request->url().($request->query() ? '?'.http_build_query($request->query()) : '');

        return view('public-blog-index', [
            'cmsGeneral' => $cmsGeneral,
            'headerConfig' => $headerConfig,
            'isInnerPage' => true,
            'posts' => $paginator,
            'featuredPosts' => $featuredPosts,
            'popularPosts' => $popularPosts,
            'allCategories' => $allCategories,
            'allTags' => $allTags,
            'layout' => $layout,
            'viewMode' => $viewMode,
            'sort' => $sort,
            'searchQuery' => $query,
            'activeCategory' => $category,
            'activeTag' => $tag,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'canonicalUrl' => $canonicalUrl,
        ]);
    }

    public function blogShow(Request $request, string $slug): View|RedirectResponse
    {
        $settings = $this->settingMap();
        $cmsGeneral = $this->cmsGeneralMap($settings);
        $cmsSocialLinks = $this->cmsSocialLinks($cmsGeneral);
        $headerConfig = $this->headerConfigMap($settings, $cmsGeneral, $cmsSocialLinks);

        if ($cmsGeneral['maintenance_mode']) {
            return view('maintenance-public', [
                'cmsGeneral' => $cmsGeneral,
                'cmsSocialLinks' => $cmsSocialLinks,
                'headerConfig' => $headerConfig,
                'isInnerPage' => true,
            ]);
        }

        $payload = $this->decodeCmsBlogNewsPayload((string) ($settings['cms_blog_news_payload'] ?? ''));
        $this->incrementBlogPostMetric((string) Str::slug($slug), 'view_count', 1);
        $posts = $this->normalizePublicBlogPosts($payload['posts'] ?? []);
        $post = $posts->first(fn (array $item): bool => (string) ($item['slug'] ?? '') === Str::slug($slug));
        if ($post === null) {
            abort(404);
        }

        $popularPosts = $posts
            ->where('slug', '!=', $post['slug'])
            ->sortByDesc(fn (array $item): int => (int) ($item['popular_score'] ?? 0))
            ->take(5)
            ->values();
        $featuredPosts = $posts
            ->where('slug', '!=', $post['slug'])
            ->filter(fn (array $item): bool => (bool) ($item['is_featured'] ?? false))
            ->take(3)
            ->values();

        $relatedFromSlug = collect($post['related_posts'] ?? [])
            ->map(fn (string $relatedSlug) => $posts->first(fn (array $item): bool => (string) ($item['slug'] ?? '') === Str::slug($relatedSlug)))
            ->filter()
            ->values();
        $relatedAuto = $posts
            ->where('slug', '!=', $post['slug'])
            ->filter(function (array $item) use ($post): bool {
                $sameCategory = count(array_intersect($item['categories'] ?? [], $post['categories'] ?? [])) > 0;
                $sameTag = count(array_intersect($item['tags'] ?? [], $post['tags'] ?? [])) > 0;

                return $sameCategory || $sameTag;
            })
            ->values();
        $relatedPosts = $relatedFromSlug->merge($relatedAuto)->unique(fn (array $item): string => (string) ($item['slug'] ?? ''))->take(4)->values();

        $approvedComments = $this->blogCommentsBySlug((string) $post['slug'])
            ->filter(fn (array $comment): bool => (string) ($comment['status'] ?? 'pending') === 'approved')
            ->values();

        $canonicalUrl = route('blog.show', ['slug' => $post['slug']]);
        $seoTitle = (string) ($post['seo_title'] !== '' ? $post['seo_title'] : $post['title'].' · '.$cmsGeneral['site_title']);
        $seoDescription = (string) ($post['seo_description'] !== '' ? $post['seo_description'] : ($post['excerpt'] !== '' ? $post['excerpt'] : Str::limit(strip_tags((string) $post['content_html']), 180)));
        $ogTitle = (string) ($post['og_title'] !== '' ? $post['og_title'] : $seoTitle);
        $ogDescription = (string) ($post['og_description'] !== '' ? $post['og_description'] : $seoDescription);
        $ogImage = (string) ($post['og_image'] !== '' ? $post['og_image'] : $post['featured_image']);

        return view('public-blog-detail', [
            'cmsGeneral' => $cmsGeneral,
            'headerConfig' => $headerConfig,
            'isInnerPage' => true,
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'featuredPosts' => $featuredPosts,
            'popularPosts' => $popularPosts,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'canonicalUrl' => $canonicalUrl,
            'ogTitle' => $ogTitle,
            'ogDescription' => $ogDescription,
            'ogImage' => $ogImage,
            'commentsEnabled' => ($payload['engagement']['comments_enabled'] ?? true) && (bool) ($post['comments_enabled'] ?? true),
            'approvedComments' => $approvedComments,
        ]);
    }

    public function blogCommentStore(Request $request, string $slug): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $settings = $this->settingMap();
        $blogPayload = $this->decodeCmsBlogNewsPayload((string) ($settings['cms_blog_news_payload'] ?? ''));
        $posts = $this->normalizePublicBlogPosts($blogPayload['posts'] ?? []);
        $post = $posts->first(fn (array $item): bool => (string) ($item['slug'] ?? '') === Str::slug($slug));
        if ($post === null) {
            return redirect()->route('blog.index');
        }

        $commentsEnabled = ($blogPayload['engagement']['comments_enabled'] ?? true) && (bool) ($post['comments_enabled'] ?? true);
        if (! $commentsEnabled) {
            return redirect()
                ->route('blog.show', ['slug' => $post['slug']])
                ->with('success', 'Komentar untuk artikel ini sedang dinonaktifkan.');
        }

        $rawComments = (string) (LandingSetting::query()->where('key', 'cms_blog_news_comments')->value('value') ?? '[]');
        $comments = json_decode($rawComments, true);
        if (! is_array($comments)) {
            $comments = [];
        }
        $comments[] = [
            'id' => (string) Str::uuid(),
            'post_slug' => (string) $post['slug'],
            'post_title' => (string) $post['title'],
            'name' => (string) $payload['name'],
            'email' => (string) $payload['email'],
            'comment' => (string) $payload['comment'],
            'status' => 'pending',
            'admin_reply' => '',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_blog_news_comments'],
            ['value' => json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()
            ->route('blog.show', ['slug' => $post['slug']])
            ->with('success', 'Komentar berhasil dikirim dan menunggu moderasi.');
    }

    public function blogLike(Request $request, string $slug): JsonResponse
    {
        $updatedPost = $this->incrementBlogPostMetric((string) Str::slug($slug), 'like_count', 1);
        if ($updatedPost === null) {
            return response()->json(['message' => 'Post tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Like tercatat.',
            'like_count' => (int) ($updatedPost['like_count'] ?? 0),
            'popular_score' => (int) ($updatedPost['popular_score'] ?? 0),
        ]);
    }

    public function blogSitemap(Request $request)
    {
        $settings = $this->settingMap();
        $posts = $this->normalizePublicBlogPosts($this->decodeCmsBlogNewsPayload((string) ($settings['cms_blog_news_payload'] ?? ''))['posts'] ?? []);
        $urls = collect([
            [
                'loc' => route('blog.index'),
                'lastmod' => now()->toDateString(),
            ],
        ])->merge(
            $posts->map(function (array $post): array {
                return [
                    'loc' => route('blog.show', ['slug' => $post['slug']]),
                    'lastmod' => $post['published_at'] !== '' ? date('Y-m-d', strtotime((string) $post['published_at'])) : now()->toDateString(),
                ];
            })
        );

        $xml = view('sitemaps.blog', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function customPage(Request $request, string $slug): View|RedirectResponse
    {
        $settings = $this->settingMap();
        $cmsGeneral = $this->cmsGeneralMap($settings);
        $cmsSocialLinks = $this->cmsSocialLinks($cmsGeneral);
        $headerConfig = $this->headerConfigMap($settings, $cmsGeneral, $cmsSocialLinks);
        $pagesPayload = $this->decodeCmsPagesPayload((string) ($settings['cms_pages_payload'] ?? ''));

        if ($cmsGeneral['maintenance_mode']) {
            return view('maintenance-public', [
                'cmsGeneral' => $cmsGeneral,
                'cmsSocialLinks' => $cmsSocialLinks,
                'headerConfig' => $headerConfig,
                'isInnerPage' => true,
            ]);
        }

        $customPage = $this->findCustomPageBySlug($pagesPayload, $slug);
        if ($customPage === null) {
            abort(404);
        }

        $publishAt = (string) ($customPage['publish_at'] ?? $pagesPayload['publish_at'] ?? '');
        if ($publishAt !== '' && strtotime($publishAt) !== false && strtotime($publishAt) > time()) {
            abort(404);
        }

        $globalProtectionEnabled = ($pagesPayload['password_protection_enabled'] ?? false) === true;
        $globalPassword = (string) ($pagesPayload['preview_password'] ?? '');
        $pageProtectionEnabled = ($customPage['password_protection_enabled'] ?? false) === true;
        $pagePassword = (string) ($customPage['preview_password'] ?? '');

        $needProtection = ($pageProtectionEnabled && $pagePassword !== '') || ($globalProtectionEnabled && $globalPassword !== '');
        if ($needProtection) {
            $expectedPassword = $pageProtectionEnabled && $pagePassword !== '' ? $pagePassword : $globalPassword;
            $sessionKey = 'cms_pages_preview_password_'.Str::slug($slug);
            $inputPassword = (string) $request->query('preview_password', $request->session()->get($sessionKey, ''));
            if ($expectedPassword !== '' && $inputPassword !== $expectedPassword) {
                return view('public-page-locked', [
                    'cmsGeneral' => $cmsGeneral,
                    'headerConfig' => $headerConfig,
                    'isInnerPage' => true,
                    'unlockParam' => 'preview_password',
                ]);
            }
            $request->session()->put($sessionKey, $inputPassword);
        }

        $customPageData = $this->buildCustomPublicPageData($customPage, $pagesPayload);

        return view('public-cms-page', [
            'cmsGeneral' => $cmsGeneral,
            'headerConfig' => $headerConfig,
            'isInnerPage' => true,
            'pageKey' => 'custom-page',
            'pageData' => $customPageData,
            'customCss' => (string) ($pagesPayload['custom_css'] ?? ''),
        ]);
    }

    private function renderCmsStaticPage(Request $request, string $pageKey): View|RedirectResponse
    {
        $settings = $this->settingMap();
        $cmsGeneral = $this->cmsGeneralMap($settings);
        $cmsSocialLinks = $this->cmsSocialLinks($cmsGeneral);
        $headerConfig = $this->headerConfigMap($settings, $cmsGeneral, $cmsSocialLinks);
        $pagesPayload = $this->decodeCmsPagesPayload((string) ($settings['cms_pages_payload'] ?? ''));

        if ($cmsGeneral['maintenance_mode']) {
            return view('maintenance-public', [
                'cmsGeneral' => $cmsGeneral,
                'cmsSocialLinks' => $cmsSocialLinks,
                'headerConfig' => $headerConfig,
                'isInnerPage' => true,
            ]);
        }

        if (($pagesPayload['password_protection_enabled'] ?? false) === true) {
            $inputPassword = (string) $request->query('preview_password', $request->session()->get('cms_pages_preview_password', ''));
            $expectedPassword = (string) ($pagesPayload['preview_password'] ?? '');
            if ($expectedPassword !== '' && $inputPassword !== $expectedPassword) {
                return view('public-page-locked', [
                    'cmsGeneral' => $cmsGeneral,
                    'headerConfig' => $headerConfig,
                    'isInnerPage' => true,
                    'unlockParam' => 'preview_password',
                ]);
            }
            $request->session()->put('cms_pages_preview_password', $inputPassword);
        }

        $pageData = $this->buildPublicPageData($pageKey, $pagesPayload);

        return view('public-cms-page', [
            'cmsGeneral' => $cmsGeneral,
            'headerConfig' => $headerConfig,
            'isInnerPage' => true,
            'pageKey' => $pageKey,
            'pageData' => $pageData,
            'customCss' => (string) ($pagesPayload['custom_css'] ?? ''),
        ]);
    }

    private function decodeCmsPagesPayload(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
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

    private function decodeCmsPortfolioPayload(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function decodeCmsBlogNewsPayload(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function normalizePublicBlogPosts(array $posts): Collection
    {
        return collect($posts)
            ->filter(fn ($post): bool => is_array($post))
            ->map(function (array $post): array {
                $slug = Str::slug((string) ($post['slug'] ?? $post['title'] ?? ''));
                $status = (string) ($post['status'] ?? 'draft');
                $publishedAt = (string) ($post['published_at'] ?? '');
                $publishTimestamp = strtotime($publishedAt) ?: 0;
                $mediaType = (string) ($post['media_type'] ?? 'image');
                if (! in_array($mediaType, ['image', 'video'], true)) {
                    $mediaType = 'image';
                }
                $featuredImage = $this->resolveBlogAssetUrl((string) ($post['featured_image'] ?? ''));
                $mediaVideoUrl = $this->resolveBlogAssetUrl((string) ($post['media_video_url'] ?? ''));
                $mediaEmbedUrl = $this->toEmbeddableVideoUrl($mediaVideoUrl);
                if ($featuredImage === '' && $mediaType === 'video') {
                    $featuredImage = $this->videoThumbnailFallback($mediaVideoUrl, $mediaEmbedUrl);
                }
                $ogImage = $this->resolveBlogAssetUrl((string) ($post['og_image'] ?? ''));

                return [
                    'title' => (string) ($post['title'] ?? ''),
                    'slug' => $slug,
                    'media_type' => $mediaType,
                    'featured_image' => $featuredImage,
                    'media_video_url' => $mediaVideoUrl,
                    'media_embed_url' => $mediaEmbedUrl,
                    'content_html' => (string) ($post['content_html'] ?? ''),
                    'excerpt' => (string) ($post['excerpt'] ?? ''),
                    'author' => (string) ($post['author'] ?? 'Editorial PHN'),
                    'published_at' => $publishedAt,
                    'publish_timestamp' => $publishTimestamp,
                    'categories' => collect($post['categories'] ?? [])->map(fn ($item): string => (string) $item)->filter()->values()->all(),
                    'tags' => collect($post['tags'] ?? [])->map(fn ($item): string => (string) $item)->filter()->values()->all(),
                    'seo_title' => (string) ($post['seo_title'] ?? ''),
                    'seo_description' => (string) ($post['seo_description'] ?? ''),
                    'canonical_url' => (string) ($post['canonical_url'] ?? ''),
                    'og_title' => (string) ($post['og_title'] ?? ''),
                    'og_description' => (string) ($post['og_description'] ?? ''),
                    'og_image' => $ogImage,
                    'comments_enabled' => (bool) ($post['comments_enabled'] ?? true),
                    'pingbacks_enabled' => (bool) ($post['pingbacks_enabled'] ?? false),
                    'status' => $status,
                    'reading_time_minutes' => max(1, (int) ($post['reading_time_minutes'] ?? 4)),
                    'is_featured' => (bool) ($post['is_featured'] ?? false),
                    'is_popular' => (bool) ($post['is_popular'] ?? false),
                    'related_posts' => collect($post['related_posts'] ?? [])->map(fn ($item): string => (string) $item)->filter()->values()->all(),
                    'view_count' => max(0, (int) ($post['view_count'] ?? 0)),
                    'like_count' => max(0, (int) ($post['like_count'] ?? 0)),
                    'comment_count' => max(0, (int) ($post['comment_count'] ?? 0)),
                    'popular_score' => $this->calculateBlogPopularScore(
                        max(0, (int) ($post['view_count'] ?? 0)),
                        max(0, (int) ($post['like_count'] ?? 0)),
                        max(0, (int) ($post['comment_count'] ?? 0))
                    ),
                ];
            })
            ->filter(function (array $post): bool {
                if ($post['title'] === '' || $post['slug'] === '') {
                    return false;
                }
                if ($post['status'] !== 'published') {
                    return false;
                }
                if ($post['publish_timestamp'] !== 0 && $post['publish_timestamp'] > time()) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    private function calculateBlogPopularScore(int $views, int $likes, int $comments): int
    {
        return $views + ($likes * 3) + ($comments * 5);
    }

    private function incrementBlogPostMetric(string $slug, string $metric, int $amount): ?array
    {
        if (! in_array($metric, ['view_count', 'like_count', 'comment_count'], true)) {
            return null;
        }

        $settings = $this->settingMap();
        $payload = $this->decodeCmsBlogNewsPayload((string) ($settings['cms_blog_news_payload'] ?? ''));
        $posts = is_array($payload['posts'] ?? null) ? $payload['posts'] : [];
        $found = null;

        foreach ($posts as $index => $post) {
            if (! is_array($post)) {
                continue;
            }
            $postSlug = Str::slug((string) ($post['slug'] ?? $post['title'] ?? ''));
            if ($postSlug !== $slug) {
                continue;
            }
            $views = max(0, (int) ($post['view_count'] ?? 0));
            $likes = max(0, (int) ($post['like_count'] ?? 0));
            $comments = max(0, (int) ($post['comment_count'] ?? 0));
            if ($metric === 'view_count') {
                $views += $amount;
            } elseif ($metric === 'like_count') {
                $likes += $amount;
            } else {
                $comments += $amount;
            }
            $posts[$index]['view_count'] = $views;
            $posts[$index]['like_count'] = $likes;
            $posts[$index]['comment_count'] = $comments;
            $posts[$index]['popular_score'] = $this->calculateBlogPopularScore($views, $likes, $comments);
            $found = $posts[$index];
            break;
        }
        if ($found === null) {
            return null;
        }

        $payload['posts'] = $posts;
        $payload['updated_at'] = now()->toDateTimeString();
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_blog_news_payload'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return $found;
    }

    private function blogCommentsBySlug(string $slug): Collection
    {
        $rawComments = (string) (LandingSetting::query()->where('key', 'cms_blog_news_comments')->value('value') ?? '[]');
        $comments = json_decode($rawComments, true);
        if (! is_array($comments)) {
            $comments = [];
        }

        return collect($comments)
            ->filter(fn ($comment): bool => is_array($comment))
            ->filter(fn (array $comment): bool => Str::slug((string) ($comment['post_slug'] ?? '')) === Str::slug($slug))
            ->sortByDesc(fn (array $comment): int => strtotime((string) ($comment['created_at'] ?? '1970-01-01')) ?: 0)
            ->values();
    }

    private function resolveBlogAssetUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (Str::startsWith($path, ['http://', 'https://', '/storage/'])) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    private function toEmbeddableVideoUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }
        if (Str::contains($url, ['youtube.com/watch?v=', 'youtu.be/'])) {
            $videoId = '';
            if (Str::contains($url, 'watch?v=')) {
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $queryParams);
                $videoId = (string) ($queryParams['v'] ?? '');
            } else {
                $path = (string) parse_url($url, PHP_URL_PATH);
                $videoId = trim($path, '/');
            }
            if ($videoId !== '') {
                return 'https://www.youtube.com/embed/'.$videoId;
            }
        }
        if (Str::contains($url, 'vimeo.com/')) {
            $path = (string) parse_url($url, PHP_URL_PATH);
            $videoId = trim($path, '/');
            if ($videoId !== '') {
                return 'https://player.vimeo.com/video/'.$videoId;
            }
        }

        return $url;
    }

    private function videoThumbnailFallback(string $videoUrl, string $embedUrl): string
    {
        $youtubeId = $this->extractYouTubeVideoId($videoUrl);
        if ($youtubeId === '' && $embedUrl !== '') {
            $youtubeId = $this->extractYouTubeVideoId($embedUrl);
        }
        if ($youtubeId !== '') {
            return 'https://img.youtube.com/vi/'.$youtubeId.'/hqdefault.jpg';
        }

        $vimeoId = $this->extractVimeoVideoId($videoUrl);
        if ($vimeoId === '' && $embedUrl !== '') {
            $vimeoId = $this->extractVimeoVideoId($embedUrl);
        }
        if ($vimeoId !== '') {
            return 'https://vumbnail.com/'.$vimeoId.'.jpg';
        }

        return '';
    }

    private function extractYouTubeVideoId(string $url): string
    {
        if ($url === '') {
            return '';
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $matches) === 1) {
            return (string) ($matches[1] ?? '');
        }

        return '';
    }

    private function extractVimeoVideoId(string $url): string
    {
        if ($url === '') {
            return '';
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches) === 1) {
            return (string) ($matches[1] ?? '');
        }

        return '';
    }

    private function safeArrayOfScalars(array $items): Collection
    {
        return collect($items)
            ->filter(fn ($item): bool => is_array($item) || is_scalar($item))
            ->values();
    }

    private function mapPortfolioProjectsToLandingItems(Collection $projects): array
    {
        return $projects
            ->map(function (array $project): array {
                $category = (string) ($project['category'] ?? 'General');
                $videoEmbed = (string) ($project['video_embed'] ?? '');
                $featuredImage = (string) ($project['featured_image'] ?? '');
                $mediaType = $videoEmbed !== '' ? 'video' : 'photo';
                $mediaUrl = $videoEmbed !== '' ? $videoEmbed : $featuredImage;
                $thumbnailUrl = $featuredImage !== '' ? $featuredImage : $mediaUrl;
                if ($mediaUrl !== '' && ! Str::startsWith($mediaUrl, ['http://', 'https://']) && ! Str::startsWith($mediaUrl, '/storage/')) {
                    $mediaUrl = '/storage/'.ltrim($mediaUrl, '/');
                }
                if ($thumbnailUrl !== '' && ! Str::startsWith($thumbnailUrl, ['http://', 'https://']) && ! Str::startsWith($thumbnailUrl, '/storage/')) {
                    $thumbnailUrl = '/storage/'.ltrim($thumbnailUrl, '/');
                }

                return [
                    'title' => (string) ($project['title'] ?? 'Untitled Project'),
                    'description' => (string) ($project['description'] ?? ''),
                    'category' => $category,
                    'media_type' => $mediaType,
                    'source_type' => Str::startsWith($videoEmbed, ['http://', 'https://']) ? 'url' : 'upload',
                    'media_url' => $mediaUrl,
                    'thumbnail_url' => $thumbnailUrl,
                    'category_slug' => Str::slug($category) ?: 'general',
                    'media_slug' => $mediaType === 'photo' ? 'photo' : 'video',
                ];
            })
            ->values()
            ->all();
    }

    private function buildPublicPageData(string $pageKey, array $payload): array
    {
        $about = $payload['about'] ?? [];
        $services = $payload['services'] ?? [];
        $faq = $payload['faq'] ?? [];
        $legal = $payload['legal'] ?? [];

        if ($pageKey === 'about') {
            return [
                'title' => 'About Us',
                'subtitle' => 'Company story, vision mission, awards, dan daftar klien.',
                'timeline' => is_array($about['story_timeline'] ?? null) ? $about['story_timeline'] : [],
                'vision' => (string) ($about['vision'] ?? ''),
                'mission' => (string) ($about['mission'] ?? ''),
                'awards' => is_array($about['awards'] ?? null) ? $about['awards'] : [],
                'clients' => is_array($about['clients'] ?? null) ? $about['clients'] : [],
            ];
        }
        if ($pageKey === 'services') {
            return [
                'title' => 'Services',
                'subtitle' => 'Kategori layanan produksi lengkap dengan deskripsi dan pricing range.',
                'categories' => is_array($services['categories'] ?? null) ? $services['categories'] : [],
            ];
        }
        if ($pageKey === 'faq') {
            return [
                'title' => 'Frequently Asked Questions',
                'subtitle' => 'Pertanyaan umum tentang produksi, timeline, pricing, dan usage.',
                'categories' => is_array($faq['categories'] ?? null) ? $faq['categories'] : [],
                'items' => is_array($faq['items'] ?? null) ? $faq['items'] : [],
            ];
        }
        if ($pageKey === 'privacy-policy') {
            return [
                'title' => 'Privacy Policy',
                'subtitle' => 'Kebijakan privasi terbaru.',
                'html' => (string) ($legal['privacy_html'] ?? ''),
                'version_history' => is_array($legal['version_history'] ?? null) ? $legal['version_history'] : [],
            ];
        }

        return [
            'title' => 'Terms of Service',
            'subtitle' => 'Syarat dan ketentuan layanan.',
            'html' => (string) ($legal['terms_html'] ?? ''),
            'version_history' => is_array($legal['version_history'] ?? null) ? $legal['version_history'] : [],
        ];
    }

    private function findCustomPageBySlug(array $payload, string $slug): ?array
    {
        $targetSlug = Str::slug($slug);
        $pages = $payload['custom_pages'] ?? [];
        if (! is_array($pages)) {
            return null;
        }

        foreach ($pages as $page) {
            if (! is_array($page)) {
                continue;
            }
            $pageSlug = Str::slug((string) ($page['slug'] ?? $page['title'] ?? ''));
            if ($pageSlug !== '' && $pageSlug === $targetSlug) {
                return $page;
            }
        }

        return null;
    }

    private function buildCustomPublicPageData(array $customPage, array $payload): array
    {
        $layout = (string) ($customPage['layout'] ?? $payload['custom_page_layout'] ?? 'full-width');
        if (! in_array($layout, ['full-width', 'sidebar'], true)) {
            $layout = 'full-width';
        }

        $sections = $customPage['sections'] ?? [];
        if (! is_array($sections)) {
            $sections = [];
        }

        return [
            'title' => (string) ($customPage['title'] ?? 'Custom Page'),
            'subtitle' => (string) ($customPage['subtitle'] ?? ''),
            'layout' => $layout,
            'content_html' => (string) ($customPage['content_html'] ?? ''),
            'sidebar_html' => (string) ($customPage['sidebar_html'] ?? ''),
            'sections' => $sections,
        ];
    }

    private function settingMap(): array
    {
        try {
            return LandingSetting::query()->pluck('value', 'key')->all();
        } catch (QueryException) {
            return [];
        }
    }

    private function decodeJsonSetting(array $settings, string $key, array $default): array
    {
        $raw = (string) ($settings[$key] ?? '');
        if ($raw === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }

    private function cmsGeneralMap(array $settings): array
    {
        return [
            'site_title' => (string) ($settings['cms_site_title'] ?? 'PRODUCTION HOUSE NUSANTARA'),
            'tagline' => (string) ($settings['cms_tagline'] ?? 'Professional Film & Content Production'),
            'site_description' => (string) ($settings['cms_site_description'] ?? ''),
            'keywords' => (string) ($settings['cms_keywords'] ?? ''),
            'contact_email' => (string) ($settings['cms_contact_email'] ?? ''),
            'contact_phone' => (string) ($settings['cms_contact_phone'] ?? ''),
            'office_address' => (string) ($settings['cms_office_address'] ?? ''),
            'map_query' => (string) ($settings['cms_map_query'] ?? ''),
            'operational_hours' => (string) ($settings['cms_operational_hours'] ?? ''),
            'social_instagram' => (string) ($settings['cms_social_instagram'] ?? ''),
            'social_youtube' => (string) ($settings['cms_social_youtube'] ?? ''),
            'social_vimeo' => (string) ($settings['cms_social_vimeo'] ?? ''),
            'social_tiktok' => (string) ($settings['cms_social_tiktok'] ?? ''),
            'social_linkedin' => (string) ($settings['cms_social_linkedin'] ?? ''),
            'social_facebook' => (string) ($settings['cms_social_facebook'] ?? ''),
            'favicon_32_url' => $this->resolveStorageAssetUrl((string) ($settings['cms_logo_favicon_32'] ?? '')),
            'apple_touch_icon_url' => $this->resolveStorageAssetUrl((string) ($settings['cms_logo_apple_touch'] ?? '')),
            'analytics_code' => (string) ($settings['cms_analytics_code'] ?? ''),
            'seo_default_og' => (string) ($settings['cms_seo_default_og'] ?? ''),
            'seo_schema_markup' => (string) ($settings['cms_seo_schema_markup'] ?? ''),
            'language_mode' => (string) ($settings['cms_language_mode'] ?? 'id'),
            'maintenance_mode' => ($settings['cms_maintenance_mode'] ?? '0') === '1',
            'cms_engine' => (string) ($settings['cms_engine'] ?? 'native'),
            'directus_url' => (string) ($settings['cms_directus_url'] ?? ''),
            'directus_project' => (string) ($settings['cms_directus_project'] ?? ''),
            'directus_collection' => (string) ($settings['cms_directus_collection'] ?? ''),
        ];
    }

    private function resolveStorageAssetUrl(string $path): string
    {
        return $path !== '' ? asset('storage/'.ltrim($path, '/')) : '';
    }

    private function storagePathToUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (Str::startsWith($path, ['http://', 'https://', '/storage/'])) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    private function decodeSliderVideoMetaMap(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function headerConfigMap(array $settings, array $cmsGeneral, array $cmsSocialLinks): array
    {
        $pagesPayload = $this->decodeCmsPagesPayload((string) ($settings['cms_pages_payload'] ?? ''));
        $sectionVisibility = $this->sectionVisibilityMap($pagesPayload['section_visibility'] ?? null);
        $navItems = $this->decodeJsonSetting($settings, 'cms_header_navigation_items', $this->defaultHeaderNavigation());
        $navItems = $this->ensureBlogNavItem($navItems);
        $navItems = $this->ensureStoreNavItem($navItems);
        $navItems = $this->filterHeaderItemsBySectionVisibility($navItems, $sectionVisibility);
        $mobileItems = $this->decodeJsonSetting($settings, 'cms_header_mobile_menu_items', $navItems);
        $mobileItems = $this->ensureBlogNavItem($mobileItems);
        $mobileItems = $this->ensureStoreNavItem($mobileItems);
        $mobileItems = $this->filterHeaderItemsBySectionVisibility($mobileItems, $sectionVisibility);
        $stickyVariant = (string) ($settings['cms_logo_sticky_variant'] ?? 'primary');
        $logoPrimary = $this->resolveStorageAssetUrl((string) ($settings['cms_logo_primary'] ?? ''));
        $logoSecondaryLight = $this->resolveStorageAssetUrl((string) ($settings['cms_logo_secondary_light'] ?? ''));
        $logoSecondaryDark = $this->resolveStorageAssetUrl((string) ($settings['cms_logo_secondary_dark'] ?? ''));
        $logoStickyCustom = $this->resolveStorageAssetUrl((string) ($settings['cms_logo_sticky'] ?? ''));

        $stickyLogoMap = [
            'primary' => [$logoPrimary, $logoSecondaryLight, $logoSecondaryDark, $logoStickyCustom],
            'secondary-light' => [$logoSecondaryLight, $logoPrimary, $logoSecondaryDark, $logoStickyCustom],
            'secondary-dark' => [$logoSecondaryDark, $logoPrimary, $logoSecondaryLight, $logoStickyCustom],
            'sticky' => [$logoStickyCustom, $logoPrimary, $logoSecondaryDark, $logoSecondaryLight],
        ];

        $resolveFirstLogo = static function (array $candidates): string {
            foreach ($candidates as $logo) {
                if ($logo !== '') {
                    return $logo;
                }
            }

            return '';
        };

        $rawMegaKeys = (string) ($settings['cms_header_mega_menu_keys'] ?? '');
        $megaMenuKeys = collect(explode(',', $rawMegaKeys))
            ->map(fn (string $item): string => Str::lower(trim($item)))
            ->filter()
            ->values()
            ->all();

        return [
            'layout' => (string) ($settings['cms_header_layout'] ?? 'transparent'),
            'sticky_on_scroll' => ($settings['cms_header_sticky_on_scroll'] ?? '1') === '1',
            'shrink_on_scroll' => ($settings['cms_header_shrink_on_scroll'] ?? '1') === '1',
            'topbar_enabled' => ($settings['cms_header_topbar_enabled'] ?? '0') === '1',
            'topbar_phone' => (string) ($settings['cms_header_topbar_phone'] ?? ($cmsGeneral['contact_phone'] ?? '')),
            'topbar_email' => (string) ($settings['cms_header_topbar_email'] ?? ($cmsGeneral['contact_email'] ?? '')),
            'topbar_social_enabled' => ($settings['cms_header_topbar_social_enabled'] ?? '0') === '1',
            'topbar_social_links' => $cmsSocialLinks,
            'topbar_language_switcher' => ($settings['cms_header_topbar_language_switcher'] ?? '0') === '1',
            'topbar_cta_text' => (string) ($settings['cms_header_topbar_cta_text'] ?? ''),
            'topbar_cta_link' => (string) ($settings['cms_header_topbar_cta_link'] ?? ''),
            'nav_items' => $this->normalizeHeaderItems($navItems, 1),
            'mega_menu_keys' => $megaMenuKeys,
            'mobile_hamburger_style' => (string) ($settings['cms_header_mobile_hamburger_style'] ?? 'classic'),
            'mobile_menu_mode' => (string) ($settings['cms_header_mobile_menu_mode'] ?? 'drawer'),
            'mobile_menu_items' => $this->normalizeHeaderItems($mobileItems, 1),
            'cta_text' => (string) ($settings['cms_header_cta_text'] ?? 'Start Project'),
            'cta_link' => $this->resolveHeaderCtaLink((string) ($settings['cms_header_cta_link'] ?? '#contact'), $sectionVisibility),
            'cta_color' => (string) ($settings['cms_header_cta_color'] ?? 'gold'),
            'cta_show_home' => ($settings['cms_header_cta_show_home'] ?? '1') === '1',
            'cta_show_inner' => ($settings['cms_header_cta_show_inner'] ?? '1') === '1',
            'logo_primary' => $resolveFirstLogo([$logoPrimary, $logoSecondaryLight, $logoSecondaryDark, $logoStickyCustom]),
            'logo_sticky' => $resolveFirstLogo($stickyLogoMap[$stickyVariant] ?? $stickyLogoMap['primary']),
        ];
    }

    private function normalizeHeaderItems(array $items, int $depth): array
    {
        if ($depth > 3) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $item) use ($depth): array {
                $children = [];
                if (isset($item['children']) && is_array($item['children'])) {
                    $children = $this->normalizeHeaderItems($item['children'], $depth + 1);
                }

                $type = (string) ($item['type'] ?? 'internal');
                if (! in_array($type, ['internal', 'external', 'mega'], true)) {
                    $type = 'internal';
                }

                $target = (string) ($item['target'] ?? '_self');
                if (! in_array($target, ['_self', '_blank'], true)) {
                    $target = '_self';
                }

                return [
                    'title' => (string) ($item['title'] ?? 'Untitled'),
                    'url' => (string) ($item['url'] ?? '#'),
                    'type' => $type,
                    'target' => $target,
                    'children' => $children,
                ];
            })
            ->values()
            ->all();
    }

    private function defaultHeaderNavigation(): array
    {
        return [
            ['title' => 'Home', 'url' => '#home', 'type' => 'internal', 'target' => '_self', 'children' => []],
            ['title' => 'Services', 'url' => '#services', 'type' => 'internal', 'target' => '_self', 'children' => []],
            ['title' => 'Portfolio', 'url' => '#portfolio', 'type' => 'mega', 'target' => '_self', 'children' => []],
            ['title' => 'Blog', 'url' => '/blog', 'type' => 'internal', 'target' => '_self', 'children' => []],
            ['title' => 'Store', 'url' => '/store', 'type' => 'internal', 'target' => '_self', 'children' => []],
            ['title' => 'Testimonials', 'url' => '#testimonials', 'type' => 'internal', 'target' => '_self', 'children' => []],
            ['title' => 'Team', 'url' => '#team', 'type' => 'internal', 'target' => '_self', 'children' => []],
            ['title' => 'Contact', 'url' => '#contact', 'type' => 'internal', 'target' => '_self', 'children' => []],
        ];
    }

    private function ensureBlogNavItem(array $items): array
    {
        $hasBlog = collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->contains(function (array $item): bool {
                $title = Str::lower(trim((string) ($item['title'] ?? '')));
                $url = trim((string) ($item['url'] ?? ''));

                return $title === 'blog' || $url === '/blog';
            });
        if ($hasBlog) {
            return $items;
        }

        $items[] = ['title' => 'Blog', 'url' => '/blog', 'type' => 'internal', 'target' => '_self', 'children' => []];

        return $items;
    }

    private function ensureStoreNavItem(array $items): array
    {
        $hasStore = collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->contains(function (array $item): bool {
                $title = Str::lower(trim((string) ($item['title'] ?? '')));
                $url = trim((string) ($item['url'] ?? ''));

                return $title === 'store' || $url === '/store';
            });
        if ($hasStore) {
            return $items;
        }

        $items[] = ['title' => 'Store', 'url' => '/store', 'type' => 'internal', 'target' => '_self', 'children' => []];

        return $items;
    }

    private function filterHeaderItemsBySectionVisibility(array $items, array $sectionVisibility): array
    {
        return collect($items)
            ->filter(fn ($item): bool => is_array($item))
            ->map(function (array $item) use ($sectionVisibility): array {
                $children = is_array($item['children'] ?? null) ? $this->filterHeaderItemsBySectionVisibility($item['children'], $sectionVisibility) : [];
                $item['children'] = $children;

                return $item;
            })
            ->filter(function (array $item) use ($sectionVisibility): bool {
                $anchorKey = $this->sectionKeyFromHeaderUrl((string) ($item['url'] ?? ''));
                if ($anchorKey === null) {
                    return true;
                }

                return ($sectionVisibility[$anchorKey] ?? true) === true;
            })
            ->values()
            ->all();
    }

    private function sectionKeyFromHeaderUrl(string $url): ?string
    {
        $clean = trim($url);
        if ($clean === '') {
            return null;
        }
        if (Str::startsWith($clean, ['http://', 'https://'])) {
            $path = (string) parse_url($clean, PHP_URL_PATH);
            $fragment = (string) parse_url($clean, PHP_URL_FRAGMENT);
            if ($path !== '' && $path !== '/') {
                return null;
            }
            $anchor = $fragment;
        } else {
            $anchor = ltrim(Str::after($clean, '#'), '/');
            if (! Str::contains($clean, '#')) {
                return null;
            }
        }
        $anchor = Str::lower(trim($anchor));
        if ($anchor === '') {
            return null;
        }

        $map = [
            'services' => 'services',
            'portfolio' => 'portfolio',
            'why-us' => 'why_us',
            'testimonials' => 'testimonials',
            'process' => 'process',
            'team' => 'team',
            'contact' => 'contact',
        ];

        return $map[$anchor] ?? null;
    }

    private function resolveHeaderCtaLink(string $ctaLink, array $sectionVisibility): string
    {
        $anchorKey = $this->sectionKeyFromHeaderUrl($ctaLink);
        if ($anchorKey === null) {
            return $ctaLink;
        }
        if (($sectionVisibility[$anchorKey] ?? true) === true) {
            return $ctaLink;
        }

        return '#home';
    }

    private function cmsSocialLinks(array $cmsGeneral): array
    {
        $items = [
            ['icon' => 'instagram', 'name' => 'Instagram', 'url' => $cmsGeneral['social_instagram'] ?? ''],
            ['icon' => 'youtube', 'name' => 'YouTube', 'url' => $cmsGeneral['social_youtube'] ?? ''],
            ['icon' => 'vimeo', 'name' => 'Vimeo', 'url' => $cmsGeneral['social_vimeo'] ?? ''],
            ['icon' => 'tiktok', 'name' => 'TikTok', 'url' => $cmsGeneral['social_tiktok'] ?? ''],
            ['icon' => 'linkedin', 'name' => 'LinkedIn', 'url' => $cmsGeneral['social_linkedin'] ?? ''],
            ['icon' => 'facebook', 'name' => 'Facebook', 'url' => $cmsGeneral['social_facebook'] ?? ''],
        ];

        return collect($items)
            ->filter(fn (array $item): bool => trim((string) $item['url']) !== '')
            ->values()
            ->all();
    }

    private function toWhatsappUrl(string $rawPhone): ?string
    {
        $digits = preg_replace('/\D+/', '', $rawPhone);
        if ($digits === null || $digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '62') === false) {
            $digits = '62'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    private function resolveFooterLogoUrl(array $settings): string
    {
        $toAsset = static fn (string $path): string => $path !== '' ? asset('storage/'.ltrim($path, '/')) : '';

        $logos = [
            'primary' => $toAsset((string) ($settings['cms_logo_primary'] ?? '')),
            'secondary-light' => $toAsset((string) ($settings['cms_logo_secondary_light'] ?? '')),
            'secondary-dark' => $toAsset((string) ($settings['cms_logo_secondary_dark'] ?? '')),
            'monochrome' => $toAsset((string) ($settings['cms_logo_monochrome'] ?? '')),
            'footer' => $toAsset((string) ($settings['cms_logo_footer'] ?? '')),
        ];

        $variant = (string) ($settings['cms_logo_footer_variant'] ?? 'primary');
        $fallbackMap = [
            'primary' => ['primary', 'footer', 'secondary-light', 'secondary-dark', 'monochrome'],
            'secondary-light' => ['secondary-light', 'primary', 'footer', 'secondary-dark', 'monochrome'],
            'secondary-dark' => ['secondary-dark', 'primary', 'footer', 'secondary-light', 'monochrome'],
            'monochrome' => ['monochrome', 'primary', 'footer', 'secondary-dark', 'secondary-light'],
            'footer' => ['footer', 'primary', 'secondary-dark', 'secondary-light', 'monochrome'],
        ];

        $candidates = $fallbackMap[$variant] ?? $fallbackMap['primary'];
        foreach ($candidates as $key) {
            $logo = $logos[$key] ?? '';
            if ($logo !== '') {
                return $logo;
            }
        }

        return '';
    }

    private function defaultServices(): array
    {
        return [
            ['icon' => '🎬', 'title' => 'Film Production', 'description' => 'Produksi film dari konsep, script, shooting, hingga final delivery dengan standar sinematik.'],
            ['icon' => '📺', 'title' => 'Iklan & Commercial', 'description' => 'Iklan visual impact tinggi untuk digital, TVC, dan kampanye brand dengan storytelling kuat.'],
            ['icon' => '🏢', 'title' => 'Company Profile', 'description' => 'Profil perusahaan profesional yang memperkuat kepercayaan klien, investor, dan mitra bisnis.'],
            ['icon' => '🎥', 'title' => 'Dokumenter', 'description' => 'Dokumenter berjiwa Nusantara dengan riset mendalam dan visual sinematik yang autentik.'],
            ['icon' => '🎤', 'title' => 'Voice Over & Dubbing', 'description' => 'Pengisian suara dan dubbing berkarakter untuk iklan, film, dan konten digital premium.'],
            ['icon' => '🎨', 'title' => 'Motion Graphic & VFX', 'description' => 'Animasi motion graphic dan efek visual modern untuk memperkuat kualitas visual produksi.'],
            ['icon' => '📸', 'title' => 'Photography', 'description' => 'Fotografi komersial dan editorial dengan komposisi artistik untuk kebutuhan campaign brand.'],
            ['icon' => '🎞', 'title' => 'Editing & Color Grading', 'description' => 'Editing presisi dan color grading cinematic agar tone visual konsisten, kuat, dan berkelas.'],
        ];
    }

    private function defaultPortfolioItems(): array
    {
        return [
            ['title' => 'Ruang Cahaya', 'category' => 'Film', 'media_type' => 'video', 'source_type' => 'url', 'media_url' => 'https://www.youtube.com/embed/Scxs7L0vhZ4', 'social_url' => '', 'thumbnail_url' => 'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=1200&q=80', 'description' => 'Drama sinematik berlatar budaya lokal dengan pendekatan visual artistik.', 'video_url' => 'https://www.youtube.com/embed/Scxs7L0vhZ4', 'detail_url' => '#contact', 'size' => 'large'],
            ['title' => 'Glow Brand Campaign', 'category' => 'Iklan', 'media_type' => 'video', 'source_type' => 'url', 'media_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'social_url' => '', 'thumbnail_url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1200&q=80', 'description' => 'Commercial brand campaign dengan ritme cepat dan visual premium modern.', 'video_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'detail_url' => '#contact', 'size' => 'tall'],
            ['title' => 'Warisan Nusantara Frame', 'category' => 'Dokumenter', 'media_type' => 'photo', 'source_type' => 'url', 'media_url' => 'https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?auto=format&fit=crop&w=1600&q=80', 'social_url' => '', 'thumbnail_url' => 'https://images.unsplash.com/photo-1522869635100-9f4c5e86aa37?auto=format&fit=crop&w=1200&q=80', 'description' => 'Still frame dokumenter dengan color mood berkarakter.', 'video_url' => '', 'detail_url' => '#contact', 'size' => 'wide'],
            ['title' => 'Corporate Visual Still', 'category' => 'Corporate', 'media_type' => 'photo', 'source_type' => 'url', 'media_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=80', 'social_url' => '', 'thumbnail_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80', 'description' => 'Key visual corporate untuk campaign dan komunikasi brand.', 'video_url' => '', 'detail_url' => '#contact', 'size' => 'normal'],
            ['title' => 'Creator Momentum', 'category' => 'Social', 'media_type' => 'video', 'source_type' => 'social', 'media_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'social_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'thumbnail_url' => 'https://images.unsplash.com/photo-1487180144351-b8472da7d491?auto=format&fit=crop&w=1200&q=80', 'description' => 'Konten sosial dinamis untuk engagement tinggi.', 'video_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'detail_url' => '#contact', 'size' => 'normal'],
            ['title' => 'Langit Senja', 'category' => 'Film', 'media_type' => 'video', 'source_type' => 'url', 'media_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'social_url' => '', 'thumbnail_url' => 'https://images.unsplash.com/photo-1535016120720-40c646be5580?auto=format&fit=crop&w=1200&q=80', 'description' => 'Feature film pendek dengan tone dramatis dan color grading cinematic.', 'video_url' => 'https://www.youtube.com/embed/gmD7n_6SPj8', 'detail_url' => '#contact', 'size' => 'wide'],
        ];
    }

    private function defaultWhyItems(): array
    {
        return [
            ['text' => '✔ Tim kreatif senior yang paham storytelling, brand, dan target audiens.'],
            ['text' => '✔ Proses produksi end-to-end: konsep, pra-produksi, shooting, hingga final delivery.'],
            ['text' => '✔ Visual sinematik konsisten dengan standar warna, audio, dan editing profesional.'],
            ['text' => '✔ Peralatan modern dan workflow efisien untuk hasil stabil di berbagai skala project.'],
            ['text' => '✔ Riset dan creative direction berbasis objective bisnis, bukan sekadar visual bagus.'],
            ['text' => '✔ Timeline jelas, manajemen produksi rapi, dan komunikasi progres yang transparan.'],
            ['text' => '✔ Fleksibel untuk kebutuhan campaign digital, corporate, dokumenter, hingga iklan.'],
            ['text' => '✔ Revisi terarah dengan quality control ketat agar output tepat sasaran dan berkelas.'],
        ];
    }

    private function defaultTeamMembers(): array
    {
        return [
            ['photo_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=900&q=80', 'name' => 'Rizki Pradana', 'role' => 'Director', 'caption' => 'Creative Vision Lead'],
            ['photo_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=900&q=80', 'name' => 'Arif Rahman', 'role' => 'Cameraman', 'caption' => 'Visual Capture Specialist'],
            ['photo_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=80', 'name' => 'Sinta Wulan', 'role' => 'Editor', 'caption' => 'Post Production Craft'],
            ['photo_url' => 'https://images.unsplash.com/photo-1504257432389-52343af06ae3?auto=format&fit=crop&w=900&q=80', 'name' => 'Dimas Putra', 'role' => 'Script Writer', 'caption' => 'Narrative & Story Architect'],
            ['photo_url' => 'https://images.unsplash.com/photo-1542206395-9feb3edaa68d?auto=format&fit=crop&w=900&q=80', 'name' => 'Nadia Laras', 'role' => 'Drone Pilot', 'caption' => 'Aerial Cinematography'],
            ['photo_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80', 'name' => 'Fikri Hidayat', 'role' => 'Production Crew', 'caption' => 'Execution & Set Excellence'],
        ];
    }

    private function defaultTestimonialItems(): array
    {
        return [
            ['name' => 'Nadia Prameswari', 'role' => 'Brand Manager · F&B Chain', 'quote' => 'Eksekusi cepat, hasil video kampanye sangat cinematic dan conversion naik signifikan.'],
            ['name' => 'Raka Aditya', 'role' => 'Marketing Lead · Property', 'quote' => 'Alur produksi rapi dari pre-production sampai final delivery. Komunikasi tim sangat responsif.'],
            ['name' => 'Ayu Lestari', 'role' => 'Founder · Beauty Brand', 'quote' => 'Visual storytelling kuat, tone brand kami tetap konsisten di semua materi konten.'],
        ];
    }

    private function defaultProcessSteps(): array
    {
        return [
            ['icon' => '💬', 'title' => 'Konsultasi', 'description' => 'Pemetaan kebutuhan, target audiens, dan objektif konten.'],
            ['icon' => '🧠', 'title' => 'Konsep & Script', 'description' => 'Pengembangan ide kreatif, treatment, shotlist, dan naskah.'],
            ['icon' => '🎥', 'title' => 'Shooting', 'description' => 'Produksi lapangan dengan workflow teknis efisien dan terarah.'],
            ['icon' => '🖥️', 'title' => 'Editing', 'description' => 'Offline-online edit, audio polishing, color grading sinematik.'],
            ['icon' => '🚀', 'title' => 'Final Delivery', 'description' => 'Master final siap tayang untuk web, TV, dan media digital.'],
        ];
    }

    private function defaultFooterSocialLinks(): array
    {
        return [
            ['icon' => 'instagram', 'name' => 'Instagram', 'url' => 'https://instagram.com'],
            ['icon' => 'youtube', 'name' => 'YouTube', 'url' => 'https://youtube.com'],
        ];
    }
}
