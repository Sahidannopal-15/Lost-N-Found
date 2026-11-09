# Findo

Aplikasi web untuk melaporkan, mencari, dan mengklaim barang hilang atau ditemukan di lingkungan kampus. Pengguna membuat laporan (foto, kategori, lokasi, tanggal), katalog menampilkan semua laporan dalam kartu yang dapat dicari dan difilter, lalu pemilik barang mengajukan klaim yang akan diverifikasi oleh petugas sebelum barang diserahkan.

Panduan pengembangan lengkap ada di [prd.md](prd.md).

## Status implementasi

| Qtd | Komponen | Status |
| --- | --- | --- |
| ✅ | Setup Laravel 12 + koneksi SQLite/MySQL | Complete |
| ✅ | Migration tabel: `users`, `categories`, `locations`, `items`, `claims` | Complete |
| ✅ | Model: `User`, `Category`, `Location`, `Item`, `Claim` dengan relasi dan accessor `code` | Complete |
| ✅ | Halaman katalog beranda (`GET /`) dengan kartu tiket | Complete |
| ✅ | Halaman detail barang (`GET /items/{item}`) | Complete |
| ✅ | REST API read-only: `GET /api/items`, `GET /api/items/{id}` | Complete |
| ✅ | Filter katalog via AJAX (jQuery) tanpa reload halaman | Complete |
| ✅ | Paginasi Bootstrap 5 (`Paginator::useBootstrapFive()`) | Complete |
| ✅ | Tes fitur katalog (`tests/Feature/CatalogApiTest.php`) | Complete |
| ✅ | Autentikasi, CRUD laporan/klaim, master data admin | Complete |
| ✅ | Dashboard admin dan kelola pengguna (opsional) | Complete |

## Tech stack

- PHP `^8.2`
- Laravel `^12.0`
- Blade + Bootstrap 5.3.3 + Bootstrap Icons 1.11.3
- jQuery 3.7.1 (CDN)
- MySQL atau SQLite (lokal)
- PHPUnit 11

## Persyaratan

- PHP `^8.2`
- Composer
- Node.js & npm (wajib sesuai langkah setup; digunakan untuk mengompilasi aset Vite)
- Database MySQL atau SQLite

## Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/Sahidannopal-15/Lost-N-Found.git
cd Lost-N-Found

# 2. Dependensi PHP
composer install

# 3. Environment
cp .env.example .env
php artisan key:generate
```

Sesuaikan file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lostnfound
DB_USERNAME=root
DB_PASSWORD=
```

Untuk SQLite (lokal cepat), set `DB_CONNECTION=sqlite` dan biarkan `DB_DATABASE` kosong (default `database/database.sqlite`).

```bash
# 4. Migrasi + seed
php artisan migrate --seed

# 5. Storage link (untuk foto)
php artisan storage:link

# 6. Jalankan server
php artisan serve
```

Buka http://127.0.0.1:8000.

## Struktur file

| Komponen | Lokasi |
| --- | --- |
| Model & relasi | `app/Models/User.php`, `Category.php`, `Location.php`, `Item.php`, `Claim.php` |
| Controller halaman | `app/Http/Controllers/ItemController.php`, `ClaimController.php`, `RiwayatController.php`, `AuthController.php` |
| Controller admin | `app/Http/Controllers/Admin/CategoryController.php`, `LocationController.php` |
| Controller layanan | `app/Http/Controllers/Api/ItemController.php`, `Petugas/ClaimController.php` |
| Route halaman | `routes/web.php` |
| Route API | `routes/api.php` |
| Skema basis data | `database/migrations/` |
| Data demo | `database/seeders/DatabaseSeeder.php` |
| Layout | `resources/views/layouts/app.blade.php` |
| Halaman katalog | `resources/views/items/index.blade.php` |
| Halaman detail | `resources/views/items/show.blade.php` |
| Form lapor | `resources/views/items/_form.blade.php`, `create.blade.php`, `edit.blade.php` |
| Halaman klaim | `resources/views/claims/create.blade.php`, `petugas/claims/index.blade.php` |
| Halaman riwayat | `resources/views/riwayat/index.blade.php` |
| Manajemen admin | `resources/views/admin/categories/*`, `admin/locations/*` |
| Partials | `resources/views/partials/tiket.blade.php`, `status.blade.php`, `kosong.blade.php` |
| CSS | `public/css/app.css` |
| Pengujian | `tests/Feature/CatalogApiTest.php` |

## Endpoint API

| Method | Path | Keterangan |
| --- | --- | --- |
| GET | `/api/items` | Daftar barang terpaginasi (9/halaman), mendukung `q`, `type`, `category_id`, `location_id`, `status`, `date_from`, `page` |
| GET | `/api/items/{id}` | Detail satu barang |

Contoh field JSON per barang:

`id`, `code`, `type`, `title`, `description`, `category`, `location`, `event_date`, `photo_url`, `status`, `created_at`.

## Pengujian

```bash
php artisan test
```

Tes katalog (filter AJAX, filter server-side, detail) berada di `tests/Feature/CatalogApiTest.php`.

## Konvensi desain

Mengikuti prd.md §11:

- Tidak menggunakan gradient, glassmorphism, bayangan, atau gerak otomatis.
- Ikon menggunakan Bootstrap Icons saja, tanpa emoji.
- Tidak menggunakan label KAPITAL.
- Tidak menggunakan `btn-primary` biru bawaan.
- Status dibedakan lewat kombinasi ikon, bentuk garis, dan teks, bukan warna saja.

## Lisensi

MIT — lihat [Laravel License](https://opensource.org/licenses/MIT).
