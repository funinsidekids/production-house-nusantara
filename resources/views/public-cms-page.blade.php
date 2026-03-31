<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageData['title'] ?? $cmsGeneral['site_title'] }} · {{ $cmsGeneral['site_title'] }}</title>
    <meta name="description" content="{{ $cmsGeneral['site_description'] !== '' ? $cmsGeneral['site_description'] : $cmsGeneral['tagline'] }}">
    @if (!empty($cmsGeneral['favicon_32_url']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $cmsGeneral['favicon_32_url'] }}">
        <link rel="shortcut icon" href="{{ $cmsGeneral['favicon_32_url'] }}">
    @endif
    @if (!empty($cmsGeneral['apple_touch_icon_url']))
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $cmsGeneral['apple_touch_icon_url'] }}">
    @endif
    <style>
@include('partials.public-base-css')
@include('partials.public-ui-primitives-css')
        :root {
            color-scheme: dark;
            --bg: #070707;
            --surface: #111;
            --line: rgba(255,255,255,.14);
            --text: #f7f5ef;
            --muted: rgba(247,245,239,.72);
            --accent: #e0ab56;
        }
        body {
            background: radial-gradient(circle at 20% -5%, rgba(224,171,86,.2), transparent 45%), var(--bg);
            color: var(--text);
        }
@include('partials.public-header-css')
        .page-shell {
            padding: 8.4rem 0 3rem;
        }
        .page-head {
            margin-bottom: 1.4rem;
        }
        .page-head h1 {
            margin: 0 0 .35rem;
            font-size: clamp(1.4rem, 3vw, 2.15rem);
        }
        .page-head p {
            margin: 0;
            color: var(--muted);
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .card {
            border: 1px solid var(--line);
            background: linear-gradient(165deg, rgba(42,26,10,.46), rgba(0,0,0,.88));
            --public-card-radius: .9rem;
            --public-card-padding: 1rem;
            --public-card-bg: linear-gradient(165deg, rgba(42,26,10,.46), rgba(0,0,0,.88));
        }
        .card h3 {
            margin: 0 0 .45rem;
        }
        .card p {
            margin: 0;
            color: var(--muted);
        }
        .chips {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }
        .chip {}
        .faq-filter {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-bottom: .9rem;
        }
        .faq-filter button {
            border: 1px solid var(--line);
            background: transparent;
            color: var(--text);
            border-radius: var(--public-btn-radius);
            padding: .38rem .78rem;
            cursor: pointer;
        }
        .faq-filter button.active {
            background: linear-gradient(135deg, #f3c469, #d89a2b);
            border-color: transparent;
            color: #1c1103;
            font-weight: 700;
        }
        .faq-item {
            border: 1px solid var(--line);
            border-radius: .75rem;
            margin-bottom: .65rem;
            overflow: hidden;
        }
        .faq-item button {
            width: 100%;
            border: 0;
            background: rgba(255,255,255,.02);
            color: var(--text);
            text-align: left;
            padding: .75rem .85rem;
            cursor: pointer;
            font-weight: 600;
        }
        .faq-item .faq-answer {
            display: none;
            padding: .85rem;
            color: var(--muted);
            border-top: 1px solid var(--line);
        }
        .faq-item.open .faq-answer {
            display: block;
        }
        .legal-body {
            border: 1px solid var(--line);
            border-radius: .8rem;
            background: rgba(255,255,255,.02);
            padding: 1rem;
            color: var(--muted);
        }
        .legal-body h1,
        .legal-body h2,
        .legal-body h3 {
            color: var(--text);
        }
        .version-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: .9rem;
        }
        .version-table th,
        .version-table td {
            border: 1px solid var(--line);
            padding: .55rem .6rem;
            text-align: left;
            font-size: .9rem;
        }
        .layout-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }
        .layout-shell.sidebar {
            grid-template-columns: minmax(0, 1.7fr) minmax(260px, .7fr);
            align-items: start;
        }
        .layout-main,
        .layout-sidebar {
            border: 1px solid var(--line);
            border-radius: .8rem;
            background: rgba(255,255,255,.02);
            padding: 1rem;
        }
        .layout-main h1,
        .layout-main h2,
        .layout-main h3,
        .layout-sidebar h1,
        .layout-sidebar h2,
        .layout-sidebar h3 {
            color: var(--text);
        }
        .layout-main p,
        .layout-sidebar p {
            color: var(--muted);
        }
        .section-block {
            margin-top: .9rem;
            border: 1px solid var(--line);
            border-radius: .7rem;
            padding: .75rem;
            background: rgba(255,255,255,.02);
        }
        .section-block h4 {
            margin: 0 0 .45rem;
        }
        @media (max-width: 980px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            .layout-shell.sidebar {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @if (trim((string) $customCss) !== '')
        <style id="publicCmsCustomCss"></style>
        <script>
            (() => {
                const node = document.getElementById('publicCmsCustomCss');
                if (!(node instanceof HTMLStyleElement)) {
                    return;
                }
                const customCssBase64 = '{{ base64_encode((string) $customCss) }}';
                node.textContent = atob(customCssBase64);
            })();
        </script>
    @endif
</head>
<body>
@include('partials.public-header-markup', [
    'headerConfig' => $headerConfig,
    'isInnerPage' => $isInnerPage ?? true,
    'headerBrandHref' => '/',
    'headerBrandText' => $cmsGeneral['site_title'],
    'headerBrandAlt' => $cmsGeneral['site_title'],
])
<main class="page-shell">
    <div class="container">
        <div class="page-head">
            <h1>{{ $pageData['title'] ?? '' }}</h1>
            <p>{{ $pageData['subtitle'] ?? '' }}</p>
        </div>

        @if ($pageKey === 'about')
            <div class="grid-2 mb-3">
                <div class="card ui-card">
                    <h3>Vision</h3>
                    <p>{{ $pageData['vision'] !== '' ? $pageData['vision'] : '-' }}</p>
                </div>
                <div class="card ui-card">
                    <h3>Mission</h3>
                    <p>{{ $pageData['mission'] !== '' ? $pageData['mission'] : '-' }}</p>
                </div>
            </div>
            <div class="card mb-3">
                <h3>Company Story / Timeline</h3>
                <div class="grid-2">
                    @foreach ($pageData['timeline'] ?? [] as $row)
                        <article class="card ui-card">
                            <h3>{{ $row['year'] ?? '-' }} · {{ $row['title'] ?? '-' }}</h3>
                            <p>{{ $row['description'] ?? '-' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="grid-2">
                <div class="card ui-card">
                    <h3>Awards & Recognition</h3>
                    @foreach ($pageData['awards'] ?? [] as $award)
                        <p>{{ $award['year'] ?? '-' }} · {{ $award['name'] ?? '-' }}</p>
                    @endforeach
                </div>
                <div class="card ui-card">
                    <h3>Client List / Logos</h3>
                    <div class="chips">
                        @foreach ($pageData['clients'] ?? [] as $client)
                            <span class="chip ui-chip">{{ is_array($client) ? ($client['name'] ?? '-') : $client }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($pageKey === 'services')
            <div class="grid-2">
                @foreach ($pageData['categories'] ?? [] as $service)
                    <article class="card ui-card">
                        <h3>{{ $service['icon'] ?? '✨' }} {{ $service['name'] ?? '-' }}</h3>
                        <p>{{ $service['description'] ?? '-' }}</p>
                        <p style="margin-top:.5rem;"><strong>Pricing:</strong> {{ $service['pricing_range'] ?? '-' }}</p>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($pageKey === 'faq')
            <div class="faq-filter" id="faqFilter">
                <button type="button" class="active" data-category="all">All</button>
                @foreach ($pageData['categories'] ?? [] as $cat)
                    <button type="button" data-category="{{ \Illuminate\Support\Str::slug((string) $cat) }}">{{ $cat }}</button>
                @endforeach
            </div>
            <div id="faqList">
                @foreach ($pageData['items'] ?? [] as $item)
                    @php $catSlug = \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'general')); @endphp
                    <div class="faq-item" data-category="{{ $catSlug }}">
                        <button type="button">{{ $item['question'] ?? '-' }}</button>
                        <div class="faq-answer">{{ $item['answer'] ?? '-' }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($pageKey === 'privacy-policy' || $pageKey === 'terms')
            <div class="legal-body">
                {!! $pageData['html'] !== '' ? $pageData['html'] : '<p>Konten belum diisi.</p>' !!}
            </div>
            <table class="version-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pageData['version_history'] ?? [] as $ver)
                        <tr>
                            <td>{{ $ver['version'] ?? '-' }}</td>
                            <td>{{ $ver['date'] ?? '-' }}</td>
                            <td>{{ $ver['notes'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($pageKey === 'custom-page')
            <div class="layout-shell {{ ($pageData['layout'] ?? 'full-width') === 'sidebar' ? 'sidebar' : 'full-width' }}">
                <div class="layout-main">
                    {!! $pageData['content_html'] !== '' ? $pageData['content_html'] : '<p>Konten custom page belum diisi.</p>' !!}
                    @foreach ($pageData['sections'] ?? [] as $section)
                        @if (is_array($section))
                            <div class="section-block">
                                <h4>{{ $section['title'] ?? 'Section' }}</h4>
                                @if (!empty($section['content_html']))
                                    {!! $section['content_html'] !!}
                                @elseif (!empty($section['content']))
                                    <p>{{ $section['content'] }}</p>
                                @else
                                    <p>-</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
                @if (($pageData['layout'] ?? 'full-width') === 'sidebar')
                    <aside class="layout-sidebar">
                        {!! $pageData['sidebar_html'] !== '' ? $pageData['sidebar_html'] : '<p>Sidebar belum diisi.</p>' !!}
                    </aside>
                @endif
            </div>
        @endif
    </div>
</main>
@include('partials.public-header-script')
@if ($pageKey === 'faq')
    <script>
        (() => {
            const filterRoot = document.getElementById('faqFilter');
            const faqItems = Array.from(document.querySelectorAll('#faqList .faq-item'));
            filterRoot?.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLButtonElement)) {
                    return;
                }
                const category = target.dataset.category || 'all';
                filterRoot.querySelectorAll('button').forEach((btn) => btn.classList.toggle('active', btn === target));
                faqItems.forEach((item) => {
                    const itemCategory = item.getAttribute('data-category') || '';
                    item.style.display = category === 'all' || category === itemCategory ? '' : 'none';
                });
            });
            faqItems.forEach((item) => {
                const button = item.querySelector('button');
                button?.addEventListener('click', () => {
                    item.classList.toggle('open');
                });
            });
        })();
    </script>
@endif
</body>
</html>
