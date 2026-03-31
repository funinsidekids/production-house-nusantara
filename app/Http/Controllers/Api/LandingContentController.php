<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\LandingSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LandingContentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $settings = collect($this->settingMap());
        $heroSlides = $this->heroSlides();
        $pagesPayload = $this->decodeCmsPagesPayload((string) ($settings['cms_pages_payload'] ?? ''));
        $sectionVisibility = $this->sectionVisibilityMap($pagesPayload['section_visibility'] ?? null);
        $portfolioPayload = $this->decodeCmsPortfolioPayload((string) ($settings['cms_portfolio_payload'] ?? ''));

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
            'auto_play' => ($settings['auto_play'] ?? '1') === '1',
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
            'footer_brand_name' => $settings['footer_brand_name'] ?? 'Production House Nusantara',
            'footer_address' => $settings['footer_address'] ?? "8°06'46.7\"S 112°18'40.7\"E",
            'footer_whatsapp' => $settings['footer_whatsapp'] ?? '+62 812-3456-7890',
            'footer_email' => $settings['footer_email'] ?? 'halo@productionhousenusantara.com',
            'footer_copyright' => $settings['footer_copyright'] ?? '© Production House Nusantara',
            'footer_social_links' => $this->decodeJsonSetting($settings, 'footer_social_links', $this->defaultFooterSocialLinks()),
        ];

        if (is_array($portfolioPayload['projects'] ?? null)) {
            $landingSections['portfolio_items'] = $this->mapPortfolioProjectsToLandingItems($portfolioPayload['projects']);
        }
        $landingSections['portfolio_title'] = (string) ($portfolioPayload['layout']['portfolio_title'] ?? ($settings['portfolio_title'] ?? 'INFINITE GALERY'));

        $landingSections['why_items'] = collect($landingSections['why_items'])
            ->filter(fn (array $item): bool => trim((string) ($item['text'] ?? '')) !== '')
            ->values()
            ->all();

        if (count($landingSections['why_items']) < 8) {
            $landingSections['why_items'] = $this->defaultWhyItems();
        }

        $landingSections['portfolio_items'] = collect($landingSections['portfolio_items'])
            ->map(function (array $item): array {
                $category = (string) ($item['category'] ?? 'General');
                $mediaType = (string) ($item['media_type'] ?? 'video');
                $sourceType = (string) ($item['source_type'] ?? 'url');
                $mediaUrl = (string) ($item['media_url'] ?? ($item['video_url'] ?? ($item['thumbnail_url'] ?? '')));
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

        $payload = [
            'heroSlides' => $heroSlides,
            'landingContent' => $landingContent,
            'sliderConfig' => $sliderConfig,
            'landingSections' => $landingSections,
            'sectionVisibility' => $sectionVisibility,
            'portfolioCategories' => $portfolioCategories,
            'modular' => $this->toModularPayload($heroSlides, $landingContent, $landingSections, $sectionVisibility),
            'meta' => [
                'apiVersion' => '2026-03-enterprise-v2',
                'cms' => [
                    'engine' => (string) ($settings['cms_engine'] ?? 'native'),
                    'directus' => [
                        'enabled' => (string) ($settings['cms_engine'] ?? 'native') === 'directus',
                        'url' => (string) ($settings['cms_directus_url'] ?? ''),
                        'project' => (string) ($settings['cms_directus_project'] ?? ''),
                        'collection' => (string) ($settings['cms_directus_collection'] ?? ''),
                    ],
                ],
                'gateway' => [
                    'ready' => true,
                    'authHeaderForwarded' => $request->bearerToken() ? true : false,
                    'requestedWith' => (string) $request->header('x-requested-with', ''),
                ],
            ],
        ];

        return response()
            ->json($payload)
            ->withHeaders([
                'X-API-Version' => '2026-03-enterprise-v2',
                'X-Gateway-Ready' => 'true',
                'Vary' => 'Authorization, X-Requested-With, Origin',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    private function settingMap(): array
    {
        try {
            return LandingSetting::query()->pluck('value', 'key')->all();
        } catch (QueryException) {
            return [];
        }
    }

    private function heroSlides(): array
    {
        try {
            $heroSlides = HeroSlide::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get([
                    'id',
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
                    'video_url' => 'https://videos.pexels.com/video-files/853890/853890-hd_1920_1080_25fps.mp4',
                    'cta_text' => null,
                    'cta_url' => null,
                    'sort_order' => 1,
                    'duration_seconds' => 7,
                    'overlay_opacity' => 0.78,
                ],
                (object) [
                    'id' => null,
                    'video_url' => 'https://videos.pexels.com/video-files/3129957/3129957-hd_1920_1080_25fps.mp4',
                    'cta_text' => null,
                    'cta_url' => null,
                    'sort_order' => 2,
                    'duration_seconds' => 7,
                    'overlay_opacity' => 0.78,
                ],
            ]);
        }

        return $heroSlides->map(function ($slide) {
            if (! empty($slide->video_url) && ! Str::startsWith($slide->video_url, ['http://', 'https://'])) {
                $slide->video_url = '/storage/'.ltrim((string) $slide->video_url, '/');
            }

            return (array) $slide;
        })->values()->all();
    }

    private function decodeJsonSetting(array|Collection $settings, string $key, array $default): array
    {
        $raw = (string) (is_array($settings) ? ($settings[$key] ?? '') : $settings->get($key, ''));
        if ($raw === '') {
            return $default;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }

    private function decodeCmsPortfolioPayload(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function mapPortfolioProjectsToLandingItems(array $projects): array
    {
        return collect($projects)
            ->filter(fn ($item): bool => is_array($item))
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

    private function toModularPayload(array $heroSlides, array $landingContent, array $landingSections, array $sectionVisibility): array
    {
        $slides = collect($heroSlides)->map(fn (array $slide): array => [
            'videoUrl' => (string) ($slide['video_url'] ?? ''),
            'ctaText' => (string) (($slide['cta_text'] ?? '') ?: $landingContent['primary_btn_text']),
            'ctaUrl' => (string) (($slide['cta_url'] ?? '') ?: $landingContent['primary_btn_url']),
            'overlay' => (float) ($slide['overlay_opacity'] ?? 0.78),
        ])->values()->all();

        return [
            'hero' => [
                'heading' => $landingContent['heading_text'],
                'tagline' => $landingContent['tagline_text'],
                'lead' => $landingContent['lead_text'],
                'slides' => $slides,
            ],
            'about' => [
                'company' => 'Production House Nusantara',
                'vision' => 'Membawa cerita lokal menjadi visual kelas global.',
                'story' => 'Kami memadukan riset, craft visual, dan eksekusi produksi modern.',
            ],
            'services' => $landingSections['services_items'],
            'portfolio' => $landingSections['portfolio_items'],
            'team' => $landingSections['team_members'],
            'workflow' => $landingSections['process_steps'],
            'clients' => [],
            'testimonials' => [],
            'contact' => [
                'title' => $landingSections['contact_title'],
                'text' => $landingSections['contact_text'],
                'whatsapp' => $landingSections['contact_button_whatsapp_url'],
                'email' => $landingSections['footer_email'],
                'mapQuery' => '-8.112972,112.311306',
            ],
            'footer' => [
                'brand' => $landingSections['footer_brand_name'],
                'address' => $landingSections['footer_address'],
                'copyright' => $landingSections['footer_copyright'],
                'socials' => $landingSections['footer_social_links'],
            ],
            'section_visibility' => $sectionVisibility,
        ];
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
}
