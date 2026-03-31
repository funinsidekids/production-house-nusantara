<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($ogImage !== '')
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    @if (!empty($cmsGeneral['favicon_32_url']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $cmsGeneral['favicon_32_url'] }}">
        <link rel="shortcut icon" href="{{ $cmsGeneral['favicon_32_url'] }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{color-scheme:dark;--bg:#070707;--line:rgba(255,255,255,.15);--text:#f7f5ef;--muted:rgba(247,245,239,.72);--accent:#dca85c;}
        *{box-sizing:border-box;}
        body{margin:0;font-family:Inter,system-ui,sans-serif;background:radial-gradient(circle at 10% -5%, rgba(220,168,92,.18), transparent 45%),var(--bg);color:var(--text);}
@include('partials.public-header-css')
        .container{width:min(1200px,92%);margin:0 auto;}
        .shell{padding:8.4rem 0 2.8rem;}
        .layout{display:grid;grid-template-columns:minmax(0,1.9fr) minmax(280px,.9fr);gap:1rem;}
        .article{border:1px solid var(--line);border-radius:.9rem;overflow:hidden;background:rgba(255,255,255,.02);}
        .hero-media{width:100%;aspect-ratio:16/9;overflow:hidden;background:#151515;}
        .hero-media img,.hero-media video,.hero-media iframe{width:100%;height:100%;object-fit:cover;display:block;border:0;}
        .body{padding:1rem;}
        h1{margin:.15rem 0 .55rem;font-size:clamp(1.5rem,3vw,2.2rem);}
        .meta{display:flex;flex-wrap:wrap;gap:.6rem;color:var(--muted);font-size:.84rem;margin-bottom:.7rem;}
        .chips{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.7rem;}
        .chip{border:1px solid var(--line);padding:.22rem .55rem;border-radius:999px;font-size:.74rem;color:var(--muted);}
        .content{line-height:1.7;color:var(--text);}
        .content iframe{width:100%;aspect-ratio:16/9;border:0;border-radius:.6rem;}
        .content img{max-width:100%;height:auto;border-radius:.55rem;}
        .share{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:1rem;}
        .share a{border:1px solid var(--line);border-radius:999px;padding:.35rem .75rem;color:var(--text);text-decoration:none;font-size:.82rem;}
        .like-btn{border:1px solid var(--line);border-radius:999px;padding:.35rem .75rem;background:transparent;color:var(--text);cursor:pointer;font-size:.82rem;}
        .section{border:1px solid var(--line);border-radius:.85rem;padding:.8rem;background:rgba(255,255,255,.02);margin-top:.9rem;}
        .section h3{margin:0 0 .55rem;}
        .list{list-style:none;margin:0;padding:0;display:grid;gap:.45rem;}
        .list a{color:var(--text);text-decoration:none;}
        .list a:hover{color:var(--accent);}
        .comments form{display:grid;gap:.5rem;}
        .comments input,.comments textarea{width:100%;border:1px solid var(--line);border-radius:.6rem;background:rgba(255,255,255,.03);color:var(--text);padding:.5rem .62rem;}
        .comments button{border:1px solid var(--line);border-radius:999px;background:transparent;color:var(--text);padding:.45rem .8rem;width:max-content;cursor:pointer;}
        .comment-list{display:grid;gap:.7rem;margin-top:.8rem;}
        .comment-item{border:1px solid var(--line);border-radius:.6rem;padding:.55rem .65rem;}
        .comment-meta{font-size:.77rem;color:var(--muted);margin-bottom:.3rem;}
        .admin-reply{margin-top:.4rem;padding:.45rem;border-left:2px solid var(--accent);background:rgba(255,255,255,.02);font-size:.88rem;}
        .flash{border:1px solid var(--line);border-radius:.6rem;padding:.55rem .7rem;margin-bottom:.65rem;background:rgba(150,227,150,.1);}
        @media(max-width:980px){.layout{grid-template-columns:1fr;}}
    </style>
    <script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post['title'],
    'description' => $seoDescription,
    'author' => ['@type' => 'Person', 'name' => $post['author']],
    'datePublished' => $post['published_at'],
    'dateModified' => $post['published_at'],
    'image' => $post['featured_image'] !== '' ? [$post['featured_image']] : [],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonicalUrl],
    'publisher' => ['@type' => 'Organization', 'name' => $cmsGeneral['site_title']],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
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
        <div class="layout">
            <article>
                <div class="article">
                    <div class="hero-media">
                        @if (($post['media_type'] ?? 'image') === 'video' && !empty($post['media_video_url']))
                            @if (!empty($post['media_embed_url']) && \Illuminate\Support\Str::contains($post['media_embed_url'], ['youtube.com/embed/', 'player.vimeo.com/video/']))
                                <iframe src="{{ $post['media_embed_url'] }}" title="{{ $post['title'] }}" loading="lazy" allowfullscreen></iframe>
                            @else
                                <video src="{{ $post['media_video_url'] }}" controls playsinline preload="metadata"></video>
                            @endif
                        @else
                            <img src="{{ $post['featured_image'] !== '' ? $post['featured_image'] : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1400&q=80' }}" alt="{{ $post['title'] }}">
                        @endif
                    </div>
                    <div class="body">
                        <h1>{{ $post['title'] }}</h1>
                        <div class="meta">
                            <span>{{ $post['author'] }}</span>
                            <span>{{ $post['published_at'] !== '' ? \Illuminate\Support\Carbon::parse($post['published_at'])->translatedFormat('d M Y H:i') : '-' }}</span>
                            <span>{{ $post['reading_time_minutes'] }} min read</span>
                        </div>
                        <div class="chips">
                            @foreach ($post['categories'] as $cat)
                                <a class="chip" href="{{ route('blog.index', ['category' => $cat]) }}">{{ $cat }}</a>
                            @endforeach
                            @foreach ($post['tags'] as $tag)
                                <a class="chip" href="{{ route('blog.index', ['tag' => $tag]) }}">#{{ $tag }}</a>
                            @endforeach
                        </div>
                        <div class="content">{!! $post['content_html'] !!}</div>
                        <div class="share">
                            <button type="button" class="like-btn" id="likeBtn">❤ Like (<span id="likeCount">{{ $post['like_count'] }}</span>)</button>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener">Share Facebook</a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($canonicalUrl) }}&text={{ urlencode($post['title']) }}" target="_blank" rel="noopener">Share X</a>
                            <a href="https://wa.me/?text={{ urlencode($post['title'].' '.$canonicalUrl) }}" target="_blank" rel="noopener">Share WhatsApp</a>
                        </div>
                    </div>
                </div>

                <section class="section">
                    <h3>Related Post</h3>
                    <ul class="list">
                        @forelse ($relatedPosts as $item)
                            <li><a href="{{ route('blog.show', ['slug' => $item['slug']]) }}">{{ $item['title'] }}</a></li>
                        @empty
                            <li>-</li>
                        @endforelse
                    </ul>
                </section>

                @if ($commentsEnabled)
                    <section class="section comments">
                        <h3>Komentar</h3>
                        @if (session('success'))
                            <div class="flash">{{ session('success') }}</div>
                        @endif
                        <form method="POST" action="{{ route('blog.comment.store', ['slug' => $post['slug']]) }}">
                            @csrf
                            <input type="text" name="name" placeholder="Nama" required>
                            <input type="email" name="email" placeholder="Email" required>
                            <textarea rows="4" name="comment" placeholder="Komentar" required></textarea>
                            <button type="submit">Kirim Komentar</button>
                        </form>
                        <div class="comment-list">
                            @forelse ($approvedComments as $comment)
                                <article class="comment-item">
                                    <div class="comment-meta">{{ $comment['name'] ?? '-' }} · {{ !empty($comment['created_at']) ? \Illuminate\Support\Carbon::parse($comment['created_at'])->translatedFormat('d M Y H:i') : '-' }}</div>
                                    <div>{{ $comment['comment'] ?? '-' }}</div>
                                    @if (!empty($comment['admin_reply']))
                                        <div class="admin-reply">Balasan admin: {{ $comment['admin_reply'] }}</div>
                                    @endif
                                </article>
                            @empty
                                <article class="comment-item">Belum ada komentar yang disetujui.</article>
                            @endforelse
                        </div>
                    </section>
                @endif
            </article>

            <aside>
                <section class="section">
                    <h3>🔥 Featured Post</h3>
                    <ul class="list">
                        @forelse ($featuredPosts as $item)
                            <li><a href="{{ route('blog.show', ['slug' => $item['slug']]) }}">{{ $item['title'] }}</a></li>
                        @empty
                            <li>-</li>
                        @endforelse
                    </ul>
                </section>
                <section class="section">
                    <h3>⭐ Popular Post</h3>
                    <ul class="list">
                        @forelse ($popularPosts as $item)
                            <li><a href="{{ route('blog.show', ['slug' => $item['slug']]) }}">{{ $item['title'] }}</a></li>
                        @empty
                            <li>-</li>
                        @endforelse
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</main>
@include('partials.public-header-script')
<script>
    (() => {
        const likeBtn = document.getElementById('likeBtn');
        const likeCount = document.getElementById('likeCount');
        if (!(likeBtn instanceof HTMLButtonElement) || !(likeCount instanceof HTMLElement)) {
            return;
        }
        likeBtn.addEventListener('click', async () => {
            likeBtn.disabled = true;
            try {
                const response = await fetch("{{ route('blog.like', ['slug' => $post['slug']]) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });
                const payload = await response.json();
                if (response.ok && payload.like_count !== undefined) {
                    likeCount.textContent = `${payload.like_count}`;
                }
            } catch (error) {
            } finally {
                likeBtn.disabled = false;
            }
        });
    })();
</script>
</body>
</html>
