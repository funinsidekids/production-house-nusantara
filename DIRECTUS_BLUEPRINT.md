# Directus Blueprint Import

## File yang disediakan

- `resources/blueprints/directus/directus-schema-blueprint.json`
- `resources/blueprints/directus/directus-field-mapping.json`

Keduanya juga bisa diunduh langsung dari dashboard:

- `Dashboard > Website CMS > General > Download Directus Schema Blueprint (JSON)`
- `Dashboard > Website CMS > General > Download Field Mapping (JSON)`

## One-Click Setup di Directus

1. Login ke Directus sebagai admin.
2. Buka `Settings > Data Model`.
3. Klik menu import schema snapshot.
4. Upload file `directus-schema-blueprint.json`.
5. Apply schema.

Setelah import selesai, collection default yang tersedia:

- `landing_content`
- `hero_slides`

## Konfigurasi Dashboard

Di `Dashboard > Website CMS > General`:

- Set `Dashboard CMS Engine` = `Directus (Super Modern CMS)`.
- Isi `Directus Base URL`.
- Isi `Primary Collection` sesuai nama collection settings di Directus (default blueprint: `landing_content`).
- Isi `Slides Collection` sesuai nama collection slides di Directus (default blueprint: `hero_slides`).
- Isi `Static Access Token`.
- Simpan.

## Auto Provision dari Dashboard

Tanpa buka panel Directus, kamu bisa langsung klik:

- `Provision Collections + Roles + Permissions`

Fitur ini akan membuat collection dan field yang dibutuhkan jika belum ada, berdasarkan blueprint internal.

Sekaligus akan membuat role default:

- `PHN Admin`
- `PHN Editor`

Dan auto set permission per collection:

- `PHN Admin`: `create`, `read`, `update`, `delete`
- `PHN Editor`: `create`, `read`, `update`

## Sinkronisasi Live

- Tombol `Push ke Directus`:
  - Laravel `landing_settings` -> Directus collection settings
  - Laravel `hero_slides` -> Directus collection slides
- Tombol `Pull dari Directus`:
  - Directus collection settings -> Laravel `landing_settings`
  - Directus collection slides -> Laravel `hero_slides`

## Mapping Ringkas

Detail mapping ada di `directus-field-mapping.json`.

Mapping utama:

- Settings:
  - `landing_settings.key` -> `landing_content.key`
  - `landing_settings.value` -> `landing_content.value`
- Hero Slides:
  - `hero_slides.id` -> `hero_slides.local_id`
  - `hero_slides.title` -> `hero_slides.title`
  - `hero_slides.caption` -> `hero_slides.caption`
  - `hero_slides.video_url` -> `hero_slides.video_url`
  - `hero_slides.cta_text` -> `hero_slides.cta_text`
  - `hero_slides.cta_url` -> `hero_slides.cta_url`
  - `hero_slides.sort_order` -> `hero_slides.sort_order`
  - `hero_slides.duration_seconds` -> `hero_slides.duration_seconds`
  - `hero_slides.overlay_opacity` -> `hero_slides.overlay_opacity`
  - `hero_slides.is_active` -> `hero_slides.is_active`
