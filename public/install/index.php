<?php
/**
 * BELAJAR — AI Personal Learning OS
 * STANDEALONE INSTALLER (tanpa Laravel, tanpa vendor)
 * ============================================================
 * Dibuat karena /install versi Laravel selalu Error 500 di
 * shared hosting Hostinger (public_html/belajar/).
 *
 * File ini TIDAK membutuhkan:
 *   - composer / vendor/
 *   - framework Laravel booting
 *   - .htaccess rewrite apa pun
 * Cukup PHP + ekstensi pdo_mysql.
 *
 * Cara pakai (sesuai alur Anda):
 *   1. Buat database MySQL di hosting (nama bebas, mis. belajar).
 *   2. Upload SEMUA file project ke public_html/belajar/
 *      (folder install/ ini harus ikut ter-upload).
 *   3. Buka  https://belajar.kasirmo.com/install/
 *   4. Isi form (host, nama DB, user, password, URL)
 *      lalu klik "Install".
 *
 * Yang dilakukan installer ini:
 *   a. Menguji koneksi MySQL.
 *   b. Mengimpor Belajar.sql (bisa dari file lokal ATAU
 *      otomatis download bila file belum ter-upload — cari
 *      Belajar.sql di root project / folder database / URL).
 *   c. Menulis .env lengkap (APP_KEY digenerate sendiri).
 *   d. Membuat folder runtime storage/framework/* agar session
 *      & view cache tersedia (mencegah 500 "No supported encrypter").
 *   e. Menandai instalasi selesai (storage/app/installed.json)
 *      dan mengunci installer dengan .htaccess.
 *
 * Setelah selesai: buka https://belajar.kasirmo.com/login
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');
set_time_limit(0);
ini_set('memory_limit', '512M');

$ROOT        = dirname(__DIR__);                 // folder project (public_html/belajar)
$ENV_FILE    = $ROOT . '/.env';
$INSTALLED   = $ROOT . '/storage/app/installed.json';
$LOCKED_FLAG = __DIR__ . '/.installed';
$CACHE_SQL   = __DIR__ . '/Belajar.sql.cache';

/* ---------- bersihkan cache konfigurasi Laravel (config:cache) ----------
 * Jika di server pernah ada bootstrap/cache/config.php, isinya menimpa
 * nilai .env — ini penyebab umum error "Access denied for user 'root'"
 * padahal installer sudah menulis kredensial yang benar. */
foreach ([$ROOT . '/bootstrap/cache/config.php', $ROOT . '/bootstrap/cache/routes.php', $ROOT . '/bootstrap/cache/services.php', $ROOT . '/bootstrap/cache/packages.php'] as $cached) {
    if (is_file($cached)) { @unlink($cached); }
}
if (is_dir($ROOT . '/storage/framework/views')) {
    foreach (glob($ROOT . '/storage/framework/views/*.php') ?: [] as $v) { @unlink($v); }
}

$errors = [];
$log    = [];
$done   = is_file($INSTALLED);

/* ---------- auto-repair: .env lama masih memakai kredensial default ----------
 * Jika installer sebelumnya sudah selesai tetapi .env ternyata berisi
 * root@127.0.0.1 tanpa password (mis. upload menimpa .env hasil instalasi),
 * aplikasi akan error "Access denied for user 'root'". Deteksi & pulihkan
 * kredensial dari storage/app/installed.json secara otomatis. */
if ($done && is_file($ENV_FILE)) {
    $cur = [];
    foreach (file($ENV_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ln) {
        if (preg_match('/^([A-Z0-9_]+)=(.*)$/', $ln, $m)) {
            $cur[$m[1]] = trim($m[2], " \\\"'");
        }
    }
    $broken = (($cur['DB_USERNAME'] ?? '') === 'root' && ($cur['DB_PASSWORD'] ?? '') === '');
    if ($broken) {
        $meta = json_decode((string) @file_get_contents($INSTALLED), true) ?: [];
        // pulihkan host & nama database dari catatan instalasi bila tersedia
        $fixHost = !empty($meta['db_host']) ? $meta['db_host'] : 'localhost';
        $fixDb   = !empty($meta['db_database']) ? $meta['db_database'] : ($cur['DB_DATABASE'] ?? '');
        $lines = file($ENV_FILE, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $i => $ln) {
            if (preg_match('/^DB_HOST=/', $ln))     $lines[$i] = 'DB_HOST=' . $fixHost;
            if (preg_match('/^DB_PORT=/', $ln))     $lines[$i] = 'DB_PORT=' . ($meta['db_port'] ?? '3306');
            if (preg_match('/^DB_DATABASE=/', $ln)) $lines[$i] = 'DB_DATABASE=' . $fixDb;
        }
        @file_put_contents($ENV_FILE, implode("\n", $lines) . "\n");
        $log[] = 'PERINGATAN: .env terdeteksi memakai kredensial DEFAULT (root@127.0.0.1 tanpa password). Penyebab paling umum: file .env dari project lokal IKUT ter-upload sehingga MENIMPA .env hasil instalasi. Host & nama database dipulihkan dari catatan instalasi. Silakan isi ulang user & password MySQL pada form di bawah lalu klik Install lagi (aman — import akan dilewati jika tabel sudah ada).';
        $done = false; // buka kembali form installer
    }
}
$old    = [
    'db_host'     => 'localhost',
    'db_port'     => '3306',
    'db_database' => '',
    'db_username' => '',
    'app_url'     => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'belajar.kasirmo.com'),
];
$oldHasEnv = false;

/* ---------- baca kredensial lama dari .env jika ada ---------- */
if (!$done && is_file($ENV_FILE)) {
    foreach (file($ENV_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ln) {
        if (preg_match('/^([A-Z0-9_]+)=(.*)$/', $ln, $m)) {
            $key = $m[1];
            $val = trim($m[2], " \"'");
            if ($key === 'DB_HOST')     $old['db_host']     = $val;
            if ($key === 'DB_PORT')     $old['db_port']     = $val;
            if ($key === 'DB_DATABASE') $old['db_database'] = $val;
            if ($key === 'DB_USERNAME') $old['db_username'] = $val;
            if ($key === 'DB_PASSWORD') $oldPassword       = $val;
            if ($key === 'APP_URL')     $old['app_url']     = $val;
            if ($key === 'GEMINI_API_KEY' && $val !== '') $oldGemini = $val;
            if ($key === 'APP_KEY' && $val !== '') $oldHasEnv = true;
        }
    }
}
$oldPassword = $oldPassword ?? '';
$oldGemini   = $oldGemini   ?? '';

function fld(string $k): string
{
    return trim((string) ($_POST[$k] ?? ''));
}

/** Cari Belajar.sql di beberapa lokasi umum. */
function findLocalSql(string $root): ?string
{
    foreach ([$root . '/Belajar.sql', $root . '/database/Belajar.sql', $root . '/sql/Belajar.sql', $root . '/database/sql/Belajar.sql'] as $p) {
        if (is_file($p) && filesize($p) > 10000) {
            return $p;
        }
    }
    return null;
}

/** Kumpulkan kandidat URL download Belajar.sql (GitHub repo user, dsb.). */
function sqlUrlCandidates(): array
{
    $urls = [];
    if (!empty($_POST['sql_url'])) {
        $u = trim((string) $_POST['sql_url']);
        if ($u !== '') $urls[] = $u;
    }
    // Repo publik Anda (ubah bila nama repo/branch berbeda):
    $repoBase = 'https://raw.githubusercontent.com/Steveandriebagus/';
    foreach (['belajar/main/Belajar.sql', 'belajar/master/Belajar.sql',
              'belajar/main/database/Belajar.sql',
              'AI-Personal-Learning-OS/main/Belajar.sql'] as $p) {
        $urls[] = $repoBase . $p;
    }
    return array_values(array_unique($urls));
}

function httpGet(string $url): string|false
{
    $ctx = stream_context_create([
        'http' => ['timeout' => 90, 'user_agent' => 'BelajarInstaller/1.0', 'follow_location' => 1],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    return @file_get_contents($url, false, $ctx);
}

/* ============================= PROSES INSTALL ============================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$done) {
    $dbHost  = fld('db_host') ?: 'localhost';
    $dbPort  = fld('db_port') ?: '3306';
    $dbName  = fld('db_database');
    $dbUser  = fld('db_username');
    $dbPass  = (string) ($_POST['db_password'] ?? '');
    if ($dbPass === '' && isset($oldPassword) && $oldPassword !== '') {
        $dbPass = $oldPassword; // pertahankan password lama bila kolom dikosongkan
    }
    $appUrl  = rtrim(fld('app_url')) ?: ('https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $gemKey  = (string) ($_POST['gemini_api_key'] ?? '');
    if ($gemKey === '' && $oldGemini !== '') {
        $gemKey = $oldGemini; // jangan sampai key Gemini hilang saat instalasi ulang
    }
    $adminEmail    = fld('admin_email');
    $adminUsername = fld('admin_username');
    $adminPass     = (string) ($_POST['admin_password'] ?? '');

    /* 1. validasi dasar */
    if ($dbName === '') $errors[] = 'Nama database wajib diisi.';
    if ($dbUser === '') $errors[] = 'Username MySQL wajib diisi.';
    if (!extension_loaded('pdo_mysql')) {
        $errors[] = 'Ekstensi PHP pdo_mysql tidak aktif. Ganti versi PHP (8.2/8.3) di panel hosting atau aktifkan ekstensinya.';
    }
    if (!empty($adminPass) && strlen($adminPass) < 8) {
        $errors[] = 'Password admin baru minimal 8 karakter.';
    }
    if (($adminEmail !== '' || !empty($adminPass)) && ($adminEmail === '' || $adminUsername === '' || $adminPass === '')) {
        $errors[] = 'Untuk mengganti akun Superadmin, isi Email + Username + Password semuanya.';
    }

    /* 2. koneksi MySQL */
    $pdo = null;
    if (!$errors) {
        try {
            $pdo = new PDO(
                "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
            );
            $log[] = "OK  Koneksi MySQL ke {$dbHost}:{$dbPort}/{$dbName} berhasil.";
        } catch (Throwable $e) {
            $errors[] = 'Gagal koneksi MySQL: ' . $e->getMessage()
                . ' — Pastikan database sudah DIBUAT di panel hosting, dan user MySQL sudah DI-ASSIGN ke database tersebut.';
        }
    }

    /* 3. cek apakah database sudah berisi tabel (import sebelumnya) */
    $tableCount = 0;
    if ($pdo) {
        try {
            $tableCount = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
        } catch (Throwable $e) { $tableCount = 0; }
    }

    /* 4. sumber SQL: lokal -> cache -> unduh URL */
    $sqlFile = null;
    if ($pdo && !$errors) {
        if ($tableCount > 0) {
            $log[] = "OK  Database {$dbName} sudah berisi {$tableCount} tabel — import dilewati (data tidak ditimpa).";
        } else {
            $local = findLocalSql($ROOT);
            if ($local) {
                $sqlFile = $local;
                $log[] = 'OK  Menggunakan Belajar.sql dari ' . str_replace($ROOT, '<project>', $local)
                       . ' (' . number_format(filesize($local) / 1024) . ' KB).';
            } elseif (is_file($CACHE_SQL) && filesize($CACHE_SQL) > 10000) {
                $sqlFile = $CACHE_SQL;
                $log[] = 'OK  Menggunakan cache Belajar.sql unduhan sebelumnya.';
            } else {
                foreach (sqlUrlCandidates() as $u) {
                    $data = httpGet($u);
                    if ($data !== false && strlen($data) > 10000 && stripos($data, 'CREATE TABLE') !== false) {
                        file_put_contents($CACHE_SQL, $data);
                        $sqlFile = $CACHE_SQL;
                        $log[] = 'OK  Belajar.sql berhasil diunduh dari ' . $u . ' (' . number_format(strlen($data) / 1024) . ' KB).';
                        break;
                    }
                }
                if (!$sqlFile) {
                    $errors[] = 'Belajar.sql tidak ditemukan di server DAN gagal diunduh. Solusi paling mudah: '
                        . 'upload file Belajar.sql ke root folder project (public_html/belajar/Belajar.sql), lalu ulangi. '
                        . 'Atau tempel URL langsung ke Belajar.sql pada kolom "URL Belajar.sql" di form.';
                }
            }
        }
    }

    /* 5. jalankan import statement-by-statement (aman utk file besar) */
    if ($sqlFile && !$errors) {
        set_time_limit(0);
        $fp = fopen($sqlFile, 'r');
        $stmtBuf = '';
        $applied = 0;
        $failed  = 0;
        $firstErr = '';
        while (($line = fgets($fp)) !== false) {
            $t = trim($line);
            if ($t === '' || str_starts_with($t, '--') || str_starts_with($t, '/*!') || str_starts_with($t, '/*')) continue;
            $stmtBuf .= $line;
            if (str_ends_with(rtrim($stmtBuf), ';')) {
                try {
                    $pdo->exec($stmtBuf);
                    $applied++;
                } catch (Throwable $e) {
                    $failed++;
                    if ($firstErr === '') $firstErr = $e->getMessage();
                }
                $stmtBuf = '';
            }
        }
        fclose($fp);
        if ($applied === 0) {
            $errors[] = 'Import tidak menjalankan statement apa pun — periksa isi file SQL.';
        } else {
            $log[] = "OK  Import selesai: {$applied} statement sukses" . ($failed ? ", {$failed} dilewati (duplikat/lama)" : '') . '.';
            if ($firstErr) $log[] = 'i   Contoh pesan yang dilewati: ' . $firstErr;
        }
    }

    /* 6. opsional: reset/ganti akun Superadmin */
    if ($pdo && !$errors && $adminEmail !== '') {
        try {
            $hash = password_hash($adminPass, PASSWORD_BCRYPT);
            $cols = [];
            foreach ($pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN) as $colName) {
                $cols[$colName] = true;
            }
            $sets  = ['password = ?', 'status = \'active\''];
            $vals  = [$hash];
            if (isset($cols['email']))    { $sets[] = 'email = ?';    array_unshift($vals, $adminEmail); }
            if (isset($cols['username'])) { $sets[] = 'username = ?'; array_unshift($vals, $adminUsername); }
            $where = isset($cols['role']) ? "role = 'superadmin'" : (isset($cols['is_admin']) ? 'is_admin = 1' : 'id = 1');
            $u = $pdo->prepare('UPDATE users SET ' . implode(', ', $sets) . " WHERE {$where} LIMIT 5");
            $u->execute($vals);
            $log[] = 'OK  Akun Superadmin diperbarui sesuai input installer.';
        } catch (Throwable $e) {
            $log[] = 'i   Superadmin tidak diubah: ' . $e->getMessage();
        }
    }

    /* 7. generate APP_KEY sendiri (random 32 byte, format base64 Laravel) */
    $appKey = 'base64:' . base64_encode(random_bytes(32));

    /* 8. tulis .env */
    if (!$errors) {
        // Deteksi prefix DB khas Hostinger: user & db biasanya "u123456_nama"
        if ($dbUser !== '' && strpos($dbName, '_') !== false && $dbUser !== $dbName) {
            $prefix = substr($dbUser, 0, strpos($dbUser, '_') + 1);
            if ($prefix !== '' && !str_starts_with($dbName, $prefix)) {
                $log[] = "i   Catatan Hostinger: nama database biasanya berprefix sama dengan user MySQL (contoh: {$prefix}{$dbName}). Jika koneksi gagal, cek nama persisnya di hPanel → MySQL Details.";
            }
        }
        $envLines = [
            'APP_NAME="AI Personal Learning OS"',
            'APP_ENV=production',
            "APP_KEY={$appKey}",
            'APP_DEBUG=false',
            'APP_TIMEZONE=Asia/Jakarta',
            'APP_URL=' . $appUrl,
            '',
            'APP_LOCALE=id',
            'APP_FALLBACK_LOCALE=en',
            'APP_FAKER_LOCALE=id_ID',
            '',
            'APP_MAINTENANCE_DRIVER=file',
            'BCRYPT_ROUNDS=12',
            '',
            'LOG_CHANNEL=stack',
            'LOG_STACK=single',
            'LOG_DEPRECATIONS_CHANNEL=null',
            'LOG_LEVEL=debug',
            '',
            'DB_CONNECTION=mysql',
            "DB_HOST={$dbHost}",
            "DB_PORT={$dbPort}",
            "DB_DATABASE={$dbName}",
            "DB_USERNAME={$dbUser}",
            'DB_PASSWORD="' . $dbPass . '"',
            '',
            'SESSION_DRIVER=file',
            'SESSION_LIFETIME=120',
            'SESSION_ENCRYPT=false',
            'SESSION_PATH=/',
            'SESSION_DOMAIN=null',
            '',
            'FILESYSTEM_DISK=local',
            'QUEUE_CONNECTION=sync',
            'BROADCAST_CONNECTION=log',
            '',
            'CACHE_STORE=file',
            'CACHE_PREFIX=',
            '',
            'MAIL_MAILER=log',
            '',
            'AWS_ACCESS_KEY_ID=',
            'AWS_SECRET_ACCESS_KEY=',
            'AWS_DEFAULT_REGION=us-east-1',
            'AWS_BUCKET=',
            'AWS_USE_PATH_STYLE_ENDPOINT=false',
            '',
            'GEMINI_API_KEY=' . $gemKey,
            'GEMINI_MODEL=gemini-2.0-flash',
            '',
            'API_VERSION=v1',
            'CONTENT_VERSION=1.0.0',
            'DATABASE_VERSION=1.0.0',
        ];
        $written = @file_put_contents($ENV_FILE, implode("\n", $envLines) . "\n");
        if ($written === false) {
            $errors[] = 'GAGAL menulis .env di ' . $ENV_FILE . ' — ubah permission folder project menjadi 755/775 lalu ulangi.';
        } else {
            $log[] = 'OK  .env ditulis (APP_KEY baru digenerate otomatis).';
        }
    }

    /* 9. pastikan folder runtime Laravel ada & writable */
    if (!$errors) {
        $dirs = [
            $ROOT . '/storage/framework/data',
            $ROOT . '/storage/framework/cache/data',
            $ROOT . '/storage/framework/sessions',
            $ROOT . '/storage/framework/views',
            $ROOT . '/storage/logs',
            $ROOT . '/storage/app/public',
            $ROOT . '/bootstrap/cache',
        ];
        foreach ($dirs as $d) {
            if (!is_dir($d)) @mkdir($d, 0775, true);
            @file_put_contents($d . '/.gitkeep', '');
        }
        @chmod($ROOT . '/storage', 0775);
        $log[] = 'OK  Folder runtime (storage/framework/*, logs, bootstrap/cache) siap.';
    }

    /* 10. tandai selesai + kunci installer */
    if (!$errors) {
        @mkdir(dirname($INSTALLED), 0775, true);
        file_put_contents($INSTALLED, json_encode([
            'installed_at' => date('c'),
            'app_url'      => $appUrl,
            'db_database'  => $dbName,
            'db_host'      => $dbHost,
            'db_port'      => $dbPort,
            'db_username'  => $dbUser,
        ], JSON_PRETTY_PRINT));
        @file_put_contents($LOCKED_FLAG, 'installed');
        @copy(__DIR__ . '/.htaccess.locked.example', __DIR__ . '/.htaccess');
        $done = true;
        $log[] = 'OK  Instalasi SELESAI. Installer dikunci.';
    }

    $_POST = []; // hindari resubmit value
}

/* ============================= TAMPILAN ============================= */
function e_(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalasi — AI Personal Learning OS</title>
<style>
  :root{--navy:#0b1a2b;--card:#12263d;--gold:#d4af37;--txt:#e8eef5;--mut:#8fa3b8;--ok:#3ddc84;--bad:#ff5d5d;--warn:#ffb347}
  *{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
  body{background:linear-gradient(160deg,var(--navy),#0f2138 60%,#0b1a2b);color:var(--txt);min-height:100vh;padding:28px 16px}
  .wrap{max-width:880px;margin:0 auto}
  h1{font-size:26px;color:var(--gold);margin-bottom:6px}
  .sub{color:var(--mut);margin-bottom:24px;font-size:14px}
  .card{background:var(--card);border:1px solid #1d3a5a;border-radius:14px;padding:22px;margin-bottom:18px}
  .card h2{font-size:16px;margin-bottom:14px;color:var(--gold)}
  label{display:block;font-size:12px;color:var(--mut);margin:10px 0 4px}
  input{width:100%;padding:11px 12px;border-radius:8px;border:1px solid #24466b;background:#0d1f33;color:var(--txt);font-size:14px}
  input:focus{outline:2px solid var(--gold)}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}
  .btn{display:inline-block;margin-top:18px;padding:13px 26px;border:0;border-radius:9px;background:var(--gold);color:#132;font-weight:700;font-size:15px;cursor:pointer;text-decoration:none}
  .btn:hover{filter:brightness(1.1)}
  .err{background:#3a1520;border:1px solid var(--bad);color:#ffc9c9;padding:12px 14px;border-radius:9px;margin-bottom:14px;font-size:13px;line-height:1.6}
  pre.log{background:#08131f;border:1px solid #1d3a5a;border-radius:9px;padding:14px;font-size:12px;line-height:1.7;overflow:auto;color:#bfe3c8;white-space:pre-wrap}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
  .b-ok{background:#123a25;color:var(--ok)}.b-bad{background:#3a1520;color:var(--bad)}.b-warn{background:#3a2f15;color:var(--warn)}
  a{color:var(--gold)}
  .small{font-size:12px;color:var(--mut);line-height:1.7}
  .hint{font-size:11px;color:#6d87a0;margin-top:3px}
  code{background:#08131f;padding:2px 6px;border-radius:5px;font-size:12px}
</style>
</head>
<body>
<div class="wrap">
  <h1>⚙️ Instalasi — AI Personal Learning OS</h1>
  <p class="sub">Modul installer mandiri (standalone PHP — tidak bergantung Composer/Laravel booting) — dirancang khusus untuk shared hosting Hostinger.</p>

  <?php if ($done): ?>
    <div class="card">
      <span class="badge b-ok">TERPASANG</span>
      <h2 style="margin-top:12px">Aplikasi sudah terinstal ✅</h2>
      <?php $meta = json_decode((string) @file_get_contents($INSTALLED), true) ?: []; ?>
      <p class="small">
        Database: <code><?= e_($meta['db_database'] ?? '-') ?></code> @ <code><?= e_($meta['db_host'] ?? '-') ?></code><br>
        Tanggal: <?= e_($meta['installed_at'] ?? '-') ?>
      </p>
      <p style="margin-top:14px">
        <a class="btn" href="<?= e_(($meta['app_url'] ?? '/') . '/login') ?>">🚀 Buka Halaman Login</a>
      </p>
      <p class="small" style="margin-top:16px">
        ⚠️ Demi keamanan, hapus folder <code>install/</code> dari server setelah aplikasi berjalan normal.<br>
        Untuk instal ulang: hapus <code>storage/app/installed.json</code> dan <code>install/.installed</code>, lalu kosongkan database.
      </p>
    </div>
  <?php endif; ?>

  <?php if ($log): ?>
    <div class="card"><h2>📋 Log Proses</h2><pre class="log"><?= e_(implode("\n", $log)) ?></pre></div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="card"><?php foreach ($errors as $er): ?><div class="err">❌ <?= e_($er) ?></div><?php endforeach; ?></div>
  <?php endif; ?>

  <?php if ($log && !$done): ?>
    <div class="card" style="border-color:#ffb347">
      <h2 style="color:#ffb347">⚠️ Catatan</h2>
      <pre class="log"><?php foreach ($log as $l) echo htmlspecialchars($l) . "\n"; ?></pre>
    </div>
  <?php endif; ?>

  <?php if (!$done): ?>
  <div class="card">
    <h2>1️⃣ Prasyarat (lakukan sebelum mengisi form)</h2>
    <p class="small">
      ☑️ Buat database MySQL di panel hosting (mis. <code>belajar_db</code>).<br>
      ☑️ Buat user MySQL &amp; <b>assign user tersebut ke database</b> (All Privileges).<br>
      ☑️ Upload semua file project ke <code>public_html/belajar/</code> — termasuk folder <code>vendor/</code>.<br>
      <span class="hint">&nbsp;&nbsp;&nbsp;Vendor belum ter-upload? Installer tetap bisa menuliskan .env &amp; import DB. Tapi aplikasi butuh vendor/ agar bisa berjalan.</span><br>
      ☑️ Set PHP Version ≥ 8.2 (Hosting → Advanced → PHP Configuration).<br>
      ☑️ Letakkan <code>Belajar.sql</code> di root project — atau biarkan installer mengunduhnya (isi URL opsional di bawah).
    </p>
  </div>

  <div class="card">
    <h2>2️⃣ Konfigurasi Database &amp; Aplikasi</h2>
    <?php if ($oldHasEnv): ?>
      <p class="small" style="background:#3a2f15;border:1px solid #6b5a20;color:#ffe9a8;padding:10px 14px;border-radius:9px;margin-bottom:6px">
        🔧 <b>Mode Perbaikan</b> — file .env lama terdeteksi. Kolom sudah terisi dari konfigurasi sebelumnya.<br>
        Kosongkan kolom Password MySQL untuk <b>mempertahankan password lama</b>. Isi ulang hanya bila kredensial berubah.
      </p>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <div class="grid">
        <div>
          <label>MySQL Host</label>
          <input name="db_host" value="<?= e_($_POST['db_host'] ?? $old['db_host']) ?>" placeholder="localhost">
        </div>
        <div>
          <label>MySQL Port</label>
          <input name="db_port" value="<?= e_($_POST['db_port'] ?? $old['db_port']) ?>" placeholder="3306">
        </div>
        <div>
          <label>Nama Database *</label>
          <input name="db_database" required value="<?= e_($_POST['db_database'] ?? $old['db_database']) ?>" placeholder="belajar_db">
        </div>
        <div>
          <label>Username MySQL *</label>
          <input name="db_username" required value="<?= e_($_POST['db_username'] ?? $old['db_username']) ?>" placeholder="u123456_admin">
        </div>
      </div>
      <label>Password MySQL *</label>
      <input type="password" name="db_password" placeholder="••••••••">
      <label>URL Aplikasi</label>
      <input name="app_url" value="<?= e_($_POST['app_url'] ?? $old['app_url']) ?>" placeholder="https://belajar.kasirmo.com">
      <label>Google Gemini API Key <span class="hint">(opsional — untuk fitur AI Teacher)</span></label>
      <input type="password" name="gemini_api_key" placeholder="AIza...">
      <label>URL Belajar.sql <span class="hint">(opsional — hanya dipakai bila file Belajar.sql tidak ada di server)</span></label>
      <input name="sql_url" value="<?= e_($_POST['sql_url'] ?? '') ?>" placeholder="https://raw.githubusercontent.com/USER/REPO/main/Belajar.sql">

      <h2 style="margin-top:22px">3️⃣ Opsional — Reset Akun Superadmin</h2>
      <p class="small">Biarkan kosong untuk memakai akun default dari Belajar.sql. Isi ketiganya untuk mengganti.</p>
      <div class="grid">
        <div>
          <label>Email Superadmin</label>
          <input name="admin_email" value="<?= e_($_POST['admin_email'] ?? '') ?>" placeholder="Steveandriebagus@gmail.com">
        </div>
        <div>
          <label>Username</label>
          <input name="admin_username" value="<?= e_($_POST['admin_username'] ?? '') ?>" placeholder="Makutharama">
        </div>
      </div>
      <label>Password Baru (min 8 karakter)</label>
      <input type="password" name="admin_password" placeholder="••••••••">

      <button class="btn" type="submit">🔧 Jalankan Instalasi</button>
    </form>
  </div>

  <div class="card">
    <h2>Status Server Saat Ini</h2>
    <p class="small">
      PHP: <span class="badge <?= version_compare(PHP_VERSION, '8.2.0', '>=') ? 'b-ok' : 'b-bad' ?>"><?= e_(PHP_VERSION) ?></span>&nbsp;
      pdo_mysql: <span class="badge <?= extension_loaded('pdo_mysql') ? 'b-ok' : 'b-bad' ?>"><?= extension_loaded('pdo_mysql') ? 'aktif' : 'TIDAK AKTIF' ?></span>&nbsp;
      Belajar.sql lokal: <span class="badge <?= findLocalSql($ROOT) ? 'b-ok' : 'b-warn' ?>"><?= findLocalSql($ROOT) ? 'ditemukan' : 'tidak ada (unduh/manual)' ?></span>&nbsp;
      vendor/: <span class="badge <?= is_dir($ROOT . '/vendor') ? 'b-ok' : 'b-warn' ?>"><?= is_dir($ROOT . '/vendor') ? 'ada' : 'BELUM ADA (upload nanti)' ?></span><br>
      Project root terdeteksi: <code><?= e_($ROOT) ?></code><br>
      .env dapat ditulis: <span class="badge <?= (!is_file($ENV_FILE) || is_writable($ENV_FILE)) ? 'b-ok' : 'b-bad' ?>"><?= (!is_file($ENV_FILE) || is_writable($ENV_FILE)) ? 'ya' : 'TIDAK (chmod 755 folder project)' ?></span>
    </p>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
