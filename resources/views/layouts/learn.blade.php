<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'AI Personal Learning OS' }} — Belajar Kelas XII</title>
    <style>
        :root{--navy:#0b1f3a;--navy2:#12294a;--gold:#d4a017;--gold2:#f0c040;--ink:#1e293b;--muted:#64748b;--bg:#f1f5f9;--ok:#16a34a;--bad:#dc2626;--warn:#ea580c}
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',system-ui,-apple-system,sans-serif}
        body{background:var(--bg);color:var(--ink);min-height:100vh;display:flex;flex-direction:column}
        a{color:inherit;text-decoration:none}
        .topbar{background:var(--navy);color:#fff;display:flex;align-items:center;gap:16px;padding:0 20px;height:60px;position:sticky;top:0;z-index:50;box-shadow:0 2px 10px rgba(0,0,0,.25)}
        .brand{font-weight:800;color:var(--gold2);letter-spacing:.5px;font-size:17px;white-space:nowrap}
        .brand span{color:#fff;font-weight:400;font-size:12px;display:block;letter-spacing:1px}
        .navlinks{display:flex;gap:4px;flex-wrap:wrap;flex:1}
        .navlinks a{padding:8px 13px;border-radius:8px;font-size:13.5px;color:#cbd5e1;transition:.15s}
        .navlinks a:hover{background:rgba(255,255,255,.08);color:#fff}
        .navlinks a.active{background:var(--gold);color:var(--navy);font-weight:700}
        .who{display:flex;align-items:center;gap:10px;margin-left:auto}
        .who .badge{background:var(--navy2);border:1px solid rgba(212,160,23,.4);padding:4px 10px;border-radius:20px;font-size:11px;color:var(--gold2);text-transform:uppercase;letter-spacing:1px}
        form.inline{display:inline}
        .btn{display:inline-block;background:var(--gold);color:var(--navy);font-weight:700;border:none;border-radius:8px;padding:9px 16px;font-size:13.5px;cursor:pointer;transition:.15s}
        .btn:hover{background:var(--gold2)}
        .btn.dark{background:var(--navy);color:#fff}.btn.dark:hover{background:var(--navy2)}
        .btn.sm{padding:5px 10px;font-size:12px;border-radius:6px}
        .btn.ok{background:var(--ok);color:#fff}.btn.bad{background:var(--bad);color:#fff}
        .btn.ghost{background:transparent;border:1.5px solid var(--navy);color:var(--navy)}
        .container{max-width:1200px;margin:0 auto;padding:24px 20px;width:100%;flex:1}
        h1.page{font-size:22px;color:var(--navy);margin-bottom:4px}
        p.sub{color:var(--muted);font-size:13.5px;margin-bottom:20px}
        .flash{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:14px}
        .flash.ok{background:#dcfce7;color:#166534;border:1px solid #86efac}
        .flash.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}
        .grid{display:grid;gap:16px}
        .grid.c4{grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
        .grid.c3{grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}
        .grid.c2{grid-template-columns:repeat(auto-fit,minmax(340px,1fr))}
        .card{background:#fff;border-radius:14px;padding:18px;box-shadow:0 1px 4px rgba(15,23,42,.08)}
        .stat .num{font-size:30px;font-weight:800;color:var(--navy)}
        .stat .lbl{color:var(--muted);font-size:12.5px;text-transform:uppercase;letter-spacing:1px;margin-top:2px}
        table{width:100%;border-collapse:collapse;font-size:13.5px}
        th{text-align:left;color:var(--muted);font-size:11.5px;text-transform:uppercase;letter-spacing:.8px;padding:10px 12px;border-bottom:2px solid #e2e8f0}
        td{padding:11px 12px;border-bottom:1px solid #eef2f7;vertical-align:top}
        tr:hover td{background:#f8fafc}
        .pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
        .pill.green{background:#dcfce7;color:#166534}.pill.red{background:#fee2e2;color:#991b1b}
        .pill.blue{background:#dbeafe;color:#1e40af}.pill.gray{background:#e2e8f0;color:#475569}
        .pill.gold{background:#fef3c7;color:#92400e}.pill.orange{background:#ffedd5;color:#9a3412}
        input,select,textarea{width:100%;padding:10px 12px;border:1.5px solid #cbd5e1;border-radius:9px;font-size:14px;background:#fff}
        input:focus,select:focus,textarea:focus{outline:none;border-color:var(--gold)}
        label{display:block;font-size:12.5px;font-weight:600;color:#475569;margin:12px 0 5px}
        .err-text{color:var(--bad);font-size:12px;margin-top:4px}
        .progress{height:8px;background:#e2e8f0;border-radius:8px;overflow:hidden}
        .progress i{display:block;height:100%;background:linear-gradient(90deg,var(--gold),var(--ok))}
        footer{background:var(--navy);color:#94a3b8;text-align:center;padding:16px;font-size:12.5px}
        @media(max-width:760px){.navlinks{display:none}.topbar{height:auto;flex-wrap:wrap;padding:10px 14px}}
    </style>
    @stack('head')
</head>
<body>
@auth
    <div class="topbar">
        <div class="brand">🎓 BELAJAR<span>AI Personal Learning OS</span></div>
        <nav class="navlinks">
            @php $role = auth()->user()->normalizedRole(); $path = request()->path(); @endphp
            @if($role === 'superadmin')
                <a href="{{ route('superadmin.dashboard') }}" class="{{ $path==='superadmin'?'active':'' }}">Dashboard</a>
                <a href="{{ route('superadmin.users.index') }}" class="{{ str_contains($path,'users')?'active':'' }}">Kelola Akun</a>
                <a href="{{ route('superadmin.users.create') }}" class="">+ Buat Akun</a>
            @elseif($role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="{{ $path==='admin'?'active':'' }}">Dashboard</a>
                <a href="{{ route('admin.subjects') }}" class="{{ str_contains($path,'mapel')?'active':'' }}">Mapel</a>
                <a href="{{ route('admin.materials') }}" class="{{ str_contains($path,'materi')?'active':'' }}">Materi</a>
                <a href="{{ route('admin.questions') }}" class="{{ str_contains($path,'soal')&&!str_contains($path,'ai')?'active':'' }}">Bank Soal</a>
                <a href="{{ route('admin.tryouts') }}" class="{{ str_contains($path,'tryout')?'active':'' }}">Tryout/TKA</a>
                <a href="{{ route('admin.ai') }}" class="{{ str_contains($path,'ai-soal')?'active':'' }}">Moderasi AI</a>
                <a href="{{ route('admin.students') }}" class="{{ str_contains($path,'siswa')?'active':'' }}">Siswa</a>
            @else
                <a href="{{ route('user.dashboard') }}" class="{{ $path==='user'?'active':'' }}">Dashboard</a>
                <a href="{{ route('user.subjects') }}" class="{{ str_contains($path,'mapel')?'active':'' }}">Materi Belajar</a>
                <a href="{{ route('user.tryouts') }}" class="{{ str_contains($path,'tryout')?'active':'' }}">Tryout & TKA</a>
                <a href="{{ route('user.bookmarks') }}" class="{{ str_contains($path,'bookmark')?'active':'' }}">Bookmark</a>
                <a href="{{ route('user.notes') }}" class="{{ str_contains($path,'catatan')?'active':'' }}">Catatan</a>
                <a href="{{ route('user.profile') }}" class="{{ str_contains($path,'profil')?'active':'' }}">Profil</a>
            @endif
        </nav>
        <div class="who">
            <span class="badge">{{ $role }}</span>
            <span style="font-size:13px">{{ Str::limit(auth()->user()->name, 18) }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button class="btn sm ghost" style="border-color:rgba(255,255,255,.35);color:#fff">Keluar</button>
            </form>
        </div>
    </div>
@endauth

<div class="container">
    @if(session('status'))<div class="flash ok">✅ {{ session('status') }}</div>@endif
    @if($errors->any())<div class="flash err">⚠️ {{ $errors->first() }}</div>@endif
    @yield('content')
</div>

<footer>© {{ date('Y') }} AI Personal Learning OS — Modul Belajar Kelas XII SMA/MA · belajar.kasirmo.com</footer>
@stack('scripts')
</body>
</html>
