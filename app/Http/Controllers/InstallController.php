<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PDO;

class InstallController extends Controller
{
    /**
     * File penanda instalasi sudah selesai.
     */
    private const INSTALLED_FILE = 'storage/app/installed.json';

    /**
     * Jangan izinkan instalasi berjalan jika aplikasi sudah terpasang.
     */
    private function alreadyInstalled(): bool
    {
        return File::exists(base_path(self::INSTALLED_FILE));
    }

    /**
     * Installer dapat dibuka lewat /install atau /install/{token}
     * (token pengaman opsional dari .env: INSTALL_TOKEN).
     */
    public function index(Request $request, ?string $token = null)
    {
        if (! $this->tokenAllowed($token)) {
            abort(404);
        }

        if ($this->alreadyInstalled()) {
            return view('install.locked');
        }

        $old = [];
        try {
            $old = $this->readEnvValues(base_path('.env'));
        } catch (\Throwable) {
            $old = [];
        }

        return view('install.index', [
            'requirements' => $this->requirements(),
            'action' => route('install.store', $token !== null ? ['token' => $token] : []),
            'old' => [
                'db_host' => $old['DB_HOST'] ?? 'localhost',
                'db_port' => $old['DB_PORT'] ?? '3306',
                'db_database' => $old['DB_DATABASE'] ?? '',
                'db_username' => $old['DB_USERNAME'] ?? '',
                'app_url' => $old['APP_URL'] ?? rtrim($request->getSchemeAndHttpHost(), '/'),
            ],
        ]);
    }

    private function tokenAllowed(?string $token): bool
    {
        $required = trim((string) env('INSTALL_TOKEN', ''));
        if ($required === '') {
            return true;
        }

        return $token !== null && hash_equals($required, $token);
    }

    /**
     * Cek kebutuhan server (PHP, ekstensi, direktori writable).
     */
    private function requirements(): array
    {
        $rows = [
            ['label' => 'Versi PHP (minimal 8.2)', 'ok' => version_compare(PHP_VERSION, '8.2.0', '>='), 'value' => PHP_VERSION],
            ['label' => 'Ekstensi pdo_mysql', 'ok' => extension_loaded('pdo_mysql'), 'value' => extension_loaded('pdo_mysql') ? 'terpasang' : 'tidak ditemukan'],
            ['label' => 'Ekstensi mbstring', 'ok' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? 'terpasang' : 'tidak ditemukan'],
            ['label' => 'Ekstensi openssl', 'ok' => extension_loaded('openssl'), 'value' => extension_loaded('openssl') ? 'terpasang' : 'tidak ditemukan'],
            ['label' => 'Ekstensi json', 'ok' => extension_loaded('json'), 'value' => extension_loaded('json') ? 'terpasang' : 'tidak ditemukan'],
            ['label' => 'Direktori storage/ dapat ditulis', 'ok' => is_writable(storage_path()), 'value' => is_writable(storage_path()) ? 'writable' : 'TIDAK writable'],
            ['label' => 'Direktori bootstrap/cache/ dapat ditulis', 'ok' => is_writable(base_path('bootstrap/cache')), 'value' => is_writable(base_path('bootstrap/cache')) ? 'writable' : 'TIDAK writable'],
        ];

        return $rows;
    }

    public function store(Request $request, ?string $token = null)
    {
        if (! $this->tokenAllowed($token)) {
            abort(404);
        }

        if ($this->alreadyInstalled()) {
            return redirect()->route('install.index')->withErrors(['installed' => 'Aplikasi sudah terinstal. Hapus file storage/app/installed.json bila ingin menginstal ulang.']);
        }

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'numeric', 'between:1,65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'gemini_key' => ['nullable', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_username' => ['required', 'string', 'max:100'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'app_name' => 'Nama aplikasi',
            'app_url' => 'URL aplikasi',
            'db_host' => 'Server MySQL (host)',
            'db_port' => 'Port MySQL',
            'db_database' => 'Nama database',
            'db_username' => 'Username MySQL',
            'db_password' => 'Password MySQL',
            'admin_name' => 'Nama Superadmin',
            'admin_email' => 'Email Superadmin',
            'admin_username' => 'Username Superadmin',
            'admin_password' => 'Password Superadmin',
        ]);

        // ---- 1. Test koneksi MySQL dengan PDO langsung ----
        if (! extension_loaded('pdo_mysql')) {
            return back()->withInput()->withErrors([
                'db_host' => 'Ekstensi PHP pdo_mysql belum aktif di server ini. Aktifkan ekstensi pdo_mysql melalui panel hosting / php.ini, lalu ulangi instalasi.',
            ]);
        }
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $data['db_host'], $data['db_port'], $data['db_database']),
                $data['db_username'],
                $data['db_password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 8]
            );
            $pdo->query('SELECT 1');
        } catch (\PDOException $e) {
            return back()->withInput()->withErrors([
                'db_host' => 'Koneksi MySQL gagal: '.$e->getMessage().' — periksa host/port/database/user/password, dan pastikan database sudah dibuat di phpMyAdmin.',
            ]);
        }

        // ---- 2. Tulis .env & .env.production ----
        try {
            $key = 'base64:'.base64_encode(random_bytes(32));
            $envContent = $this->buildEnv($data, $key);
            File::put(base_path('.env'), $envContent);
            File::put(base_path('.env.production'), $envContent);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['db_host' => 'Gagal menulis file .env: '.$e->getMessage()]);
        }

        // Reset konfigurasi runtime agar nilai .env baru terpakai.
        putenv('APP_KEY='.$key);
        $_ENV['APP_KEY'] = $key;
        $_SERVER['APP_KEY'] = $key;
        config(['app.key' => $key]);
        config([
            'database.connections.mysql.host' => $data['db_host'],
            'database.connections.mysql.port' => $data['db_port'],
            'database.connections.mysql.database' => $data['db_database'],
            'database.connections.mysql.username' => $data['db_username'],
            'database.connections.mysql.password' => $data['db_password'] ?? '',
        ]);
        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        config(['app.url' => rtrim($data['app_url'], '/')]);

        $steps = [];
        $log = function (string $name, callable $fn) use (&$steps): void {
            $t0 = microtime(true);
            try {
                $detail = $fn();
                $steps[] = ['name' => $name, 'ok' => true, 'detail' => $detail ?: 'selesai', 'ms' => round((microtime(true) - $t0) * 1000)];
            } catch (\Throwable $e) {
                $steps[] = ['name' => $name, 'ok' => false, 'detail' => $e->getMessage(), 'ms' => round((microtime(true) - $t0) * 1000)];
                throw $e;
            }
        };

        // Panggil Artisan lewat proses PHP CLI terpisah agar konfigurasi .env baru
        // benar-benar dimuat ulang (penting di shared hosting / opcache).
        $artisan = function (string $cmd, array $args = []) {
            $php = PHP_BINARY ?: 'php';
            $base = base_path('artisan');
            $full = \Illuminate\Support\Str::startsWith($cmd, 'db:seed')
                ? [$php, $base, $cmd, '--force']
                : [$php, $base, $cmd];
            foreach ($args as $k => $v) {
                if ($v === null) {
                    $full[] = $k;
                } else {
                    $full[] = $k;
                    $full[] = (string) $v;
                }
            }
            $out = [];
            $code = 0;
            exec(implode(' ', array_map('escapeshellarg', $full)).' 2>&1', $out, $code);
            $text = trim(implode("\n", $out));
            if ($code !== 0) {
                throw new \RuntimeException($text !== '' ? $text : 'Perintah '.$cmd.' gagal (exit '.$code.').');
            }

            return $text;
        };

        try {
            // ---- 3. Jalankan seluruh migrasi ----
            $log('Menjalankan migrasi database', fn () => $this->shortOutput($artisan('migrate')));

            // ---- 4. Seed konten pembelajaran + superadmin ----
            $log('Mengisi data awal (kurikulum, mapel, bab, materi, soal, tryout, TKA)', fn () => $this->shortOutput($artisan('db:seed')));

            // ---- 5. Pastikan akun Superadmin sesuai input ----
            $log('Menyiapkan akun Superadmin', function () use ($data) {
                $now = now();
                $payload = [
                    'name' => $data['admin_name'],
                    'username' => $data['admin_username'],
                    'password' => Hash::make($data['admin_password']),
                    'primary_role' => \App\Models\User::ROLE_SUPERADMIN,
                    'role_slugs' => json_encode(['superadmin']),
                    'approval_status' => \App\Models\User::APPROVAL_APPROVED,
                    'email_verified_at' => $now,
                    'updated_at' => $now,
                ];
                if (DB::getSchemaBuilder()->hasColumn('users', 'status')) {
                    $payload['status'] = \App\Models\User::STATUS_ACTIVE;
                }

                $user = DB::table('users')->where('email', strtolower($data['admin_email']))->first();
                if ($user) {
                    DB::table('users')->where('id', $user->id)->update($payload);
                } else {
                    DB::table('users')->insert($payload + [
                        'email' => strtolower($data['admin_email']),
                        'created_at' => $now,
                    ]);
                }

                // Bersihkan akun demo bawaan agar tidak ada admin lain selain superadmin installer.
                DB::table('users')
                    ->where('email', '!=', strtolower($data['admin_email']))
                    ->whereRaw("LOWER(COALESCE(primary_role,'')) IN ('admin','superadmin')")
                    ->update(['primary_role' => 'User', 'role_slugs' => json_encode(['user'])]);

                return 'Akun Superadmin berhasil disiapkan.';
            });

            // ---- 6. Simpan API key Gemini (opsional) ----
            if (! empty($data['gemini_key'])) {
                $keyValue = $data['gemini_key'];
                $log('Menyimpan Gemini API Key', function () use ($keyValue) {
                    if (Schema::hasTable('settings')) {
                        DB::table('settings')->updateOrInsert(
                            ['key' => 'gemini_api_key'],
                            ['value' => \Illuminate\Support\Facades\Crypt::encryptString($keyValue), 'group' => 'ai', 'is_public' => 0, 'updated_at' => now()]
                        );
                    }

                    return 'API key tersimpan terenkripsi di database (tidak diekspos ke client).';
                });
            }

            // ---- 7. Optimasi cache untuk shared hosting ----
            $log('Membersihkan & membangun cache konfigurasi', function () use ($artisan) {
                foreach (['config:clear', 'cache:clear', 'view:clear', 'config:cache', 'view:cache'] as $cmd) {
                    try {
                        $artisan($cmd);
                    } catch (\Throwable) {
                        // lanjutkan — tidak fatal
                    }
                }

                return 'Cache siap.';
            });

            // ---- 8. Verifikasi akhir ----
            $log('Verifikasi hasil instalasi', function () {
                $counts = [
                    'users' => DB::table('users')->count(),
                    'subjects' => Schema::hasTable('subjects') ? DB::table('subjects')->count() : 0,
                    'materials' => Schema::hasTable('materials') ? DB::table('materials')->count() : 0,
                    'questions' => Schema::hasTable('questions') ? DB::table('questions')->count() : 0,
                    'tryouts' => Schema::hasTable('tryouts') ? DB::table('tryouts')->count() : 0,
                ];

                if ($counts['users'] === 0) {
                    throw new \RuntimeException('Verifikasi gagal: tabel users kosong.');
                }

                return 'Terverifikasi — users: '.$counts['users'].', mapel: '.$counts['subjects'].', materi: '.$counts['materials'].', soal: '.$counts['questions'].', tryout: '.$counts['tryouts'].'.';
            });
        } catch (\Throwable $e) {
            // Simpan progres langkah yang sudah berhasil agar bisa ditampilkan.
            File::ensureDirectoryExists(storage_path('app'));
            File::put(storage_path('app/install_failed.json'), json_encode([
                'failed_at' => now()->toDateTimeString(),
                'error' => $e->getMessage(),
                'steps' => $steps,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return view('install.result', [
                'success' => false,
                'steps' => $steps,
                'error' => $e->getMessage(),
                'appUrl' => rtrim($data['app_url'], '/'),
                'backUrl' => $request->fullUrl(),
            ]);
        }

        // ---- 9. Tandai instalasi selesai ----
        File::ensureDirectoryExists(storage_path('app'));
        File::put(storage_path('app/installed.json'), json_encode([
            'installed_at' => now()->toDateTimeString(),
            'app_url' => $data['app_url'],
            'database' => $data['db_database'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return view('install.result', [
            'success' => true,
            'steps' => $steps,
            'error' => null,
            'appUrl' => rtrim($data['app_url'], '/'),
            'backUrl' => $request->fullUrl(),
        ]);
    }

    /**
     * Potong output artisan yang terlalu panjang untuk ditampilkan di halaman hasil.
     */
    private function shortOutput(string $text): string
    {
        $text = trim(preg_replace('/\R{3,}/', "\n\n", $text));

        return \Illuminate\Support\Str::limit($text, 1500);
    }

    /**
     * Bangun isi file .env dari input form.
     */
    private function buildEnv(array $d, string $key): string
    {
        // Nama aplikasi dibersihkan dari karakter yang merusak file .env.
        $appName = preg_replace('/[^A-Za-z0-9 \-_.]/', '', $d['app_name']);
        $appName = str_replace(' ', '-', trim($appName));

        // Semua nilai dibungkus kutip ganda agar aman (password DB boleh berisi spasi/karakter khusus).
        $esc = fn (?string $v): string => str_replace(['\\', '"', "\r", "\n"], ['\\\\', '\\"', '', ''], (string) $v);

        return <<<ENV
        APP_NAME="{$esc($appName)}"
        APP_ENV=production
        APP_KEY={$esc($key)}
        APP_DEBUG=false
        APP_URL="{$esc(rtrim($d['app_url'], '/'))}"
        APP_TIMEZONE=Asia/Jakarta
        APP_LOCALE=id
        APP_FALLBACK_LOCALE=en

        LOG_CHANNEL=single
        LOG_LEVEL=warning

        DB_CONNECTION=mysql
        DB_HOST="{$esc($d['db_host'])}"
        DB_PORT="{$esc($d['db_port'])}"
        DB_DATABASE="{$esc($d['db_database'])}"
        DB_USERNAME="{$esc($d['db_username'])}"
        DB_PASSWORD="{$esc($d['db_password'] ?? '')}"

        BROADCAST_DRIVER=log
        CACHE_STORE=file
        QUEUE_CONNECTION=database
        SESSION_DRIVER=file
        SESSION_LIFETIME=120

        GEMINI_API_KEY=""
        GEMINI_MODEL=gemini-2.0-flash

        CDN_BASE_URL="\${APP_URL}/storage"
        ENV;
    }

    /**
     * Baca pasangan key=value sederhana dari file .env.
     */
    private function readEnvValues(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $out[trim($k)] = trim($v, " \"'");
        }

        return $out;
    }
}
