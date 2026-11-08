# Lost n Found Kampus

Aplikasi web untuk melaporkan, mencari, dan mengklaim barang hilang atau ditemukan di kampus. Pengguna membuat laporan (foto, kategori, lokasi, tanggal), semua laporan tampil di katalog yang bisa dicari dan difilter, dan pemilik barang mengajukan klaim yang diverifikasi petugas sebelum barang diserahkan.

> Panduan pengembangan lengkap ada di [prd.md](prd.md).

## Status implementasi

- ✅ Setup Laravel 12 + koneksi SQLite/MySQL
- ✅ Migration tabel: `users`, `categories`, `locations`, `items`
- ✅ Model: `User`, `Category`, `Location`, `Item` (relasi + accessor `code`)
- ✅ Katalog beranda (`GET /`) dengan kartu tiket
- ✅ Detail barang (`GET /items/{item}`)
- ✅ REST API read-only: `GET /api/items`, `GET /api/items/{id}`
- ✅ Filter katalog via AJAX (jQuery) tanpa reload halaman
- ✅ Paginasi Bootstrap 5 (`Paginator::useBootstrapFive()`)
- ✅ Tes fitur katalog (`tests/Feature/CatalogApiTest.php`)
- ⏳ Autentikasi, CRUD laporan/klaim, master data admin (sesuai urutan di prd.md §13)

## Tech stack

- PHP 8.2+, Laravel 12
- Blade + Bootstrap 5.3.3 + Bootstrap Icons 1.11.3
- jQuery 3.7.1 (CDN)
- MySQL / SQLite (lokal)
- PHPUnit 11

## Persyaratan

- PHP `^8.2`
- Composer
- Node.js & npm (opsional, hanya untuk Vite; aplikasi memakai CSS statis)
- Database MySQL atau SQLite

## Instalasi

```bash
# 1. Clone
git clone https://github.com/Sahidannopal-15/Lost-N-Found.git
cd Lost-N-Found

# 2. Dependensi
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate
```

Sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lostnfound
DB_USERNAME=root
DB_PASSWORD=
```

Untuk SQLite (lokal cepat), cukup set `DB_CONNECTION=sqlite` dan biarkan `DB_DATABASE` kosong (default `database/database.sqlite`).

```bash
# 4. Migrasi (dan seed bila tersedia)
php artisan migrate --seed

# 5. Storage link (untuk foto)
php artisan storage:link

# 6. Jalankan
php artisan serve
```

Buka http://127.0.0.1:8000

## Struktur file khusus aplikasi

| Tanggung jawab | File |
|---|---|
| Model & relasi | `app/Models/User.php`, `Category.php`, `Location.php`, `Item.php` |
| Controller katalog | `app/Http/Controllers/ItemController.php` |
| Controller API | `app/Http/Controllers/Api/ItemController.php` |
| Paginasi Bootstrap | `app/Providers/AppServiceProvider.php` |
| Route halaman | `routes/web.php` |
| Route API | `routes/api.php` |
| Skema basis data | `database/migrations/2026_11_08_00000*_create_*_table.php` |
| Data demo | `database/seeders/DatabaseSeeder.php` |
| Halaman Blade | `resources/views/items/index.blade.php`, `show.blade.php` |
| Partials | `resources/views/partials/tiket.blade.php`, `status.blade.php`, `kosong.blade.php` |
| Layout | `resources/views/layouts/app.blade.php` |
| CSS aplikasi | `public/css/app.css` |
| Pengujian | `tests/Feature/CatalogApiTest.php` |

## Endpoint API

| Method | Path | Keterangan |
|---|---|---|
| GET | `/api/items` | Daftar barang terpaginasi (9/halaman), mendukung `q`, `type`, `category_id`, `location_id`, `status`, `date_from`, `page` |
| GET | `/api/items/{id}` | Detail satu barang |

Field JSON per barang: `id`, `code`, `type`, `title`, `description`, `category`, `location`, `event_date`, `photo_url`, `status`, `created_at`.

## Pengujian

```bash
php artisan test
```

Tes katalog (filter AJAX, server-side filter, detail) berada di `tests/Feature/CatalogApiTest.php`.

## Konvensi desain

Mengikuti prd.md §11: tanpa gradient, glassmorphism, bayangan, atau gerak otomatis; tanpa emoji sebagai ikon (Bootstrap Icons saja); tanpa label KAPITAL; tanpa `btn-primary` biru bawaan. Status dibedakan lewat ikon, bentuk garis, dan teks, bukan warna saja.

## Lisensi

MIT — lihat [Laravel License](https://opensource.org/licenses/MIT).
