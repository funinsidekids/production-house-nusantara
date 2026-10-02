<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Hasil Instalasi — AI Personal Learning OS</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:radial-gradient(1200px 600px at 80% -10%,#1b2f4d 0%,#0b1526 55%);color:#e8edf5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#122036;border:1px solid #22344f;border-radius:16px;padding:36px;max-width:720px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.45)}
.icon{font-size:52px;text-align:center}
h1{font-size:24px;text-align:center;margin:8px 0 4px}
.sub{text-align:center;color:#8fa0b8;font-size:14px;margin-bottom:22px}
.step{display:flex;gap:12px;padding:10px 0;border-bottom:1px dashed #22344f;font-size:13.5px}
.step:last-child{border-bottom:none}
.dot{width:22px;height:22px;border-radius:50%;flex:none;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;margin-top:2px}
.dot.ok{background:rgba(62,207,142,.15);color:#3ecf8e}
.dot.bad{background:rgba(255,107,107,.15);color:#ff6b6b}
.detail{color:#8fa0b8;font-size:12px;margin-top:3px;white-space:pre-wrap;word-break:break-word;max-height:120px;overflow:auto}
.errbox{background:rgba(255,107,107,.12);border:1px solid rgba(255,107,107,.4);color:#ffb3b3;padding:14px 16px;border-radius:10px;font-size:13px;margin:16px 0;line-height:1.6}
a.btn{display:block;margin-top:24px;padding:14px;text-align:center;background:linear-gradient(135deg,#d4a942,#f0c75e);color:#1a1206;font-weight:800;border-radius:10px;text-decoration:none;font-size:16px}
a.btn:hover{filter:brightness(1.08)}
.note{margin-top:14px;font-size:12px;color:#8fa0b8;text-align:center;line-height:1.6}
code{background:#0b1526;padding:2px 6px;border-radius:6px;font-size:12px;color:#f0c75e}
</style>
</head>
<body>
<div class="card">
  <div class="icon">{{ $success ? '✅' : '⚠️' }}</div>
  <h1 style="color:{{ $success ? '#3ecf8e' : '#ff6b6b' }}">{{ $success ? 'Instalasi Berhasil!' : 'Instalasi Gagal' }}</h1>
  <p class="sub">{{ $success ? 'Aplikasi siap digunakan. Halaman /install telah dikunci.' : 'Perbaiki pesan error berikut lalu jalankan ulang installer.' }}</p>

  @foreach($steps as $s)
    <div class="step">
      <div class="dot {{ $s['ok'] ? 'ok' : 'bad' }}">{{ $s['ok'] ? '✓' : '✗' }}</div>
      <div style="flex:1">
        <b>{{ $s['name'] }}</b> <span style="color:#5c6f8a">({{ $s['ms'] }} ms)</span>
        <div class="detail">{{ \Illuminate\Support\Str::limit($s['detail'], 400) }}</div>
      </div>
    </div>
  @endforeach

  @if($error)
    <div class="errbox"><b>Error:</b> {{ $error }}</div>
  @endif

  @if($success)
    <a class="btn" href="{{ $appUrl }}">🎓 Buka Aplikasi Sekarang</a>
    <p class="note">Login menggunakan akun Superadmin yang Anda buat pada installer.<br>
    Pastikan izin folder <code>storage/</code> dan <code>bootstrap/cache/</code> adalah 755/775 di File Manager hosting.</p>
  @else
    <a class="btn" href="{{ $backUrl }}">↩ Ulangi Instalasi</a>
  @endif
</div>
</body>
</html>
