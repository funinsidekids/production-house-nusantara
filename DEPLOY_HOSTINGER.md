# Deploy Laravel ke Hostinger (Aman & Minim Risiko)

## 1) Requirement minimum hosting

- PHP: `>= 8.4` (disarankan 8.4+)
- Extensions wajib: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `phar`, `session`, `simplexml`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`, `zip`
- Database: MySQL/MariaDB aktif
- Storage writable: `storage/` dan `bootstrap/cache/`

## 2) Konfigurasi PHP di panel Hostinger

- Pilih versi PHP minimal `8.4`
- Aktifkan extension `intl` dan `zip`
- Jika ada opsi OPcache, aktifkan untuk performa

## 3) Deploy command yang direkomendasikan

Jalankan di server/proyek:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
composer hostinger:check
composer hostinger:optimize
```

## 4) Nilai `.env` yang aman untuk shared hosting

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://domainkamu.com`
- `QUEUE_CONNECTION=database` atau `sync`
- `BROADCAST_CONNECTION=log` bila tidak menjalankan service websocket terpisah
- `CACHE_STORE=file` atau `database`
- `SESSION_DRIVER=file` atau `database`

## 5) Verifikasi pascadeploy

```bash
php artisan app:hosting-check
php artisan about
```

Jika `app:hosting-check` gagal, perbaiki item yang ditandai sebelum aplikasi go-live.

Catatan:
- Jika kamu menjalankan test di server (bukan hanya deploy production), PHPUnit 13 memerlukan PHP minimal `8.4.1`.
