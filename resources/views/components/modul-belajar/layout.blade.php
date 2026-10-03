<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Modul Belajar Kelas 12 SMA/MA' }}</title>
    <meta name="description" content="{{ $description ?? 'Platform modul belajar online kelas 12 SMA/MA: Matematika, Fisika, Kimia, Biologi, dan Bahasa Inggris siap menghadapi UTBK/SNBT.' }}">
    <style>
        :root{--primary:#4f46e5;--ink:#1e293b;--muted:#64748b;--bg:#f8fafc;--card:#ffffff;--line:#e2e8f0}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--ink);line-height:1.65}
        a{color:inherit;text-decoration:none}
        .container{max-width:1080px;margin:0 auto;padding:0 20px}
        header.site{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;padding:14px 0;position:sticky;top:0;z-index:50;box-shadow:0 2px 10px rgba(0,0,0,.15)}
        header.site .inner{display:flex;align-items:center;justify-content:space-between;gap:16px}
        .brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1.05rem}
        .brand .logo{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-size:1.2rem}
        nav.top a{color:rgba(255,255,255,.85);margin-left:20px;font-size:.92rem;font-weight:600}
        nav.top a:hover,nav.top a.active{color:#fff}
        .hero{background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 60%,#a855f7 100%);color:#fff;padding:56px 0 64px}
        .hero h1{font-size:clamp(1.6rem,4vw,2.4rem);line-height:1.25;max-width:640px}
        .hero p{margin-top:12px;max-width:560px;color:rgba(255,255,255,.9)}
        .badge-pill{display:inline-block;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);padding:4px 14px;border-radius:999px;font-size:.8rem;font-weight:600;margin-bottom:14px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}
        .section{padding:44px 0}
        .section h2{font-size:1.4rem;margin-bottom:6px}
        .section .sub{color:var(--muted);margin-bottom:24px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;transition:transform .15s ease,box-shadow .15s ease;display:block}
        .card:hover{transform:translateY(-3px);box-shadow:0 12px 24px rgba(15,23,42,.08)}
        .icon-sq{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;font-size:1.3rem;color:#fff;margin-bottom:14px}
        .card h3{font-size:1.05rem;margin-bottom:6px}
        .card p{font-size:.88rem;color:var(--muted)}
        .meta{margin-top:14px;font-size:.8rem;color:var(--muted);display:flex;gap:14px}
        .chapter{background:var(--card);border:1px solid var(--line);border-radius:14px;margin-bottom:16px;overflow:hidden}
        .chapter>header{padding:18px 22px;background:#fff;border-bottom:1px solid var(--line)}
        .chapter>header .num{font-size:.75rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.06em}
        .chapter>header h3{font-size:1.08rem;margin-top:2px}
        .chapter>header p{font-size:.86rem;color:var(--muted);margin-top:4px}
        .material-list{list-style:none}
        .material-list li+li{border-top:1px dashed var(--line)}
        .material-list a{display:flex;align-items:center;gap:12px;padding:13px 22px;font-size:.92rem}
        .material-list a:hover{background:#eef2ff}
        .tag{font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:999px;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
        .tag-teori{background:#e0e7ff;color:#4338ca}.tag-contoh{background:#dcfce7;color:#15803d}
        .tag-latihan{background:#fef3c7;color:#b45309}.tag-rangkuman{background:#fce7f3;color:#be185d}.tag-video{background:#cffafe;color:#0e7490}
        .breadcrumb{font-size:.85rem;color:var(--muted);margin:22px 0 6px}
        .breadcrumb a:hover{color:var(--primary)}
        .article{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:30px;margin-bottom:24px}
        .article h1{font-size:1.5rem;margin:8px 0 4px}
        .article .lead{color:var(--muted);font-size:.9rem;margin-bottom:18px}
        .article .content{font-size:.98rem;white-space:pre-line}
        .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:10px;padding:11px 20px;font-size:.9rem;font-weight:700;cursor:pointer;transition:filter .15s}
        .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{filter:brightness(1.1)}
        .btn-outline{background:#fff;color:var(--primary);border:1.5px solid var(--primary)}
        .nav-row{display:flex;justify-content:space-between;gap:12px;margin-top:8px}
        .nav-row .card{flex:1;padding:16px 20px}
        .nav-row .small{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;font-weight:700}
        footer.site{background:#0f172a;color:#94a3b8;padding:34px 0;margin-top:50px;font-size:.85rem;text-align:center}
        footer.site a{color:#e2e8f0;font-weight:600}
        .toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:#0f172a;color:#fff;padding:12px 22px;border-radius:12px;font-size:.88rem;opacity:0;pointer-events:none;transition:all .25s;z-index:99}
        .toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
        @media(max-width:640px){nav.top a{margin-left:12px;font-size:.82rem}.article{padding:20px}}
    </style>
    @stack('head')
</head>
<body>
<header class="site">
    <div class="container inner">
        <a href="{{ route('modul-belajar.index') }}" class="brand">
            <span class="logo">📚</span>
            <span>Modul<span style="opacity:.75">Kelas12</span></span>
        </a>
        <nav class="top">
            <a href="{{ route('modul-belajar.index') }}">Beranda</a>
            <a href="{{ route('modul-belajar.index') }}#mapel">Mata Pelajaran</a>
            <a href="{{ url('/') }}">Situs Utama</a>
        </nav>
    </div>
</header>

<main>
    {{ $slot }}
</main>

<footer class="site">
    <div class="container">
        &copy; {{ date('Y') }} Modul Belajar Kelas 12 SMA/MA — disusun untuk persiapan ujian &amp; UTBK/SNBT.
        Dibangun dengan <a href="https://laravel.com" target="_blank" rel="noopener">Laravel</a>.
    </div>
</footer>

<div class="toast" id="toast"></div>
@stack('scripts')
</body>
</html>
