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

class WebsiteCmsBlogNewsController extends Controller
{
    private const MAX_REVISIONS = 10;

    public function index(): View
    {
        $settings = LandingSetting::query()->pluck('value', 'key')->all();
        $payload = $this->decodePayload((string) ($settings['cms_blog_news_payload'] ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) ($settings['cms_blog_news_revisions'] ?? '')));
        $commentsQueue = $this->decodeComments((string) ($settings['cms_blog_news_comments'] ?? ''));
        $pendingComments = collect($commentsQueue)->filter(fn (array $comment): bool => (string) ($comment['status'] ?? 'pending') === 'pending')->values()->all();

        return view('content.dashboard.website-cms-blog-news', [
            'form' => [
                'editor_engine' => (string) ($payload['editor']['engine'] ?? 'tinymce'),
                'excerpt_mode' => (string) ($payload['editor']['excerpt_mode'] ?? 'manual'),
                'posts_json' => $this->prettyJson($payload['posts'] ?? $this->defaultPosts()),
                'posts_form' => $this->normalizePostsForForm($payload['posts'] ?? $this->defaultPosts()),
                'categories_json' => $this->prettyJson($payload['categories'] ?? $this->defaultCategories()),
                'tags_json' => $this->prettyJson($payload['tags'] ?? $this->defaultTags()),
                'layout_view_mode' => (string) ($payload['layout']['view_mode'] ?? 'grid'),
                'layout_sidebar_enabled' => (bool) ($payload['layout']['sidebar_enabled'] ?? true),
                'layout_pagination_mode' => (string) ($payload['layout']['pagination_mode'] ?? 'pagination'),
                'layout_items_per_page' => (int) ($payload['layout']['items_per_page'] ?? 9),
                'layout_show_reading_time' => (bool) ($payload['layout']['show_reading_time'] ?? true),
                'layout_sidebar_blocks_json' => $this->prettyJson($payload['layout']['sidebar_blocks'] ?? $this->defaultSidebarBlocks()),
                'engagement_comments_enabled' => (bool) ($payload['engagement']['comments_enabled'] ?? true),
                'engagement_pingbacks_enabled' => (bool) ($payload['engagement']['pingbacks_enabled'] ?? false),
                'engagement_spam_protection' => (string) ($payload['engagement']['spam_protection'] ?? 'manual'),
                'engagement_social_share_counters' => (bool) ($payload['engagement']['social_share_counters'] ?? true),
                'engagement_related_posts_mode' => (string) ($payload['engagement']['related_posts_mode'] ?? 'auto'),
                'calendar_default_status' => (string) ($payload['calendar']['default_status'] ?? 'draft'),
                'calendar_editorial_workflow_json' => $this->prettyJson($payload['calendar']['workflow'] ?? $this->defaultEditorialWorkflow()),
                'calendar_scheduled_posts_json' => $this->prettyJson($payload['calendar']['scheduled_posts'] ?? []),
                'strategy_notes' => (string) ($payload['strategy_notes'] ?? 'Publish "Behind the Scenes" dari project aktif untuk build authority & SEO traffic.'),
            ],
            'revisions' => $revisions,
            'revisionCount' => count($revisions),
            'commentsQueue' => $commentsQueue,
            'pendingCommentsCount' => count($pendingComments),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'editor_engine' => ['required', 'in:tinymce,ckeditor,markdown,directus'],
            'excerpt_mode' => ['required', 'in:manual,auto'],
            'posts_json' => ['nullable', 'string'],
            'posts_form' => ['nullable', 'array'],
            'posts_form.*.title' => ['nullable', 'string', 'max:220'],
            'posts_form.*.slug' => ['nullable', 'string', 'max:220'],
            'posts_form.*.media_type' => ['nullable', 'in:image,video'],
            'posts_form.*.featured_image' => ['nullable', 'string', 'max:2000'],
            'posts_form.*.featured_image_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'posts_form.*.media_video_url' => ['nullable', 'string', 'max:2000'],
            'posts_form.*.media_video_file' => ['nullable', 'file', 'mimetypes:video/*', 'max:512000'],
            'posts_form.*.content_html' => ['nullable', 'string'],
            'posts_form.*.excerpt' => ['nullable', 'string'],
            'posts_form.*.author' => ['nullable', 'string', 'max:120'],
            'posts_form.*.published_at' => ['nullable', 'date'],
            'posts_form.*.categories_text' => ['nullable', 'string'],
            'posts_form.*.tags_text' => ['nullable', 'string'],
            'posts_form.*.seo_title' => ['nullable', 'string', 'max:255'],
            'posts_form.*.seo_description' => ['nullable', 'string'],
            'posts_form.*.canonical_url' => ['nullable', 'string', 'max:2000'],
            'posts_form.*.og_title' => ['nullable', 'string', 'max:255'],
            'posts_form.*.og_description' => ['nullable', 'string'],
            'posts_form.*.og_image' => ['nullable', 'string', 'max:2000'],
            'posts_form.*.comments_enabled' => ['nullable', 'boolean'],
            'posts_form.*.pingbacks_enabled' => ['nullable', 'boolean'],
            'posts_form.*.status' => ['nullable', 'in:draft,review,published'],
            'posts_form.*.reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'posts_form.*.is_featured' => ['nullable', 'boolean'],
            'posts_form.*.is_popular' => ['nullable', 'boolean'],
            'posts_form.*.related_posts_text' => ['nullable', 'string'],
            'posts_form.*.view_count' => ['nullable', 'integer', 'min:0'],
            'posts_form.*.like_count' => ['nullable', 'integer', 'min:0'],
            'posts_form.*.comment_count' => ['nullable', 'integer', 'min:0'],
            'categories_json' => ['nullable', 'string'],
            'tags_json' => ['nullable', 'string'],
            'layout_view_mode' => ['required', 'in:grid,list'],
            'layout_sidebar_enabled' => ['nullable', 'boolean'],
            'layout_pagination_mode' => ['required', 'in:pagination,infinite'],
            'layout_items_per_page' => ['required', 'integer', 'min:1', 'max:30'],
            'layout_show_reading_time' => ['nullable', 'boolean'],
            'layout_sidebar_blocks_json' => ['nullable', 'string'],
            'engagement_comments_enabled' => ['nullable', 'boolean'],
            'engagement_pingbacks_enabled' => ['nullable', 'boolean'],
            'engagement_spam_protection' => ['required', 'in:manual,akismet'],
            'engagement_social_share_counters' => ['nullable', 'boolean'],
            'engagement_related_posts_mode' => ['required', 'in:auto,manual'],
            'calendar_default_status' => ['required', 'in:draft,review,published'],
            'calendar_editorial_workflow_json' => ['nullable', 'string'],
            'calendar_scheduled_posts_json' => ['nullable', 'string'],
            'strategy_notes' => ['nullable', 'string'],
            'action' => ['nullable', 'in:save,restore'],
            'restore_revision_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $action = (string) ($data['action'] ?? 'save');
        $currentPayload = $this->decodePayload((string) (LandingSetting::query()->where('key', 'cms_blog_news_payload')->value('value') ?? ''));
        $revisions = $this->trimRevisions($this->decodeRevisions((string) (LandingSetting::query()->where('key', 'cms_blog_news_revisions')->value('value') ?? '')));

        if ($action === 'restore') {
            $index = (int) ($data['restore_revision_index'] ?? -1);
            if ($index >= 0 && isset($revisions[$index]['payload']) && is_array($revisions[$index]['payload'])) {
                LandingSetting::query()->updateOrCreate(
                    ['key' => 'cms_blog_news_payload'],
                    ['value' => json_encode($revisions[$index]['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
                );

                return redirect()
                    ->route('dashboard-website-cms-blog-news')
                    ->with('success', 'Revision Blog/News berhasil dipulihkan.');
            }

            return redirect()
                ->route('dashboard-website-cms-blog-news')
                ->with('success', 'Revision tidak ditemukan.');
        }

        try {
            $postsFromForm = $this->parsePostsFromForm($request, $request->input('posts_form', []));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'posts_form' => 'Konversi video post gagal. Pastikan FFmpeg/GStreamer/HandBrake aktif di server.',
            ]);
        }
        $posts = $postsFromForm !== [] ? $postsFromForm : $this->safeJsonArray($data['posts_json'] ?? '[]');
        if (($data['excerpt_mode'] ?? 'manual') === 'auto') {
            $posts = $this->autoGenerateExcerpts($posts);
        }

        $payload = [
            'editor' => [
                'engine' => $data['editor_engine'],
                'excerpt_mode' => $data['excerpt_mode'],
            ],
            'posts' => $posts,
            'categories' => $this->safeJsonArray($data['categories_json'] ?? '[]'),
            'tags' => $this->safeJsonArray($data['tags_json'] ?? '[]'),
            'layout' => [
                'view_mode' => $data['layout_view_mode'],
                'sidebar_enabled' => $request->boolean('layout_sidebar_enabled'),
                'pagination_mode' => $data['layout_pagination_mode'],
                'items_per_page' => (int) $data['layout_items_per_page'],
                'show_reading_time' => $request->boolean('layout_show_reading_time'),
                'sidebar_blocks' => $this->safeJsonArray($data['layout_sidebar_blocks_json'] ?? '[]'),
            ],
            'engagement' => [
                'comments_enabled' => $request->boolean('engagement_comments_enabled'),
                'pingbacks_enabled' => $request->boolean('engagement_pingbacks_enabled'),
                'spam_protection' => $data['engagement_spam_protection'],
                'social_share_counters' => $request->boolean('engagement_social_share_counters'),
                'related_posts_mode' => $data['engagement_related_posts_mode'],
            ],
            'calendar' => [
                'default_status' => $data['calendar_default_status'],
                'workflow' => $this->safeJsonArray($data['calendar_editorial_workflow_json'] ?? '[]'),
                'scheduled_posts' => $this->safeJsonArray($data['calendar_scheduled_posts_json'] ?? '[]'),
            ],
            'strategy_notes' => (string) ($data['strategy_notes'] ?? ''),
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
            ['key' => 'cms_blog_news_payload'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_blog_news_revisions'],
            ['value' => json_encode($revisions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()
            ->route('dashboard-website-cms-blog-news')
            ->with('success', 'Blog/News CMS berhasil diperbarui.');
    }

    public function moderateComment(Request $request, string $commentId): RedirectResponse
    {
        $data = $request->validate([
            'moderation_action' => ['required', 'in:approve,reject,delete'],
            'admin_reply' => ['nullable', 'string', 'max:3000'],
        ]);

        $comments = $this->decodeComments((string) (LandingSetting::query()->where('key', 'cms_blog_news_comments')->value('value') ?? '[]'));
        $payload = $this->decodePayload((string) (LandingSetting::query()->where('key', 'cms_blog_news_payload')->value('value') ?? ''));
        $posts = is_array($payload['posts'] ?? null) ? $payload['posts'] : [];
        $action = (string) $data['moderation_action'];

        foreach ($comments as $index => $comment) {
            if ((string) ($comment['id'] ?? '') !== (string) $commentId) {
                continue;
            }
            $oldStatus = (string) ($comment['status'] ?? 'pending');
            $postSlug = Str::slug((string) ($comment['post_slug'] ?? ''));

            if ($action === 'delete') {
                unset($comments[$index]);
                if ($oldStatus === 'approved') {
                    $posts = $this->adjustPostCommentCount($posts, $postSlug, -1);
                }
                break;
            }

            $newStatus = $action === 'approve' ? 'approved' : 'rejected';
            if ($oldStatus !== $newStatus) {
                if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                    $posts = $this->adjustPostCommentCount($posts, $postSlug, -1);
                }
                if ($oldStatus !== 'approved' && $newStatus === 'approved') {
                    $posts = $this->adjustPostCommentCount($posts, $postSlug, 1);
                }
            }
            $comments[$index]['status'] = $newStatus;
            $comments[$index]['admin_reply'] = (string) ($data['admin_reply'] ?? '');
            $comments[$index]['updated_at'] = now()->toDateTimeString();
            break;
        }

        $comments = array_values($comments);
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_blog_news_comments'],
            ['value' => json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
        $payload['posts'] = $posts;
        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_blog_news_payload'],
            ['value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()
            ->route('dashboard-website-cms-blog-news')
            ->with('success', 'Moderasi komentar berhasil diperbarui.');
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

    private function trimRevisions(array $revisions): array
    {
        return array_values(array_slice($revisions, 0, self::MAX_REVISIONS));
    }

    private function prettyJson(array $value): string
    {
        return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function decodeComments(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return collect(is_array($decoded) ? $decoded : [])
            ->filter(fn ($item): bool => is_array($item))
            ->sortByDesc(fn (array $item): int => strtotime((string) ($item['created_at'] ?? '1970-01-01')) ?: 0)
            ->values()
            ->all();
    }

    private function autoGenerateExcerpts(array $posts): array
    {
        return collect($posts)
            ->filter(fn ($post): bool => is_array($post))
            ->map(function (array $post): array {
                $excerpt = trim((string) ($post['excerpt'] ?? ''));
                if ($excerpt !== '') {
                    return $post;
                }
                $content = trim(strip_tags((string) ($post['content_html'] ?? '')));
                $post['excerpt'] = Str::limit($content, 180, '...');

                return $post;
            })
            ->values()
            ->all();
    }

    private function normalizePostsForForm(array $posts): array
    {
        return collect($posts)
            ->filter(fn ($post): bool => is_array($post))
            ->map(function (array $post): array {
                return [
                    'title' => (string) ($post['title'] ?? ''),
                    'slug' => (string) ($post['slug'] ?? ''),
                    'media_type' => (string) ($post['media_type'] ?? 'image'),
                    'featured_image' => (string) ($post['featured_image'] ?? ''),
                    'media_video_url' => (string) ($post['media_video_url'] ?? ''),
                    'content_html' => (string) ($post['content_html'] ?? ''),
                    'excerpt' => (string) ($post['excerpt'] ?? ''),
                    'author' => (string) ($post['author'] ?? 'Editorial PHN'),
                    'published_at' => (string) ($post['published_at'] ?? now()->toDateTimeString()),
                    'categories_text' => collect($post['categories'] ?? [])->filter()->implode(', '),
                    'tags_text' => collect($post['tags'] ?? [])->filter()->implode(', '),
                    'seo_title' => (string) ($post['seo_title'] ?? ''),
                    'seo_description' => (string) ($post['seo_description'] ?? ''),
                    'canonical_url' => (string) ($post['canonical_url'] ?? ''),
                    'og_title' => (string) ($post['og_title'] ?? ''),
                    'og_description' => (string) ($post['og_description'] ?? ''),
                    'og_image' => (string) ($post['og_image'] ?? ''),
                    'comments_enabled' => (bool) ($post['comments_enabled'] ?? true),
                    'pingbacks_enabled' => (bool) ($post['pingbacks_enabled'] ?? false),
                    'status' => (string) ($post['status'] ?? 'draft'),
                    'reading_time_minutes' => (string) ($post['reading_time_minutes'] ?? 4),
                    'is_featured' => (bool) ($post['is_featured'] ?? false),
                    'is_popular' => (bool) ($post['is_popular'] ?? false),
                    'related_posts_text' => collect($post['related_posts'] ?? [])->filter()->implode(', '),
                    'view_count' => (string) ($post['view_count'] ?? 0),
                    'like_count' => (string) ($post['like_count'] ?? 0),
                    'comment_count' => (string) ($post['comment_count'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function parsePostsFromForm(Request $request, $posts): array
    {
        if (! is_array($posts)) {
            return [];
        }

        return collect($posts)
            ->filter(fn ($post): bool => is_array($post))
            ->map(function (array $post, int $index) use ($request): array {
                $title = trim((string) ($post['title'] ?? ''));
                $slug = trim((string) ($post['slug'] ?? ''));
                $mediaType = (string) ($post['media_type'] ?? 'image');
                if (! in_array($mediaType, ['image', 'video'], true)) {
                    $mediaType = 'image';
                }
                $featuredImage = trim((string) ($post['featured_image'] ?? ''));
                $mediaVideoUrl = trim((string) ($post['media_video_url'] ?? ''));
                $thumbnailUpload = $request->file("posts_form.$index.featured_image_file");
                if ($thumbnailUpload instanceof UploadedFile) {
                    $featuredImage = $thumbnailUpload->store('cms/blog-news/thumbnails', 'public');
                }
                $resolvedSlug = $slug !== '' ? Str::slug($slug) : Str::slug($title);
                $videoUpload = $request->file("posts_form.$index.media_video_file");
                if ($videoUpload instanceof UploadedFile) {
                    $asset = app(VideoConversionEngine::class)->registerUpload(
                        $videoUpload,
                        'cms/blog-news/videos',
                        'blog',
                        $title !== '' ? $title : $resolvedSlug,
                        ['setting_key' => 'cms_blog_news_payload']
                    );
                    $mediaVideoUrl = (string) $asset->source_path;
                }

                $categories = collect(explode(',', (string) ($post['categories_text'] ?? '')))
                    ->map(fn (string $item): string => trim($item))
                    ->filter()
                    ->values()
                    ->all();
                $tags = collect(explode(',', (string) ($post['tags_text'] ?? '')))
                    ->map(fn (string $item): string => trim($item))
                    ->filter()
                    ->values()
                    ->all();
                $relatedPosts = collect(explode(',', (string) ($post['related_posts_text'] ?? '')))
                    ->map(fn (string $item): string => trim($item))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'title' => $title,
                    'slug' => $resolvedSlug,
                    'media_type' => $mediaType,
                    'featured_image' => $featuredImage,
                    'media_video_url' => $mediaVideoUrl,
                    'content_html' => (string) ($post['content_html'] ?? ''),
                    'excerpt' => (string) ($post['excerpt'] ?? ''),
                    'author' => (string) ($post['author'] ?? 'Editorial PHN'),
                    'published_at' => (string) ($post['published_at'] ?? now()->toDateTimeString()),
                    'categories' => $categories,
                    'tags' => $tags,
                    'seo_title' => (string) ($post['seo_title'] ?? ''),
                    'seo_description' => (string) ($post['seo_description'] ?? ''),
                    'canonical_url' => (string) ($post['canonical_url'] ?? ''),
                    'og_title' => (string) ($post['og_title'] ?? ''),
                    'og_description' => (string) ($post['og_description'] ?? ''),
                    'og_image' => (string) ($post['og_image'] ?? ''),
                    'comments_enabled' => filter_var($post['comments_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'pingbacks_enabled' => filter_var($post['pingbacks_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'status' => in_array((string) ($post['status'] ?? ''), ['draft', 'review', 'published'], true) ? (string) $post['status'] : 'draft',
                    'reading_time_minutes' => max(1, (int) ($post['reading_time_minutes'] ?? 4)),
                    'is_featured' => filter_var($post['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_popular' => filter_var($post['is_popular'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'related_posts' => $relatedPosts,
                    'view_count' => max(0, (int) ($post['view_count'] ?? 0)),
                    'like_count' => max(0, (int) ($post['like_count'] ?? 0)),
                    'comment_count' => max(0, (int) ($post['comment_count'] ?? 0)),
                    'popular_score' => $this->calculatePopularScore(
                        max(0, (int) ($post['view_count'] ?? 0)),
                        max(0, (int) ($post['like_count'] ?? 0)),
                        max(0, (int) ($post['comment_count'] ?? 0))
                    ),
                ];
            })
            ->filter(fn (array $post): bool => trim($post['title']) !== '')
            ->values()
            ->all();
    }

    private function adjustPostCommentCount(array $posts, string $slug, int $delta): array
    {
        return collect($posts)
            ->map(function ($post) use ($slug, $delta) {
                if (! is_array($post)) {
                    return $post;
                }
                $postSlug = Str::slug((string) ($post['slug'] ?? $post['title'] ?? ''));
                if ($postSlug !== $slug) {
                    return $post;
                }
                $commentCount = max(0, (int) ($post['comment_count'] ?? 0) + $delta);
                $viewCount = max(0, (int) ($post['view_count'] ?? 0));
                $likeCount = max(0, (int) ($post['like_count'] ?? 0));
                $post['comment_count'] = $commentCount;
                $post['popular_score'] = $this->calculatePopularScore($viewCount, $likeCount, $commentCount);

                return $post;
            })
            ->values()
            ->all();
    }

    private function calculatePopularScore(int $views, int $likes, int $comments): int
    {
        return $views + ($likes * 3) + ($comments * 5);
    }

    private function defaultPosts(): array
    {
        return [
            [
                'title' => 'Behind the Scenes: Commercial Shoot Jakarta',
                'slug' => 'behind-the-scenes-commercial-shoot-jakarta',
                'featured_image' => '',
                'media_type' => 'image',
                'media_video_url' => '',
                'content_html' => '<p>Konten BTS produksi komersial terbaru...</p>',
                'excerpt' => 'Cerita di balik proses produksi komersial dari pre-production sampai final delivery.',
                'author' => 'Editorial PHN',
                'published_at' => now()->toDateTimeString(),
                'categories' => ['Behind the Scenes'],
                'tags' => ['production house', 'bts', 'commercial'],
                'seo_title' => 'Behind the Scenes Commercial Shoot Jakarta',
                'seo_description' => 'Update BTS project aktif untuk membangun authority dan SEO traffic.',
                'canonical_url' => '',
                'og_title' => 'Behind the Scenes Commercial Shoot Jakarta',
                'og_description' => 'Cerita produksi komersial terbaru oleh Production House Nusantara.',
                'og_image' => '',
                'comments_enabled' => true,
                'pingbacks_enabled' => false,
                'status' => 'published',
                'reading_time_minutes' => 4,
            ],
        ];
    }

    private function defaultCategories(): array
    {
        return [
            ['name' => 'Production Tips', 'slug' => 'production-tips'],
            ['name' => 'Behind the Scenes', 'slug' => 'behind-the-scenes'],
            ['name' => 'Company News', 'slug' => 'company-news'],
            ['name' => 'Industry Trends', 'slug' => 'industry-trends'],
            ['name' => 'Equipment Reviews', 'slug' => 'equipment-reviews'],
            ['name' => 'Client Success Stories', 'slug' => 'client-success-stories'],
            ['name' => 'Tutorials', 'slug' => 'tutorials'],
        ];
    }

    private function defaultTags(): array
    {
        return ['production-house', 'cinematic', 'commercial', 'seo-content', 'video-marketing'];
    }

    private function defaultSidebarBlocks(): array
    {
        return ['recent_posts', 'categories', 'tags'];
    }

    private function defaultEditorialWorkflow(): array
    {
        return ['draft', 'review', 'publish'];
    }
}
