@extends('layouts/contentNavbarLayout')

@section('title', 'Website CMS - Blog / News')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">WEBSITE CMS · Blog / News</h4>
                <p class="mb-0">Content marketing, company updates, dan industry insights dengan editor post, kategori, SEO, layout, engagement, dan content calendar.</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard-website-cms-blog-news.update') }}" enctype="multipart/form-data" class="row g-4">
                    @csrf
                    <input type="hidden" name="action" value="save">
                    <style>
                        .blog-panel-map {
                            display: flex;
                            flex-wrap: wrap;
                            gap: .45rem;
                        }
                        .blog-panel-chip {
                            border: 1px solid var(--bs-border-color);
                            border-radius: 999px;
                            padding: .25rem .65rem;
                            font-size: .78rem;
                        }
                        .post-card {
                            border: 1px solid var(--bs-border-color);
                            border-radius: .7rem;
                            padding: .85rem;
                            background: var(--bs-body-bg);
                        }
                        .post-card.dragging {
                            opacity: .65;
                        }
                        .post-card.drop-target {
                            outline: 2px dashed var(--bs-primary);
                        }
                        .post-list-toolbar {
                            display: flex;
                            gap: .6rem;
                            flex-wrap: wrap;
                        }
                        .post-list-toolbar .form-control,
                        .post-list-toolbar .form-select {
                            min-width: 180px;
                        }
                        .upload-progress-wrap {
                            display: none;
                            margin-top: .7rem;
                        }
                        .upload-progress-wrap.active {
                            display: block;
                        }
                    </style>

                    <div class="col-12">
                        <h6 class="mb-1">Blog</h6>
                        <div class="blog-panel-map">
                            @foreach (['Semua Artikel','Kategori','Tag','Detail Artikel','Komentar','Penulis','Featured Post','Popular Post','Related Post','Arsip','Search','Tambah Artikel','Media'] as $label)
                                <span class="blog-panel-chip">{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="mb-0">Post Editor</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Editor Engine</label>
                        <select class="form-select" name="editor_engine">
                            <option value="tinymce" @selected(old('editor_engine', $form['editor_engine']) === 'tinymce')>TinyMCE</option>
                            <option value="ckeditor" @selected(old('editor_engine', $form['editor_engine']) === 'ckeditor')>CKEditor</option>
                            <option value="markdown" @selected(old('editor_engine', $form['editor_engine']) === 'markdown')>Markdown</option>
                            <option value="directus" @selected(old('editor_engine', $form['editor_engine']) === 'directus')>Directus Rich Text</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Excerpt Mode</label>
                        <select class="form-select" name="excerpt_mode">
                            <option value="manual" @selected(old('excerpt_mode', $form['excerpt_mode']) === 'manual')>Manual</option>
                            <option value="auto" @selected(old('excerpt_mode', $form['excerpt_mode']) === 'auto')>Auto-generate</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Semua Artikel (Add / Edit / Delete Post)</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPost">Tambah Artikel</button>
                        </div>
                        <div class="post-list-toolbar mb-2">
                            <input type="text" class="form-control" id="postSearchInput" placeholder="Search judul / isi / kategori / tag">
                            <select class="form-select" id="postCategoryFilter">
                                <option value="all">Filter Kategori: All</option>
                            </select>
                            <select class="form-select" id="postTagFilter">
                                <option value="all">Filter Tag: All</option>
                            </select>
                            <select class="form-select" id="postSortMode">
                                <option value="newest">Sort: Terbaru</option>
                                <option value="popular">Sort: Populer</option>
                                <option value="alphabetical">Sort: A-Z</option>
                            </select>
                        </div>
                        <div id="postRepeater" class="d-flex flex-column gap-3">
                            @php $postsForm = old('posts_form', $form['posts_form']); @endphp
                            @foreach ($postsForm as $index => $post)
                                <div class="post-card js-post-card" draggable="true" data-published-at="{{ $post['published_at'] ?? '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-post-drag">↕</button>
                                            <strong>Artikel #<span class="js-post-order">{{ $index + 1 }}</span></strong>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-post-up">↑</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-post-down">↓</button>
                                            <button type="button" class="btn btn-sm btn-outline-danger js-post-remove">Hapus</button>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label class="form-label">Judul</label><input class="form-control" type="text" name="posts_form[{{ $index }}][title]" value="{{ $post['title'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Slug</label><input class="form-control" type="text" name="posts_form[{{ $index }}][slug]" value="{{ $post['slug'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Media Type</label><select class="form-select" name="posts_form[{{ $index }}][media_type]"><option value="image" @selected(($post['media_type'] ?? 'image')==='image')>Photo</option><option value="video" @selected(($post['media_type'] ?? '')==='video')>Video</option></select></div>
                                        <div class="col-md-2"><label class="form-label">Tanggal Publish</label><input class="form-control" type="datetime-local" name="posts_form[{{ $index }}][published_at]" value="{{ old("posts_form.$index.published_at", !empty($post['published_at']) ? \Illuminate\Support\Carbon::parse($post['published_at'])->format('Y-m-d\TH:i') : '') }}"></div>
                                        <div class="col-md-1"><label class="form-label">Penulis</label><input class="form-control" type="text" name="posts_form[{{ $index }}][author]" value="{{ $post['author'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">Thumbnail URL</label><input class="form-control" type="text" name="posts_form[{{ $index }}][featured_image]" value="{{ $post['featured_image'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Upload Thumbnail</label><input class="form-control" type="file" name="posts_form[{{ $index }}][featured_image_file]" accept=".jpg,.jpeg,.png,.webp"></div>
                                        <div class="col-md-4"><label class="form-label">Video External URL</label><input class="form-control" type="text" name="posts_form[{{ $index }}][media_video_url]" value="{{ $post['media_video_url'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Upload Video (auto convert WebM)</label><input class="form-control" type="file" name="posts_form[{{ $index }}][media_video_file]" accept="video/*"></div>
                                        <div class="col-md-3"><label class="form-label">Kategori (comma)</label><input class="form-control" type="text" name="posts_form[{{ $index }}][categories_text]" value="{{ $post['categories_text'] ?? '' }}"></div>
                                        <div class="col-md-3"><label class="form-label">Tag (comma)</label><input class="form-control" type="text" name="posts_form[{{ $index }}][tags_text]" value="{{ $post['tags_text'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="posts_form[{{ $index }}][status]"><option value="draft" @selected(($post['status'] ?? 'draft')==='draft')>Draft</option><option value="review" @selected(($post['status'] ?? '')==='review')>Review</option><option value="published" @selected(($post['status'] ?? '')==='published')>Published</option></select></div>
                                        <div class="col-md-2"><label class="form-label">Reading Time</label><input class="form-control" type="number" min="1" max="120" name="posts_form[{{ $index }}][reading_time_minutes]" value="{{ $post['reading_time_minutes'] ?? 4 }}"></div>
                                        <div class="col-12"><label class="form-label">Isi Artikel (rich text + media embed)</label><textarea class="form-control font-monospace" rows="4" name="posts_form[{{ $index }}][content_html]">{{ $post['content_html'] ?? '' }}</textarea></div>
                                        <div class="col-12"><label class="form-label">Cuplikan / Summary</label><textarea class="form-control" rows="2" name="posts_form[{{ $index }}][excerpt]">{{ $post['excerpt'] ?? '' }}</textarea></div>
                                        <div class="col-md-4"><label class="form-label">SEO Title</label><input class="form-control" type="text" name="posts_form[{{ $index }}][seo_title]" value="{{ $post['seo_title'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">SEO Description</label><input class="form-control" type="text" name="posts_form[{{ $index }}][seo_description]" value="{{ $post['seo_description'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">Canonical URL</label><input class="form-control" type="text" name="posts_form[{{ $index }}][canonical_url]" value="{{ $post['canonical_url'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">OG Title</label><input class="form-control" type="text" name="posts_form[{{ $index }}][og_title]" value="{{ $post['og_title'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">OG Description</label><input class="form-control" type="text" name="posts_form[{{ $index }}][og_description]" value="{{ $post['og_description'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">OG Image</label><input class="form-control" type="text" name="posts_form[{{ $index }}][og_image]" value="{{ $post['og_image'] ?? '' }}"></div>
                                        <div class="col-md-4"><label class="form-label">Related Posts (slug comma)</label><input class="form-control" type="text" name="posts_form[{{ $index }}][related_posts_text]" value="{{ $post['related_posts_text'] ?? '' }}"></div>
                                        <div class="col-md-2"><label class="form-label">Views</label><input class="form-control" type="number" min="0" name="posts_form[{{ $index }}][view_count]" value="{{ $post['view_count'] ?? 0 }}"></div>
                                        <div class="col-md-2"><label class="form-label">Likes</label><input class="form-control" type="number" min="0" name="posts_form[{{ $index }}][like_count]" value="{{ $post['like_count'] ?? 0 }}"></div>
                                        <div class="col-md-2"><label class="form-label">Comments</label><input class="form-control" type="number" min="0" name="posts_form[{{ $index }}][comment_count]" value="{{ $post['comment_count'] ?? 0 }}"></div>
                                        <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[{{ $index }}][comments_enabled]" value="1" @checked($post['comments_enabled'] ?? true)><label class="form-check-label">Komentar</label></div></div>
                                        <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[{{ $index }}][pingbacks_enabled]" value="1" @checked($post['pingbacks_enabled'] ?? false)><label class="form-check-label">Pingbacks</label></div></div>
                                        <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[{{ $index }}][is_featured]" value="1" @checked($post['is_featured'] ?? false)><label class="form-check-label">Featured</label></div></div>
                                        <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[{{ $index }}][is_popular]" value="1" @checked($post['is_popular'] ?? false)><label class="form-check-label">Popular</label></div></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <template id="postCardTemplate">
                            <div class="post-card js-post-card" draggable="true" data-published-at="">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-post-drag">↕</button>
                                        <strong>Artikel #<span class="js-post-order">__ORDER__</span></strong>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-post-up">↑</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-post-down">↓</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-post-remove">Hapus</button>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label">Judul</label><input class="form-control" type="text" name="posts_form[__INDEX__][title]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Slug</label><input class="form-control" type="text" name="posts_form[__INDEX__][slug]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Media Type</label><select class="form-select" name="posts_form[__INDEX__][media_type]"><option value="image" selected>Photo</option><option value="video">Video</option></select></div>
                                    <div class="col-md-2"><label class="form-label">Tanggal Publish</label><input class="form-control" type="datetime-local" name="posts_form[__INDEX__][published_at]" value=""></div>
                                    <div class="col-md-1"><label class="form-label">Penulis</label><input class="form-control" type="text" name="posts_form[__INDEX__][author]" value="Editorial PHN"></div>
                                    <div class="col-md-4"><label class="form-label">Thumbnail URL</label><input class="form-control" type="text" name="posts_form[__INDEX__][featured_image]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Upload Thumbnail</label><input class="form-control" type="file" name="posts_form[__INDEX__][featured_image_file]" accept=".jpg,.jpeg,.png,.webp"></div>
                                    <div class="col-md-4"><label class="form-label">Video External URL</label><input class="form-control" type="text" name="posts_form[__INDEX__][media_video_url]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Upload Video (auto convert WebM)</label><input class="form-control" type="file" name="posts_form[__INDEX__][media_video_file]" accept="video/*"></div>
                                    <div class="col-md-3"><label class="form-label">Kategori (comma)</label><input class="form-control" type="text" name="posts_form[__INDEX__][categories_text]" value=""></div>
                                    <div class="col-md-3"><label class="form-label">Tag (comma)</label><input class="form-control" type="text" name="posts_form[__INDEX__][tags_text]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="posts_form[__INDEX__][status]"><option value="draft" selected>Draft</option><option value="review">Review</option><option value="published">Published</option></select></div>
                                    <div class="col-md-2"><label class="form-label">Reading Time</label><input class="form-control" type="number" min="1" max="120" name="posts_form[__INDEX__][reading_time_minutes]" value="4"></div>
                                    <div class="col-12"><label class="form-label">Isi Artikel (rich text + media embed)</label><textarea class="form-control font-monospace" rows="4" name="posts_form[__INDEX__][content_html]"></textarea></div>
                                    <div class="col-12"><label class="form-label">Cuplikan / Summary</label><textarea class="form-control" rows="2" name="posts_form[__INDEX__][excerpt]"></textarea></div>
                                    <div class="col-md-4"><label class="form-label">SEO Title</label><input class="form-control" type="text" name="posts_form[__INDEX__][seo_title]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">SEO Description</label><input class="form-control" type="text" name="posts_form[__INDEX__][seo_description]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">Canonical URL</label><input class="form-control" type="text" name="posts_form[__INDEX__][canonical_url]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">OG Title</label><input class="form-control" type="text" name="posts_form[__INDEX__][og_title]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">OG Description</label><input class="form-control" type="text" name="posts_form[__INDEX__][og_description]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">OG Image</label><input class="form-control" type="text" name="posts_form[__INDEX__][og_image]" value=""></div>
                                    <div class="col-md-4"><label class="form-label">Related Posts (slug comma)</label><input class="form-control" type="text" name="posts_form[__INDEX__][related_posts_text]" value=""></div>
                                    <div class="col-md-2"><label class="form-label">Views</label><input class="form-control" type="number" min="0" name="posts_form[__INDEX__][view_count]" value="0"></div>
                                    <div class="col-md-2"><label class="form-label">Likes</label><input class="form-control" type="number" min="0" name="posts_form[__INDEX__][like_count]" value="0"></div>
                                    <div class="col-md-2"><label class="form-label">Comments</label><input class="form-control" type="number" min="0" name="posts_form[__INDEX__][comment_count]" value="0"></div>
                                    <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[__INDEX__][comments_enabled]" value="1" checked><label class="form-check-label">Komentar</label></div></div>
                                    <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[__INDEX__][pingbacks_enabled]" value="1"><label class="form-check-label">Pingbacks</label></div></div>
                                    <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[__INDEX__][is_featured]" value="1"><label class="form-check-label">Featured</label></div></div>
                                    <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="posts_form[__INDEX__][is_popular]" value="1"><label class="form-check-label">Popular</label></div></div>
                                </div>
                            </div>
                        </template>
                        <input type="hidden" name="posts_json" id="posts_json_hidden" value="{{ old('posts_json', $form['posts_json']) }}">
                        <details class="mt-3">
                            <summary class="small text-muted">Advanced: Posts JSON Manual</summary>
                            <textarea class="form-control font-monospace mt-2" rows="8" id="posts_json_manual">{{ old('posts_json', $form['posts_json']) }}</textarea>
                        </details>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Categories & Tags</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categories JSON</label>
                        <textarea class="form-control font-monospace" rows="7" name="categories_json" placeholder='[{"name":"Production Tips","slug":"production-tips"}]'>{{ old('categories_json', $form['categories_json']) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tags JSON</label>
                        <textarea class="form-control font-monospace" rows="7" name="tags_json" placeholder='["seo-keyword","topic-cluster"]'>{{ old('tags_json', $form['tags_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Post Settings (SEO & Social)</h6>
                    </div>
                    <div class="col-12">
                        <p class="text-muted mb-0">Konfigurasi SEO meta, canonical URL, OG tags, comments, pingbacks, dan social preview disimpan per post di Posts JSON.</p>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Blog Layout</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">View Mode</label>
                        <select class="form-select" name="layout_view_mode">
                            <option value="grid" @selected(old('layout_view_mode', $form['layout_view_mode']) === 'grid')>Grid</option>
                            <option value="list" @selected(old('layout_view_mode', $form['layout_view_mode']) === 'list')>List</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pagination Mode</label>
                        <select class="form-select" name="layout_pagination_mode">
                            <option value="pagination" @selected(old('layout_pagination_mode', $form['layout_pagination_mode']) === 'pagination')>Pagination</option>
                            <option value="infinite" @selected(old('layout_pagination_mode', $form['layout_pagination_mode']) === 'infinite')>Infinite Scroll</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Items Per Page</label>
                        <input type="number" min="1" max="30" class="form-control" name="layout_items_per_page" value="{{ old('layout_items_per_page', $form['layout_items_per_page']) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="layout_sidebar_enabled" value="1" @checked(old('layout_sidebar_enabled', $form['layout_sidebar_enabled']))>
                            <label class="form-check-label">Sidebar</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="layout_show_reading_time" value="1" @checked(old('layout_show_reading_time', $form['layout_show_reading_time']))>
                            <label class="form-check-label">Reading Time</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Sidebar Blocks JSON</label>
                        <textarea class="form-control font-monospace" rows="4" name="layout_sidebar_blocks_json" placeholder='["recent_posts","categories","tags"]'>{{ old('layout_sidebar_blocks_json', $form['layout_sidebar_blocks_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Engagement</h6>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="engagement_comments_enabled" value="1" @checked(old('engagement_comments_enabled', $form['engagement_comments_enabled']))>
                            <label class="form-check-label">Comments</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="engagement_pingbacks_enabled" value="1" @checked(old('engagement_pingbacks_enabled', $form['engagement_pingbacks_enabled']))>
                            <label class="form-check-label">Pingbacks/Trackbacks</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Spam Protection</label>
                        <select class="form-select" name="engagement_spam_protection">
                            <option value="manual" @selected(old('engagement_spam_protection', $form['engagement_spam_protection']) === 'manual')>Manual Moderation</option>
                            <option value="akismet" @selected(old('engagement_spam_protection', $form['engagement_spam_protection']) === 'akismet')>Akismet</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Related Posts</label>
                        <select class="form-select" name="engagement_related_posts_mode">
                            <option value="auto" @selected(old('engagement_related_posts_mode', $form['engagement_related_posts_mode']) === 'auto')>Auto-suggest</option>
                            <option value="manual" @selected(old('engagement_related_posts_mode', $form['engagement_related_posts_mode']) === 'manual')>Manual</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="engagement_social_share_counters" value="1" @checked(old('engagement_social_share_counters', $form['engagement_social_share_counters']))>
                            <label class="form-check-label">Share Counters</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Content Calendar & Workflow</h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Default Status</label>
                        <select class="form-select" name="calendar_default_status">
                            <option value="draft" @selected(old('calendar_default_status', $form['calendar_default_status']) === 'draft')>Draft</option>
                            <option value="review" @selected(old('calendar_default_status', $form['calendar_default_status']) === 'review')>Review</option>
                            <option value="published" @selected(old('calendar_default_status', $form['calendar_default_status']) === 'published')>Publish</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Editorial Workflow JSON</label>
                        <textarea class="form-control font-monospace" rows="3" name="calendar_editorial_workflow_json" placeholder='["draft","review","publish"]'>{{ old('calendar_editorial_workflow_json', $form['calendar_editorial_workflow_json']) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Scheduled Posts JSON</label>
                        <textarea class="form-control font-monospace" rows="5" name="calendar_scheduled_posts_json" placeholder='[{"slug":"post-slug","publish_at":"2026-03-29 09:00:00","status":"scheduled"}]'>{{ old('calendar_scheduled_posts_json', $form['calendar_scheduled_posts_json']) }}</textarea>
                    </div>

                    <div class="col-12 mt-2">
                        <h6 class="mb-0">Production House Strategy</h6>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Strategy Notes</label>
                        <textarea class="form-control" rows="4" name="strategy_notes">{{ old('strategy_notes', $form['strategy_notes']) }}</textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Blog / News CMS</button>
                        <div class="upload-progress-wrap" id="blogUploadProgressWrap">
                            <div class="progress mt-2" role="progressbar" aria-label="Upload progress" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="blogUploadProgressBar" style="width: 0%">0%</div>
                            </div>
                            <small class="text-muted" id="blogUploadProgressText">Menyiapkan upload...</small>
                            <small class="text-muted d-block" id="blogUploadSummaryText"></small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Comment Moderation Queue</h5>
                <p class="text-muted">Pending komentar: {{ $pendingCommentsCount }}</p>
                @if (empty($commentsQueue))
                    <p class="mb-0">Belum ada komentar dari publik.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Post</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Komentar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($commentsQueue as $comment)
                                    <tr>
                                        <td>{{ $comment['post_title'] ?? '-' }}</td>
                                        <td>{{ $comment['name'] ?? '-' }}</td>
                                        <td>{{ $comment['email'] ?? '-' }}</td>
                                        <td style="min-width:260px;">{{ $comment['comment'] ?? '-' }}</td>
                                        <td>{{ ucfirst((string) ($comment['status'] ?? 'pending')) }}</td>
                                        <td style="min-width:320px;">
                                            <form method="POST" action="{{ route('dashboard-website-cms-blog-news.comments.moderate', ['commentId' => $comment['id'] ?? '']) }}" class="d-flex flex-column gap-2">
                                                @csrf
                                                <select class="form-select form-select-sm" name="moderation_action">
                                                    <option value="approve">Approve</option>
                                                    <option value="reject">Reject</option>
                                                    <option value="delete">Delete</option>
                                                </select>
                                                <input class="form-control form-control-sm" type="text" name="admin_reply" value="{{ $comment['admin_reply'] ?? '' }}" placeholder="Balasan admin (opsional)">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Revision History</h5>
                <p class="text-muted">Total revision tersimpan: {{ $revisionCount }}</p>
                @if (empty($revisions))
                    <p class="mb-0">Belum ada revision.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Saved At</th>
                                    <th>Summary</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($revisions as $index => $revision)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $revision['saved_at'] ?? '-' }}</td>
                                        <td>{{ $revision['summary'] ?? 'Revision' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('dashboard-website-cms-blog-news.update') }}">
                                                @csrf
                                                <input type="hidden" name="action" value="restore">
                                                <input type="hidden" name="restore_revision_index" value="{{ $index }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Restore</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    (() => {
        const form = document.querySelector('form[action="{{ route("dashboard-website-cms-blog-news.update") }}"]');
        const repeater = document.getElementById('postRepeater');
        const addButton = document.getElementById('btnAddPost');
        const template = document.getElementById('postCardTemplate');
        const hiddenJson = document.getElementById('posts_json_hidden');
        const manualJson = document.getElementById('posts_json_manual');
        const uploadProgressWrap = document.getElementById('blogUploadProgressWrap');
        const uploadProgressBar = document.getElementById('blogUploadProgressBar');
        const uploadProgressText = document.getElementById('blogUploadProgressText');
        const uploadSummaryText = document.getElementById('blogUploadSummaryText');
        const searchInput = document.getElementById('postSearchInput');
        const categoryFilter = document.getElementById('postCategoryFilter');
        const tagFilter = document.getElementById('postTagFilter');
        const sortMode = document.getElementById('postSortMode');
        let draggingCard = null;

        if (!(form instanceof HTMLFormElement) || !(repeater instanceof HTMLElement) || !(template instanceof HTMLTemplateElement) || !(hiddenJson instanceof HTMLInputElement)) {
            return;
        }

        const reindex = () => {
            const cards = Array.from(repeater.querySelectorAll('.js-post-card'));
            cards.forEach((card, index) => {
                const order = card.querySelector('.js-post-order');
                if (order instanceof HTMLElement) {
                    order.textContent = `${index + 1}`;
                }
                card.querySelectorAll('[name]').forEach((field) => {
                    const current = field.getAttribute('name') || '';
                    field.setAttribute('name', current.replace(/posts_form\[\d+\]/, `posts_form[${index}]`));
                });
            });
        };

        const createCard = (index) => template.innerHTML
            .replaceAll('__INDEX__', `${index}`)
            .replaceAll('__ORDER__', `${index + 1}`);

        const parseComma = (value) => `${value || ''}`
            .split(',')
            .map((item) => item.trim())
            .filter((item) => item !== '');

        const collectPosts = () => {
            const cards = Array.from(repeater.querySelectorAll('.js-post-card'));
            return cards.map((card) => {
                const get = (suffix) => {
                    const field = card.querySelector(`[name$="${suffix}"]`);
                    if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement) {
                        return `${field.value || ''}`.trim();
                    }
                    return '';
                };
                const getBool = (suffix) => {
                    const field = card.querySelector(`[name$="${suffix}"]`);
                    return field instanceof HTMLInputElement ? field.checked : false;
                };
                const title = get('[title]');
                const slugInput = get('[slug]');
                const slug = slugInput !== '' ? slugInput : title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

                return {
                    title,
                    slug,
                    media_type: get('[media_type]') || 'image',
                    featured_image: get('[featured_image]'),
                    media_video_url: get('[media_video_url]'),
                    content_html: get('[content_html]'),
                    excerpt: get('[excerpt]'),
                    author: get('[author]'),
                    published_at: get('[published_at]'),
                    categories: parseComma(get('[categories_text]')),
                    tags: parseComma(get('[tags_text]')),
                    seo_title: get('[seo_title]'),
                    seo_description: get('[seo_description]'),
                    canonical_url: get('[canonical_url]'),
                    og_title: get('[og_title]'),
                    og_description: get('[og_description]'),
                    og_image: get('[og_image]'),
                    comments_enabled: getBool('[comments_enabled]'),
                    pingbacks_enabled: getBool('[pingbacks_enabled]'),
                    status: get('[status]') || 'draft',
                    reading_time_minutes: Number(get('[reading_time_minutes]') || 4),
                    is_featured: getBool('[is_featured]'),
                    is_popular: getBool('[is_popular]'),
                    related_posts: parseComma(get('[related_posts_text]')),
                    view_count: Number(get('[view_count]') || 0),
                    like_count: Number(get('[like_count]') || 0),
                    comment_count: Number(get('[comment_count]') || 0),
                };
            }).filter((post) => post.title !== '');
        };

        const syncHidden = () => {
            const posts = collectPosts();
            const json = JSON.stringify(posts);
            hiddenJson.value = json;
            if (manualJson instanceof HTMLTextAreaElement) {
                manualJson.value = JSON.stringify(posts, null, 2);
            }
            updateFilterOptions();
            applySearchFilterSort();
        };

        const updateFilterOptions = () => {
            const posts = collectPosts();
            const categories = [...new Set(posts.flatMap((post) => post.categories || []))].sort();
            const tags = [...new Set(posts.flatMap((post) => post.tags || []))].sort();
            if (categoryFilter instanceof HTMLSelectElement) {
                const current = categoryFilter.value;
                categoryFilter.innerHTML = '<option value="all">Filter Kategori: All</option>' + categories.map((c) => `<option value="${c}">${c}</option>`).join('');
                categoryFilter.value = categories.includes(current) ? current : 'all';
            }
            if (tagFilter instanceof HTMLSelectElement) {
                const current = tagFilter.value;
                tagFilter.innerHTML = '<option value="all">Filter Tag: All</option>' + tags.map((t) => `<option value="${t}">${t}</option>`).join('');
                tagFilter.value = tags.includes(current) ? current : 'all';
            }
        };

        const applySearchFilterSort = () => {
            const query = (searchInput instanceof HTMLInputElement ? searchInput.value : '').trim().toLowerCase();
            const category = categoryFilter instanceof HTMLSelectElement ? categoryFilter.value : 'all';
            const tag = tagFilter instanceof HTMLSelectElement ? tagFilter.value : 'all';
            const mode = sortMode instanceof HTMLSelectElement ? sortMode.value : 'newest';
            const cards = Array.from(repeater.querySelectorAll('.js-post-card'));

            cards.forEach((card) => {
                const title = (card.querySelector('[name$="[title]"]')?.value || '').toLowerCase();
                const content = (card.querySelector('[name$="[content_html]"]')?.value || '').toLowerCase();
                const cats = (card.querySelector('[name$="[categories_text]"]')?.value || '').toLowerCase();
                const tags = (card.querySelector('[name$="[tags_text]"]')?.value || '').toLowerCase();
                const visibleQuery = query === '' || title.includes(query) || content.includes(query) || cats.includes(query) || tags.includes(query);
                const visibleCategory = category === 'all' || parseComma(card.querySelector('[name$="[categories_text]"]')?.value || '').includes(category);
                const visibleTag = tag === 'all' || parseComma(card.querySelector('[name$="[tags_text]"]')?.value || '').includes(tag);
                card.style.display = visibleQuery && visibleCategory && visibleTag ? '' : 'none';
            });

            const visibleCards = cards.filter((card) => card.style.display !== 'none');
            const sorted = visibleCards.sort((a, b) => {
                if (mode === 'alphabetical') {
                    const aTitle = (a.querySelector('[name$="[title]"]')?.value || '').toLowerCase();
                    const bTitle = (b.querySelector('[name$="[title]"]')?.value || '').toLowerCase();
                    return aTitle.localeCompare(bTitle);
                }
                if (mode === 'popular') {
                    const aScore = Number((a.querySelector('[name$="[view_count]"]')?.value || '0')) + Number((a.querySelector('[name$="[like_count]"]')?.value || '0')) + Number((a.querySelector('[name$="[comment_count]"]')?.value || '0'));
                    const bScore = Number((b.querySelector('[name$="[view_count]"]')?.value || '0')) + Number((b.querySelector('[name$="[like_count]"]')?.value || '0')) + Number((b.querySelector('[name$="[comment_count]"]')?.value || '0'));
                    return bScore - aScore;
                }
                const aDate = Date.parse((a.querySelector('[name$="[published_at]"]')?.value || '1970-01-01'));
                const bDate = Date.parse((b.querySelector('[name$="[published_at]"]')?.value || '1970-01-01'));
                return bDate - aDate;
            });
            sorted.forEach((card) => repeater.appendChild(card));
            reindex();
        };

        addButton?.addEventListener('click', () => {
            const nextIndex = repeater.querySelectorAll('.js-post-card').length;
            repeater.insertAdjacentHTML('beforeend', createCard(nextIndex));
            reindex();
            syncHidden();
        });

        repeater.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }
            const card = target.closest('.js-post-card');
            if (!(card instanceof HTMLElement) || !(card.parentElement instanceof HTMLElement)) {
                return;
            }
            if (target.classList.contains('js-post-remove')) {
                card.remove();
                reindex();
                syncHidden();
                return;
            }
            if (target.classList.contains('js-post-up')) {
                const prev = card.previousElementSibling;
                if (prev instanceof HTMLElement) {
                    card.parentElement.insertBefore(card, prev);
                }
                reindex();
                syncHidden();
                return;
            }
            if (target.classList.contains('js-post-down')) {
                const next = card.nextElementSibling;
                if (next instanceof HTMLElement) {
                    card.parentElement.insertBefore(next, card);
                }
                reindex();
                syncHidden();
            }
        });

        repeater.addEventListener('dragstart', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains('js-post-card')) {
                return;
            }
            draggingCard = target;
            target.classList.add('dragging');
            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
            }
        });
        repeater.addEventListener('dragend', () => {
            if (draggingCard instanceof HTMLElement) {
                draggingCard.classList.remove('dragging');
            }
            repeater.querySelectorAll('.js-post-card').forEach((card) => card.classList.remove('drop-target'));
            draggingCard = null;
        });
        repeater.addEventListener('dragover', (event) => {
            event.preventDefault();
            const target = event.target;
            if (!(target instanceof HTMLElement) || !(draggingCard instanceof HTMLElement)) {
                return;
            }
            const dropCard = target.closest('.js-post-card');
            repeater.querySelectorAll('.js-post-card').forEach((card) => card.classList.remove('drop-target'));
            if (dropCard instanceof HTMLElement && dropCard !== draggingCard) {
                dropCard.classList.add('drop-target');
            }
        });
        repeater.addEventListener('drop', (event) => {
            event.preventDefault();
            const target = event.target;
            if (!(target instanceof HTMLElement) || !(draggingCard instanceof HTMLElement)) {
                return;
            }
            const dropCard = target.closest('.js-post-card');
            if (!(dropCard instanceof HTMLElement) || dropCard === draggingCard) {
                return;
            }
            const cards = Array.from(repeater.querySelectorAll('.js-post-card'));
            const dragIndex = cards.indexOf(draggingCard);
            const dropIndex = cards.indexOf(dropCard);
            if (dragIndex < dropIndex) {
                repeater.insertBefore(draggingCard, dropCard.nextSibling);
            } else {
                repeater.insertBefore(draggingCard, dropCard);
            }
            reindex();
            syncHidden();
        });

        repeater.addEventListener('input', syncHidden);
        repeater.addEventListener('change', syncHidden);
        searchInput?.addEventListener('input', applySearchFilterSort);
        categoryFilter?.addEventListener('change', applySearchFilterSort);
        tagFilter?.addEventListener('change', applySearchFilterSort);
        sortMode?.addEventListener('change', applySearchFilterSort);
        manualJson?.addEventListener('input', () => {
            hiddenJson.value = manualJson.value;
        });
        form.addEventListener('submit', () => {
            if (manualJson instanceof HTMLTextAreaElement && manualJson.value.trim() !== '' && manualJson.value.trim() !== hiddenJson.value.trim()) {
                hiddenJson.value = manualJson.value;
                return;
            }
            syncHidden();
        });

        let uploading = false;
        const maxPostBytes = 640 * 1024 * 1024;
        const toMbText = (bytes) => `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
        const toSizeText = (bytes) => {
            if (bytes < 1024) {
                return `${bytes}B`;
            }
            if (bytes < 1024 * 1024) {
                return `${(bytes / 1024).toFixed(1)}KB`;
            }
            if (bytes < 1024 * 1024 * 1024) {
                return `${(bytes / (1024 * 1024)).toFixed(1)}MB`;
            }
            return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)}GB`;
        };
        const toEtaText = (seconds) => {
            if (!Number.isFinite(seconds) || seconds < 0) {
                return '-';
            }
            const totalSeconds = Math.round(seconds);
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            return mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
        };
        form.addEventListener('submit', (event) => {
            if (uploading) {
                event.preventDefault();
                return;
            }
            event.preventDefault();
            const totalUploadBytes = Array.from(form.querySelectorAll('input[type="file"]')).reduce((carry, input) => {
                if (!(input instanceof HTMLInputElement) || !input.files) {
                    return carry;
                }
                return carry + Array.from(input.files).reduce((sum, file) => sum + file.size, 0);
            }, 0);
            if (totalUploadBytes > maxPostBytes) {
                if (uploadProgressWrap instanceof HTMLElement) {
                    uploadProgressWrap.classList.add('active');
                }
                if (uploadProgressBar instanceof HTMLElement) {
                    uploadProgressBar.style.width = '100%';
                    uploadProgressBar.textContent = 'Batas Terlampaui';
                }
                if (uploadProgressText instanceof HTMLElement) {
                    uploadProgressText.textContent = `Total upload ${toMbText(totalUploadBytes)} melebihi batas request ${toMbText(maxPostBytes)}. Kurangi jumlah file atau ukuran video.`;
                }
                if (uploadSummaryText instanceof HTMLElement) {
                    uploadSummaryText.textContent = '';
                }
                return;
            }
            const selectedFiles = Array.from(form.querySelectorAll('input[type="file"]'))
                .flatMap((input) => input instanceof HTMLInputElement && input.files ? Array.from(input.files) : []);
            const fileCount = selectedFiles.length;
            const fileNames = selectedFiles.slice(0, 3).map((file) => file.name);
            uploading = true;
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = true;
            }
            if (uploadProgressWrap instanceof HTMLElement) {
                uploadProgressWrap.classList.add('active');
            }
            if (uploadProgressBar instanceof HTMLElement) {
                uploadProgressBar.style.width = '0%';
                uploadProgressBar.textContent = '0%';
            }
            if (uploadProgressText instanceof HTMLElement) {
                uploadProgressText.textContent = 'Mengupload file media...';
            }
            if (uploadSummaryText instanceof HTMLElement) {
                const namesText = fileNames.length > 0 ? ` (${fileNames.join(', ')}${fileCount > fileNames.length ? ', ...' : ''})` : '';
                uploadSummaryText.textContent = `File: ${fileCount} · Total: ${toSizeText(totalUploadBytes)}${namesText}`;
            }

            const formActionUrl = form.getAttribute('action') || window.location.href;
            const xhr = new XMLHttpRequest();
            const uploadStartedAt = Date.now();
            xhr.open((form.method || 'POST').toUpperCase(), formActionUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.addEventListener('progress', (progressEvent) => {
                if (!progressEvent.lengthComputable) {
                    return;
                }
                const percent = Math.min(100, Math.round((progressEvent.loaded / progressEvent.total) * 100));
                if (uploadProgressBar instanceof HTMLElement) {
                    uploadProgressBar.style.width = `${percent}%`;
                    uploadProgressBar.textContent = `${percent}%`;
                }
                if (uploadProgressText instanceof HTMLElement) {
                    const elapsed = Math.max((Date.now() - uploadStartedAt) / 1000, 0.1);
                    const speed = progressEvent.loaded / elapsed;
                    const remainingBytes = Math.max(progressEvent.total - progressEvent.loaded, 0);
                    const eta = speed > 0 ? remainingBytes / speed : 0;
                    uploadProgressText.textContent = percent < 100
                        ? `Mengupload file media... ETA ${toEtaText(eta)}`
                        : 'Upload selesai, memproses...';
                }
                if (uploadSummaryText instanceof HTMLElement) {
                    const elapsed = Math.max((Date.now() - uploadStartedAt) / 1000, 0.1);
                    const speed = progressEvent.loaded / elapsed;
                    uploadSummaryText.textContent = `Terkirim ${toSizeText(progressEvent.loaded)} / ${toSizeText(progressEvent.total)} · Kecepatan ${toSizeText(Math.round(speed))}/s`;
                }
            });
            xhr.onload = () => {
                if (uploadProgressBar instanceof HTMLElement) {
                    uploadProgressBar.style.width = '100%';
                    uploadProgressBar.textContent = '100%';
                }
                if (xhr.status >= 200 && xhr.status < 400) {
                    window.location.href = xhr.responseURL || formActionUrl;
                    return;
                }
                uploading = false;
                if (submitButton instanceof HTMLButtonElement) {
                    submitButton.disabled = false;
                }
                if (uploadProgressText instanceof HTMLElement) {
                    uploadProgressText.textContent = 'Upload gagal. Silakan periksa input lalu coba lagi.';
                }
                if (xhr.responseText) {
                    document.open();
                    document.write(xhr.responseText);
                    document.close();
                }
            };
            xhr.onerror = () => {
                uploading = false;
                if (submitButton instanceof HTMLButtonElement) {
                    submitButton.disabled = false;
                }
                if (uploadProgressText instanceof HTMLElement) {
                    uploadProgressText.textContent = 'Terjadi kendala jaringan saat upload.';
                }
            };
            xhr.send(new FormData(form));
        });

        reindex();
        syncHidden();
    })();
</script>
@endsection
