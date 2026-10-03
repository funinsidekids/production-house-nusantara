<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Koneksi Database Gagal — AI Personal Learning OS</title>
<style>
  :root{--navy:#0b1a2b;--card:#12263d;--gold:#d4af37;--txt:#e8eef5;--mut:#8fa3b8;--bad:#ff5d5d}
  *{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
  body{background:linear-gradient(160deg,var(--navy),#0f2138 60%,#0b1a2b);color:var(--txt);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 16px}
  .wrap{max-width:720px;width:100%}
  h1{font-size:24px;color:var(--gold);margin-bottom:8px}
  .sub{color:var(--mut);font-size:14px;margin-bottom:22px;line-height:1.7}
  .card{background:var(--card);border:1px solid #1d3a5a;border-radius:14px;padding:24px;margin-bottom:16px}
  .badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:#3a1520;color:var(--bad);margin-bottom:14px}
  ol{margin:12px 0 0 20px;font-size:14px;line-height:2}
  code{background:#08131f;padding:2px 7px;border-radius:5px;font-size:13px;color:#ffd97a}
  a.btn{display:inline-block;margin-top:16px;padding:12px 24px;border-radius:9px;background:var(--gold);color:#132;font-weight:700;text-decoration:none}
  a.btn:hover{filter:brightness(1.1)}
  .small{font-size:12px;color:var(--mut);line-height:1.8;margin-top:14px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <span class="badge">KONEKSI DATABASE GAGAL</span>
    <h1>⚠️ Aplikasi belum dapat terhubung ke MySQL</h1>
    <p class="sub">
      Konfigurasi database pada file <code>.env</code> tidak valid atau hilang —
      Laravel jatuh ke kredensial bawaan (<code>root@127.0.0.1</code> tanpa password)
      yang pasti ditolak oleh hosting. Ini bukan kerusakan data; cukup perbaiki konfigurasi.
    </p>
    <ol>
      <li>Buka installer: <a style="color:var(--gold)" href="/install/">/install/</a> — kolom host/user/database akan terisi otomatis dari <code>.env</code> lama bila ada.</li>
      <li>Ambil kredensial asli di <b>hPanel → Hosting → Advanced → MySQL Details</b>:
          nama user (contoh <code>u1234567_belajar</code>) dan password-nya.</li>
      <li>Pastikan <b>Nama Database</b> persis seperti di panel (contoh <code>u1234567_belajar</code>, bukan <code>belajar</code>).</li>
      <li><b>MySQL Host</b> Hostinger: isi <code>localhost</code>.</li>
      <li>Klik <b>Jalankan Instalasi</b> — installer akan menulis ulang <code>.env</code> dan mengecek koneksi.</li>
    </ol>
    <a class="btn" href="/install/">🔧 Buka Installer Perbaikan</a>
    <p class="small">
      Catatan: jika Anda baru memindahkan/merename folder project, hapus juga cache lama lewat
      Terminal hosting: <code>php artisan optimize:clear</code>.<br>
      Halaman ini muncul dengan status HTTP 503 dan tidak menampilkan detail sensitif server.
    </p>
  </div>
</div>
</body>
</html>
