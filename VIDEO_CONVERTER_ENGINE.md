# Video Converter Engine (Laravel + FFmpeg + Queue)

## Arsitektur

Upload Video → Laravel Controller → Store Source File → Dispatch Queue `media` → Worker `ConvertVideoJob` → Convert WebM + MP4 + Thumbnail + Duration → Save `video_assets` → Update referensi CMS (Slider, Portfolio, Blog) → Play di website.

## Komponen Utama

- Package FFmpeg Laravel: `pbmedia/laravel-ffmpeg`
- Config binary:
  - [laravel-ffmpeg.php](file:///d:/Xampp/htdocs/PRODUCTION%20HOUSE%20NUSANTARA/config/laravel-ffmpeg.php)
  - Linux default: `/usr/bin/ffmpeg` dan `/usr/bin/ffprobe`
  - Windows default: `ffmpeg.exe` dan `ffprobe.exe`
- Tabel video engine: `video_assets` (plus field conversion terbaru)
- Model:
  - [VideoAsset.php](file:///d:/Xampp/htdocs/PRODUCTION%20HOUSE%20NUSANTARA/app/Models/VideoAsset.php)
  - [Video.php](file:///d:/Xampp/htdocs/PRODUCTION%20HOUSE%20NUSANTARA/app/Models/Video.php)
- Job queue converter:
  - [ConvertVideoJob.php](file:///d:/Xampp/htdocs/PRODUCTION%20HOUSE%20NUSANTARA/app/Jobs/ConvertVideoJob.php)
- Engine upload:
  - [VideoConversionEngine.php](file:///d:/Xampp/htdocs/PRODUCTION%20HOUSE%20NUSANTARA/app/Support/VideoConversionEngine.php)

## Integrasi CMS

- Slider CMS upload video otomatis register ke engine queue.
- Portfolio CMS upload video otomatis register ke engine queue.
- Blog/News CMS upload video otomatis register ke engine queue.
- Saat konversi selesai, job mengupdate referensi path source menjadi path WebM pada:
  - `hero_slides.video_url`
  - `cms_slider_video_slide_meta`
  - `cms_portfolio_payload`
  - `cms_blog_news_payload`

## Endpoint Upload Umum

- Upload:
  - `POST /dashboard/media/videos/upload`
  - field: `video` (required), `title` (optional), `context` (optional)
- Status:
  - `GET /dashboard/media/videos/{id}`
- Retry:
  - `POST /dashboard/media/videos/{id}/retry`

## UI Monitor Queue

- Halaman dashboard analytics (`/dashboard`) sekarang menampilkan tabel monitor queue conversion.
- Menampilkan status per video: `uploaded`, `processing`, `ready`, `failed`.
- Menampilkan progress visual per status.
- Menyediakan tombol `Retry` untuk status `failed` agar job dikirim ulang ke queue `media`.

## Format Input yang Didukung

Semua format `video/*` didukung oleh validasi upload, termasuk praktik umum produksi:

- mp4, mov, mkv, avi, mxf, webm, flv, wmv
- prores/dnxhd/mts/m2ts selama dikenali mime video oleh sistem + FFmpeg tersedia

## Worker Queue

Pastikan queue worker aktif:

```bash
php artisan queue:work --queue=media,default --tries=1 --timeout=0
```

Untuk mode dev pada project ini, `composer dev` sudah menjalankan queue listener.

## Checklist Verifikasi Cepat

1. Upload video >100MB dari CMS Slider/Portfolio/Blog.
2. Pastikan row `video_assets` status berubah: `uploaded` → `processing` → `ready`.
3. Pastikan field hasil terisi: `webm_path`, `mp4_path`, `thumb_path`, `duration_seconds`.
4. Cek website menggunakan path WebM hasil konversi.
