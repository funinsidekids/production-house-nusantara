<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AI Personal Learning OS | Modul Belajar Kelas XII SMA/MA</title>
    <style>
        :root{--navy:#0b1f3a;--navy2:#12294a;--gold:#d4a017;--gold2:#f0c040;--ink:#e2e8f0}
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',system-ui,sans-serif}
        body{min-height:100vh;background:radial-gradient(1200px 600px at 80% -10%,#1a3a63 0%,transparent 60%),linear-gradient(160deg,var(--navy) 0%,#061224 100%);color:var(--ink);display:flex;flex-direction:column}
        .wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 18px;gap:48px;flex-wrap:wrap}
        .hero{max-width:520px}
        .logo{font-size:15px;letter-spacing:3px;color:var(--gold2);font-weight:800;margin-bottom:14px}
        h1{font-size:clamp(28px,4.5vw,44px);line-height:1.15;color:#fff}
        h1 em{color:var(--gold2);font-style:normal}
        .hero p{margin-top:14px;color:#9fb3cc;font-size:15px;line-height:1.65}
        .stats{display:flex;gap:14px;margin-top:26px;flex-wrap:wrap}
        .stat{background:rgba(255,255,255,.05);border:1px solid rgba(212,160,23,.25);border-radius:14px;padding:14px 20px;min-width:110px;text-align:center}
        .stat b{display:block;font-size:24px;color:var(--gold2)}
        .stat span{font-size:11.5px;letter-spacing:1px;text-transform:uppercase;color:#8fa6c0}
        .panel{width:390px;background:#fff;border-radius:20px;padding:30px 28px;color:#1e293b;box-shadow:0 24px 60px rgba(0,0,0,.45)}
        .tabs{display:flex;background:#f1f5f9;border-radius:11px;padding:4px;margin-bottom:6px}
        .tabs button{flex:1;border:none;background:transparent;padding:9px;border-radius:8px;font-size:13px;font-weight:700;color:#64748b;cursor:pointer}
        .tabs button.on{background:var(--navy);color:#fff}
        h2{font-size:19px;color:var(--navy);margin:14px 0 2px}
        .sub{font-size:13px;color:#64748b;margin-bottom:14px}
        label{display:block;font-size:12.5px;font-weight:600;color:#475569;margin:12px 0 5px}
        input{width:100%;padding:11px 13px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14.5px}
        input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(212,160,23,.15)}
        .btn{width:100%;margin-top:18px;background:var(--gold);color:var(--navy);font-weight:800;font-size:15px;border:none;border-radius:11px;padding:13px;cursor:pointer;transition:.15s}
        .btn:hover{background:var(--gold2)}
        .flash{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:12px}
        .flash.err{background:#fee2e2;color:#991b1b}.flash.ok{background:#dcfce7;color:#166534}
        .alt{margin-top:16px;font-size:13px;text-align:center;color:#64748b}
        .alt a{color:var(--navy);font-weight:700}
        .roles{display:flex;gap:8px;margin-top:16px;justify-content:center;flex-wrap:wrap}
        .rolechip{font-size:11px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:20px;padding:5px 12px;color:#475569}
        footer{text-align:center;padding:16px;font-size:12px;color:#5b7392}
        @media(max-width:900px){.wrap{gap:28px}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="logo">🎓 AI PERSONAL LEARNING OS</div>
        <h1>Modul Belajar, Latihan Soal, Tryout &amp; <em>TKA</em> Kelas XII SMA/MA</h1>
        <p>Platform belajar terpadu dengan bank soal per bab, tryout otomatis, analisis progres, remedial berbasis kelemahan topik, dan AI Guru (Gemini) sebagai pendamping belajar pribadi Anda.</p>
        <div class="stats">
            <div class="stat"><b>{{ $stats['subjects'] }}</b><span>Mata Pelajaran</span></div>
            <div class="stat"><b>{{ $stats['materials'] }}</b><span>Materi</span></div>
            <div class="stat"><b>{{ $stats['questions'] }}</b><span>Soal</span></div>
            <div class="stat"><b>{{ $stats['tryouts'] }}</b><span>Tryout/TKA</span></div>
        </div>
        <div class="roles">
            <span class="rolechip">👨‍🎓 Siswa — belajar &amp; tryout</span>
            <span class="rolechip">🛠️ Admin — kelola konten</span>
            <span class="rolechip">⭐ Superadmin — kelola akun</span>
        </div>
    </div>

    <div class="panel">
        <div class="tabs">
            <button type="button" class="on" onclick="location.href='{{ route('home') }}'">Masuk</button>
            <button type="button" onclick="location.href='{{ route('register') }}'">Daftar Akun</button>
        </div>

        @if(session('status'))<div class="flash ok">{{ session('status') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="flash err">⚠️ {{ $errors->first() }}</div>@endif

        <h2>Selamat datang kembali</h2>
        <div class="sub">Masuk dengan email atau username Anda.</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="identity">Email / Username</label>
            <input id="identity" name="identity" value="{{ old('identity') }}" required autofocus autocomplete="username" placeholder="nama@email.com">

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">

            <label style="display:flex;align-items:center;gap:8px;font-weight:400;margin-top:14px">
                <input type="checkbox" name="remember" value="1" style="width:auto"> Ingat saya
            </label>

            <button class="btn" type="submit">MASUK →</button>
        </form>

        <div class="alt">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a> (perlu persetujuan Superadmin)</div>
    </div>
</div>
<footer>© {{ date('Y') }} AI Personal Learning OS · belajar.kasirmo.com</footer>
</body>
</html>
