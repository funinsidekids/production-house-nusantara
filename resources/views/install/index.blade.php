<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Instalasi — AI Personal Learning OS</title>
<style>
:root{--navy:#0b1526;--navy2:#122036;--gold:#d4a942;--gold2:#f0c75e;--text:#e8edf5;--muted:#8fa0b8;--ok:#3ecf8e;--bad:#ff6b6b;--line:#22344f}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:radial-gradient(1200px 600px at 80% -10%,#1b2f4d 0%,var(--navy) 55%);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.wrap{width:100%;max-width:960px}
.card{background:var(--navy2);border:1px solid var(--line);border-radius:16px;padding:32px;box-shadow:0 20px 60px rgba(0,0,0,.45)}
h1{font-size:26px;color:var(--gold2);margin-bottom:4px}
.sub{color:var(--muted);font-size:14px;margin-bottom:24px}
.badge{display:inline-block;background:rgba(212,169,66,.15);color:var(--gold2);border:1px solid rgba(212,169,66,.4);padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;margin-bottom:16px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:24px}
@media(max-width:760px){.grid2{grid-template-columns:1fr}}
.section{background:rgba(11,21,38,.6);border:1px solid var(--line);border-radius:12px;padding:20px;margin-bottom:20px}
.section h2{font-size:15px;color:var(--gold);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px}
.req{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px dashed var(--line);font-size:13px}
.req:last-child{border-bottom:none}
.pill{padding:2px 10px;border-radius:999px;font-size:11px;font-weight:700}
.pill.ok{background:rgba(62,207,142,.15);color:var(--ok)}
.pill.bad{background:rgba(255,107,107,.15);color:var(--bad)}
label{display:block;font-size:13px;color:var(--muted);margin:12px 0 5px}
input{width:100%;padding:11px 13px;background:var(--navy);border:1px solid var(--line);border-radius:9px;color:var(--text);font-size:14px}
input:focus{outline:none;border-color:var(--gold)}
.hint{font-size:11.5px;color:var(--muted);margin-top:4px}
.err{background:rgba(255,107,107,.12);border:1px solid rgba(255,107,107,.4);color:#ffb3b3;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:18px;line-height:1.5}
button{margin-top:24px;width:100%;padding:14px;background:linear-gradient(135deg,var(--gold),var(--gold2));color:#1a1206;font-size:16px;font-weight:800;border:none;border-radius:10px;cursor:pointer;letter-spacing:.02em}
button:hover{filter:brightness(1.08)}
.steps ol{margin-left:18px;font-size:13.5px;color:var(--muted);line-height:1.9}
.footer{text-align:center;color:var(--muted);font-size:12px;margin-top:18px}
code{background:var(--navy);padding:2px 6px;border-radius:6px;font-size:12px;color:var(--gold2)}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <span class="badge">MODE INSTALASI</span>
    <h1>AI Personal Learning OS</h1>
    <p class="sub">Modul Belajar, Latihan Soal, Tryout &amp; TKA — Kelas XII SMA/MA. Lengkapi konfigurasi server di bawah ini, lalu klik <b>Mulai Instalasi</b>.</p>

    @if($errors->any())
      <div class="err"><b>Instalasi belum berhasil:</b><br>{{ $errors->first() }}</div>
    @endif

    <div class="grid2">
      <div>
        <div class="section">
          <h2>① Pengecekan Server</h2>
          @foreach($requirements as $r)
            <div class="req">
              <span>{{ $r['label'] }} <span style="color:#5c6f8a">({{ $r['value'] }})</span></span>
              <span class="pill {{ $r['ok'] ? 'ok' : 'bad' }}">{{ $r['ok'] ? '✓ OK' : '✗ GAGAL' }}</span>
            </div>
          @endforeach
        </div>
        <div class="section steps">
          <h2>Panduan Singkat</h2>
          <ol>
            <li>Buat database MySQL kosong di panel hosting / phpMyAdmin.</li>
            <li>Import file <code>database/mysql_schema.sql</code> bila tersedia (opsional — installer juga dapat membuat tabel otomatis).</li>
            <li>Upload seluruh file aplikasi ke hosting.</li>
            <li>Buka <code>https://domainanda.com/install</code>, isi form ini.</li>
            <li>Setelah sukses, halaman instalasi otomatis terkunci.</li>
          </ol>
        </div>
      </div>
      <div>
        <form method="POST" action="{{ $action }}">
          @csrf
          <div class="section">
            <h2>② Aplikasi</h2>
            <label>Nama Aplikasi</label>
            <input name="app_name" value="{{ old('app_name', 'AI Personal Learning OS') }}" required>
            <label>URL Aplikasi (domain lengkap)</label>
            <input name="app_url" type="url" value="{{ old('app_url', $old['app_url']) }}" placeholder="https://www.myapp.com" required>
            <p class="hint">Contoh: https://www.myapp.com atau https://myapp.com/subfolder</p>
          </div>

          <div class="section">
            <h2>③ Database MySQL</h2>
            <label>MySQL Server (Host)</label>
            <input name="db_host" value="{{ old('db_host', $old['db_host']) }}" placeholder="localhost / mysql.namahosting.com" required>
            <label>Port MySQL</label>
            <input name="db_port" type="number" value="{{ old('db_port', $old['db_port']) }}" required>
            <label>Nama Database</label>
            <input name="db_database" value="{{ old('db_database') }}" placeholder="namauser_learningdb" required>
            <label>Username MySQL</label>
            <input name="db_username" value="{{ old('db_username') }}" placeholder="namauser_dbuser" required>
            <label>Password MySQL</label>
            <input name="db_password" type="password" placeholder="(kosongkan jika tidak ada)">
          </div>

          <div class="section">
            <h2>④ Akun Superadmin</h2>
            <p class="hint" style="margin-bottom:6px">Akun utama pengelola aplikasi. Kredensial ini <b>tidak ditampilkan</b> di halaman manapun setelah instalasi.</p>
            <label>Nama Lengkap</label>
            <input name="admin_name" value="{{ old('admin_name') }}" required>
            <label>Email (untuk login)</label>
            <input name="admin_email" type="email" value="{{ old('admin_email') }}" required>
            <label>Username</label>
            <input name="admin_username" value="{{ old('admin_username') }}" required>
            <label>Password (min. 8 karakter)</label>
            <input name="admin_password" type="password" required minlength="8">
            <label>Konfirmasi Password</label>
            <input name="admin_password_confirmation" type="password" required minlength="8">
          </div>

          <div class="section">
            <h2>⑤ Google Gemini (Opsional)</h2>
            <label>Gemini API Key</label>
            <input name="gemini_key" type="password" placeholder="AIza... (dikosongkan boleh)">
            <p class="hint">Disimpan terenkripsi di server sebagai AI Gateway. Android tidak perlu menyimpan API key.</p>
          </div>

          <button type="submit">🚀 Mulai Instalasi</button>
        </form>
      </div>
    </div>
    <p class="footer">Installer hanya bisa dijalankan satu kali. Setelah selesai, akses langsung dinonaktifkan demi keamanan.</p>
  </div>
</div>
</body>
</html>
