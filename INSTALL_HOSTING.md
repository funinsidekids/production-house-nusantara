# PANDUAN INSTALASI — belajar.kasirmo.com

Aplikasi: **AI Personal Learning OS — Modul Belajar Kelas XII MA/SMA** (Laravel)

## ⭐ CARA CEPAT (rekomendasi untuk Hostinger shared hosting)

Karena `/install` versi Laravel sebelumnya Error 500 di hosting, kini tersedia
**installer STANDALONE** yang TIDAK bergantung pada Laravel/Composer/.htaccess rewrite:

```
https://belajar.kasirmo.com/install/     <- folder install/index.php (root project)
https://belajar.kasirmo.com/install      <- via .htaccess root (rewrite ke install/)
```

### Langkah-langkah

1. **Buat database MySQL** di panel hosting (hPanel → MySQL):
   - nama database, mis. `u123456_belajar`
   - buat user + password MySQL, lalu **assign user ke database** (All Privileges).
2. **Upload SEMUA file project** ke `public_html/belajar/` (docroot subdomain),
   termasuk: `app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ vendor/ install/`, `.htaccess`, dan `Belajar.sql`.
   - `vendor/` WAJIB ada agar aplikasi bisa jalan setelah instalasi
     (upload via FileZilla/FTP, atau Terminal: `composer install --no-dev`).
   - Jika File Manager hosting kesulitan upload `vendor/` (ribuan file), zip dulu
     seluruh project → upload zip → extract di File Manager.
3. **Set PHP ≥ 8.2** (hPanel → Advanced → PHP Configuration) dan pastikan ekstensi
   `pdo_mysql` aktif.
4. Buka **https://belajar.kasirmo.com/install/** → isi form (MySQL host localhost,
   port 3306, nama DB, user, password, URL aplikasi, Gemini API key opsional) →
   klik **Jalankan Instalasi**.
5. Installer otomatis:
   - menguji koneksi MySQL;
   - mengimpor `Belajar.sql` (56 tabel + data mapel/materi/soal/akun Superadmin)
     — jika file SQL tidak ada di server, installer mengunduhnya (atau tempel URL
     di kolom "URL Belajar.sql"); jika DB sudah berisi tabel, import dilewati;
   - menuliskan `.env` lengkap dengan APP_KEY digenerate sendiri;
   - membuat folder runtime `storage/framework/{sessions,views,cache}` & logs
     (penyebab umum Error 500);
   - mengunci dirinya (`storage/app/installed.json` + `.htaccess` deny).
6. Buka **https://belajar.kasirmo.com/login** → aplikasi siap digunakan.
7. Setelah sukses, **hapus folder `install/`** dari server demi keamanan.

> Catatan: Anda TIDAK perlu import Belajar.sql manual lewat phpMyAdmin —
> installer yang melakukannya. Import manual tetap boleh; installer akan
> mendeteksi DB sudah terisi dan melompatinya.

## Troubleshooting

| Gejala | Penyebab & Solusi |
|---|---|
| `/install/` tetap 404 | Folder `install/` tidak ikut ter-upload, atau docroot bukan folder project. Cek: akses langsung `https://belajar.kasirmo.com/install/index.php`. |
| `/install/` Error 500 | Jarang terjadi (file standalone). Pastikan PHP ≥ 8.2 & `pdo_mysql` aktif. |
| Halaman utama Error 500 | 1) `vendor/` belum ada → upload/composer install. 2) PHP < 8.2 → ganti versi PHP. 3) `storage/` & `bootstrap/cache` tidak writable → chmod 775. 4) Jalankan Terminal: `php artisan optimize:clear`. |
| Error 500 "please run composer install" | `vendor/` tidak lengkap — upload ulang via FTP (File Manager sering melewatkan file tersembunyi/jumlah besar). |
| Login error / session hilang | Pastikan `.env` hasil installer tidak dihapus; `SESSION_DRIVER=file` sudah diset installer. |
| Install ulang | Hapus `storage/app/installed.json`, `install/.installed`, `install/.htaccess` (hasil kunci), lalu kosongkan/drop database. |

## Cara lama (masih tersedia)
- Installer Laravel: `https://belajar.kasirmo.com/install` via route web (butuh Laravel boot normal).
- Diagnosa mandiri: `https://belajar.kasirmo.com/install.php` (cek PHP, vendor, permission, DB).
