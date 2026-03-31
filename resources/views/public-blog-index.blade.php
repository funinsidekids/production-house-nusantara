<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if (!empty($cmsGeneral['favicon_32_url']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $cmsGeneral['favicon_32_url'] }}">
        <link rel="shortcut icon" href="{{ $cmsGeneral['favicon_32_url'] }}">
    @endif
    @if (!empty($cmsGeneral['apple_touch_icon_url']))
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $cmsGeneral['apple_touch_icon_url'] }}">
    @endif
    <style>
        :root{color-scheme:dark;--bg:#070707;--line:rgba(255,255,255,.15);--text:#f7f5ef;--muted:rgba(247,245,239,.72);--accent:#dca85c;}
        *{box-sizing:border-box;}
        body{margin:0;font-family:Inter,system-ui,sans-serif;background:radial-gradient(circle at 15% -10%, rgba(220,168,92,.18), transparent 45%),var(--bg);color:var(--text);}
@include('partials.public-header-css')
        .container{width:min(1200px,92%);margin:0 auto;}
        .shell{padding:8.4rem 0 2.5rem;}
        .topbar{display:flex;justify-content:space-between;align-items:end;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
        .topbar h1{margin:0 0 .2rem;font-size:clamp(1.4rem,2.9vw,2.2rem);}
        .topbar p{margin:0;color:var(--muted);}
        .toolbar{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:.6rem;margin-bottom:1rem;}
        .toolbar input,.toolbar select{width:100%;border:1px solid var(--line);border-radius:.65rem;background:rgba(255,255,255,.03);color:var(--text);padding:.55rem .62rem;}
        .layout{display:grid;grid-template-columns:minmax(0,1.9fr) minmax(280px,.9fr);gap:1rem;align-items:start;}
        .posts{display:grid;gap:.8rem;}
        .posts.grid{grid-template-columns:repeat(3,minmax(0,1fr));}
        .posts.list{grid-template-columns:1fr;}
        .post{border:1px solid var(--line);border-radius:.85rem;overflow:hidden;background:rgba(255,255,255,.02);}
        .post a{text-decoration:none;color:inherit;display:block;}
        .post-media{width:100%;aspect-ratio:16/9;overflow:hidden;background:#161616;}
        .post-media img,.post-media video,.post-media iframe{width:100%;height:100%;object-fit:cover;display:block;border:0;}
        .post .body{padding:.75rem;}
        .post h3{margin:0 0 .35rem;font-size:1rem;}
        .meta{display:flex;flex-wrap:wrap;gap:.45rem;color:var(--muted);font-size:.78rem;margin-bottom:.35rem;}
        .chip{border:1px solid var(--line);padding:.2rem .5rem;border-radius:999px;font-size:.74rem;color:var(--muted);}
        .excerpt{color:var(--muted);font-size:.9rem;line-height:1.5;}
        .read{display:inline-block;margin-top:.45rem;color:var(--accent);font-size:.85rem;}
        .side-card{border:1px solid var(--line);border-radius:.85rem;padding:.8rem;background:rgba(255,255,255,.02);margin-bottom:.8rem;}
        .side-card h4{margin:0 0 .6rem;font-size:1rem;}
        .side-card ul{list-style:none;margin:0;padding:0;display:grid;gap:.4rem;}
        .side-card li a{color:var(--text);text-decoration:none;font-size:.9rem;}
        .side-card li a:hover{color:var(--accent);}
        .pager{display:flex;justify-content:center;gap:.5rem;margin-top:1rem;flex-wrap:wrap;}
        .pager a,.pager span{border:1px solid var(--line);padding:.35rem .65rem;border-radius:999px;color:var(--text);text-decoration:none;font-size:.85rem;}
        .infinite-wrap{display:flex;justify-content:center;margin-top:1rem;}
        .infinite-wrap a{border:1px solid var(--line);padding:.45rem .8rem;border-radius:999px;color:var(--text);text-decoration:none;}
        @media(max-width:1080px){.posts.grid{grid-template-columns:repeat(2,minmax(0,1fr));}.layout{grid-template-columns:1fr;}.toolbar{grid-template-columns:1fr 1fr;}}
        @media(max-width:640px){.posts.grid{grid-template-columns:1fr;}.toolbar{grid-template-columns:1fr;}}
    </style>
</head>
<body>
@include('partials.public-header-markup', [
    'headerConfig' => $headerConfig,
    'isInnerPage' => $isInnerPage ?? true,
    'headerBrandHref' => '/',
    'headerBrandText' => $cmsGeneral['site_title'],
    'headerBrandAlt' => $cmsGeneral['site_title'],
])
<main class="shell">
    <div class="container">
        <div class="topbar">
            <div>
                <h1>Blog / News</h1>
                <p>Behind the scenes, production tips, company news, dan industry insights.</p>
            </div>
        </div>
        <form method="GET" class="toolbar">
            <input type="text" name="q" placeholder="Search judul / isi / kategori / tag" value="{{ $searchQuery }}">
            <select name="category">
                <option value="">Kategori: Semua</option>
                @foreach ($allCategories as $item)
                    <option value="{{ $item }}" @selected($activeCategory === $item)>{{ $item }}</option>
                @endforeach
            </select>
            <select name="tag">
                <option value="">Tag: Semua</option>
                @foreach ($allTags as $item)
                    <option value="{{ $item }}" @selected($activeTag === $item)>{{ $item }}</option>
                @endforeach
            </select>
            <select name="sort">
                <option value="newest" @selected($sort === 'newest')>Sort: Terbaru</option>
                <option value="popular" @selected($sort === 'popular')>Sort: Populer</option>
                <option value="alphabetical" @selected($sort === 'alphabetical')>Sort: A-Z</option>
            </select>
            <select name="view">
                <option value="grid" @selected($viewMode === 'grid')>Mode: Grid</option>
                <option value="list" @selected($viewMode === 'list')>Mode: List</option>
            </select>
        </form>
        <div class="layout">
            <section>
                <div class="posts {{ $viewMode }}" id="blogPosts">
                    @forelse ($posts as $post)
                        <article class="post">
                            <a href="{{ route('blog.show', ['slug' => $post['slug']]) }}">
                                <div class="post-media">
                                    @if (($post['media_type'] ?? 'image') === 'video' && !empty($post['media_video_url']))
                                        @if (!empty($post['media_embed_url']) && \Illuminate\Support\Str::contains($post['media_embed_url'], ['youtube.com/embed/', 'player.vimeo.com/video/']))
                                            <iframe src="{{ $post['media_embed_url'] }}" title="{{ $post['title'] }}" loading="lazy" allowfullscreen></iframe>
                                        @else
                                            <video src="{{ $post['media_video_url'] }}" muted playsinline preload="metadata"></video>
                                        @endif
                                    @else
                                        <img src="{{ $post['featured_image'] !== '' ? $post['featured_image'] : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80' }}" alt="{{ $post['title'] }}">
                                    @endif
                                </div>
                                <div class="body">
                                    <div class="meta">
                                        <span>{{ $post['author'] }}</span>
                                        <span>{{ $post['published_at'] !== '' ? \Illuminate\Support\Carbon::parse($post['published_at'])->translatedFormat('d M Y') : '-' }}</span>
                                        <span>{{ $post['reading_time_minutes'] }} min</span>
                                    </div>
                                    <h3>{{ $post['title'] }}</h3>
                                    <div class="meta">
                                        @foreach (array_slice($post['categories'], 0, 2) as $cat)
                                            <span class="chip">{{ $cat }}</span>
                                        @endforeach
                                    </div>
                                    <p class="excerpt">{{ $post['excerpt'] !== '' ? $post['excerpt'] : \Illuminate\Support\Str::limit(strip_tags($post['content_html']), 140) }}</p>
                                    <span class="read">Read More →</span>
                                </div>
                            </a>
                        </article>
                    @empty
                        <article class="side-card"><p class="mb-0">Belum ada artikel yang cocok dengan filter saat ini.</p></article>
                    @endforelse
                </div>
                @if (($layout['pagination_mode'] ?? 'pagination') === 'pagination')
                    <div class="pager">{{ $posts->links() }}</div>
                @else
                    <div class="infinite-wrap" id="infiniteWrap">
                        @if ($posts->nextPageUrl())
                            <a id="infiniteNext" href="{{ $posts->nextPageUrl() }}">Load More</a>
                        @endif
                    </div>
                @endif
            </section>
            @if (($layout['sidebar_enabled'] ?? true))
                <aside>
                    <div class="side-card">
                        <h4>🔥 Featured Post</h4>
                        <ul>
                            @forelse ($featuredPosts as $post)
                                <li><a href="{{ route('blog.show', ['slug' => $post['slug']]) }}">{{ $post['title'] }}</a></li>
                            @empty
                                <li>-</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="side-card">
                        <h4>⭐ Popular Post</h4>
                        <ul>
                            @forelse ($popularPosts as $post)
                                <li><a href="{{ route('blog.show', ['slug' => $post['slug']]) }}">{{ $post['title'] }}</a></li>
                            @empty
                                <li>-</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="side-card">
                        <h4>Kategori</h4>
                        <ul>
                            @foreach ($allCategories as $item)
                                <li><a href="{{ route('blog.index', array_merge(request()->query(), ['category' => $item, 'page' => 1])) }}">{{ $item }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="side-card">
                        <h4>Tags</h4>
                        <div class="meta">
                            @foreach ($allTags as $item)
                                <a class="chip" href="{{ route('blog.index', array_merge(request()->query(), ['tag' => $item, 'page' => 1])) }}">{{ $item }}</a>
                            @endforeach
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>
</main>
@include('partials.public-header-script')
@if (($layout['pagination_mode'] ?? 'pagination') === 'infinite')
    <script>
        (() => {
            const next = document.getElementById('infiniteNext');
            if (!(next instanceof HTMLAnchorElement)) {
                return;
            }
            const wrap = document.getElementById('infiniteWrap');
            if (!(wrap instanceof HTMLElement)) {
                return;
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        window.location.href = next.href;
                    }
                });
            }, { rootMargin: '120px 0px' });
            observer.observe(wrap);
        })();
    </script>
@endif
</body>
</html>
