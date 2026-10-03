<?php
/**
 * BELAJAR — Installer Self-Healing (diagnosa Error 500 di shared hosting)
 *
 * Akses: https://belajar.kasirmo.com/install.php
 *
 * File ini TIDAK butuh Laravel bootstrap. Jika halaman ini bisa terbuka,
 * berarti server PHP Anda hidup dan masalah ada di konfigurasi Laravel/DB.
 * Setelah instalasi selesai, HAPUS file ini dari server.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__); // folder project (file ini: project/public/install.php)
header('Content-Type: text/html; charset=UTF-8');

function row(string $label, string $value, bool $ok): string
{
    $cls = $ok ? '#16a34a' : '#dc2626';
    $icon = $ok ? '✅' : '❌';
    return '<tr><td>' . htmlspecialchars($label) . '</td>'
        . '<td style="font-family:monospace">' . htmlspecialchars($value) . '</td>'
        . '<td style="color:' . $cls . '">' . $icon . '</td></tr>';
}

$results = [];

// 1. Versi PHP
$phpv = PHP_VERSION;
$results[] = row('Versi PHP', $phpv, version_compare($phpv, '8.2', '>='));

// 2. Struktur project
foreach (['app', 'bootstrap', 'config', 'routes', 'storage', 'vendor', 'public'] as $d) {
    $results[] = row("Folder $d/", $root . '/' . $d, is_dir($root . '/' . $d));
}
$results[] = row('File autoload', $root . '/vendor/autoload.php', is_file($root . '/vendor/autoload.php'));

// 3. Writeable
foreach (['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache', '.env'] as $w) {
    $p = $root . '/' . $w;
    $results[] = row("Writeable $w", $p, is_writable($p));
}

// 4. Ekstensi PHP wajib Laravel
foreach (['pdo', 'mbstring', 'openssl', 'json', 'fileinfo', 'curl'] as $ext) {
    $results[] = row("Ekstensi $ext", extension_loaded($ext) ? 'loaded' : 'MISSING', extension_loaded($ext));
}

// 5. Laravel boot test
$bootError = null;
try {
    require $root . '/vendor/autoload.php';
    $app = require_once $root . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $bootOk = true;
} catch (\Throwable $e) {
    $bootOk = false;
    $bootError = get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
}
$results[] = row('Laravel boot', $bootOk ? 'OK' : ($bootError ?? 'FAIL'), $bootOk);

// 6. Koneksi database sesuai .env yang sekarang
$dbOk = false;
$dbMsg = '-';
if (is_file($root . '/.env')) {
    $env = [];
    foreach (file($root . '/.env') as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \"'");
    }
    $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
    $dbPort = (int)($env['DB_PORT'] ?? 3306);
    $dbName = $env['DB_DATABASE'] ?? '';
    $dbUser = $env['DB_USERNAME'] ?? '';
    $dbPass = $env['DB_PASSWORD'] ?? '';
    try {
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_TIMEOUT => 5]);
        $t = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $dbOk = true;
        $dbMsg = count($t) . ' tabel ditemukan di `' . $dbName . '`';
    } catch (\Throwable $e) {
        $dbMsg = $e->getMessage();
    }
    $results[] = row('Koneksi MySQL (.env)', "$dbUser@****:$dbPort/$dbName → $dbMsg", $dbOk);
} else {
    $results[] = row('File .env', 'belum ada — normal SEBELUM instalasi', false);
}

$allOk = $bootOk && $dbOk;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnosa Instalasi — BELAJAR</title>
<style>
 body{font-family:system-ui,sans-serif;background:#0b1220;color:#e2e8f0;margin:0;padding:24px}
 .box{max-width:900px;margin:auto;background:#111a2e;border:1px solid #24304d;border-radius:12px;padding:24px}
 h1{font-size:20px;margin-top:0} table{width:100%;border-collapse:collapse;font-size:13px}
 td{padding:6px 8px;border-bottom:1px solid #1d2840} a.btn{display:inline-block;margin-top:16px;background:#d4a017;color:#0b1220;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:700}
 .err{background:#2a1114;border:1px solid #7f1d1d;padding:12px;border-radius:8px;font-family:monospace;font-size:12px;margin-top:12px;word-break:break-all}
 .hint{background:#152238;border:1px solid #24304d;padding:12px;border-radius:8px;font-size:13px;margin-top:16px}
</style>
</head>
<body>
<div class="box">
 <h1>🔧 Diagnosa Instalasi — <?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '') ?></h1>
 <table><?= implode('', $results) ?></table>

 <?php if ($bootError): ?>
   <div class="err"><b>Error boot Laravel:</b><br><?= htmlspecialchars($bootError) ?></div>
 <?php endif; ?>

 <?php if ($allOk): ?>
   <div class="hint">🎉 Semua komponen sehat. Silakan lanjut ke installer resmi:<br>
     <a class="btn" href="/install">Buka Halaman Instalasi →</a>
     atau jika sudah terinstal: <a class="btn" href="/login">Login →</a>
   </div>
 <?php elseif (!$bootOk): ?>
   <div class="hint"><b>Penyebab Error 500 ada di atas (baris merah).</b> Penyebab paling umum di Hostinger:
     <ol>
       <li><b>Versi PHP terlalu lama</b> → ubah ke PHP 8.2/8.3/8.4 di <i>Hosting → PHP Configuration / PHP Selector</i>.</li>
       <li><b>Folder vendor/ tidak ikut ter-upload</b> (sebagian file manager menyembunyikan/memblokir file berjumlah banyak). Solusi: upload ulang via FTP/FileZilla, atau jalankan di Terminal hosting: <code>cd ~/domains/belajar.kasirmo.com/public_html/belajar && composer install --no-dev</code></li>
       <li><b>Struktur path salah</b> → pastikan file-file ini berada di <code>.../public_html/belajar/app, .../belajar/vendor</code>, dst.</li>
     </ol>
   </div>
 <?php else: ?>
   <div class="hint">PHP &amp; Laravel OK — masalah ada di koneksi database. Pastikan kredensial MySQL di cPanel benar, atau langsung buka <a href="/install">/install</a> untuk mengisi form instalasi.</div>
 <?php endif; ?>

 <p style="font-size:11px;color:#64748b;margin-top:16px">⚠️ Demi keamanan, hapus file <code>public/install.php</code> ini setelah instalasi berhasil.</p>
</div>
</body>
</html>
