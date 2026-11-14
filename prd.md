# PRD: Lost n Found Kampus (panduan developer)

## 0. Cara memakai dokumen ini

Dokumen ini adalah satu-satunya sumber kebenaran untuk pengembangan. Kalau dipakai bersama AI coding tool:

- Kerjakan **hanya** fitur di bagian 4. Jangan menambah fitur, library, atau halaman di luar dokumen ini.
- Kerjakan per langkah kecil (lihat bagian 13), uji tiap langkah sebelum lanjut.
- Pakai nama tabel, kolom, status, dan route persis seperti tertulis.
- Jangan memakai Breeze/Jetstream (Tailwind). Auth ditulis manual.
- Tampilan memakai file desain di bagian 11. Jangan mengubah warna, font, atau nama kelas komponennya. Aturan responsif dan aksesibilitas ada di bagian 11.1, komposisi warna 60-30-10 di bagian 11.2. Halaman baru memakai komponen yang sudah ada.

## 1. Ringkasan

Aplikasi web untuk melaporkan, mencari, dan mengklaim barang hilang atau ditemukan di kampus. Pengguna membuat laporan (foto, kategori, lokasi, tanggal), semua laporan tampil di katalog yang bisa dicari dan difilter, dan pemilik barang mengajukan klaim yang diverifikasi petugas sebelum barang diserahkan.

## 2. Batasan teknis

- Gratis: seluruh teknologi open source.
- Berjalan lokal (`php artisan serve`). Tidak ada deploy/hosting.
- Stack: HTML, CSS, Bootstrap 5, JavaScript + jQuery, PHP, Laravel, MySQL, REST API.
- Tidak ada minimal jumlah CRUD, tapi semua CRUD yang ada harus tersambung ke database.

## 3. Role dan hak akses

| Role | Cara mendapatkan | Hak akses |
|---|---|---|
| **Guest** | Belum login | Lihat katalog dan detail, register, login |
| **Pengguna** | Register sendiri | Hak Guest + buat laporan hilang/temuan, ubah/hapus laporan miliknya, tandai laporan hilang miliknya selesai, ajukan klaim, lihat riwayat |
| **Petugas** | Seeder | Hak Pengguna (kecuali mengklaim) + lihat daftar klaim, setujui/tolak klaim dengan catatan (setuju = serah terima), catat laporan temuan |
| **Admin** | Seeder | CRUD kategori dan lokasi, hapus laporan bermasalah (termasuk yang `dalam_klaim`), lihat semua laporan dan klaim (hanya baca), dashboard, kelola/nonaktifkan pengguna |

- Register selalu menghasilkan role `pengguna`.
- Pengguna tidak boleh mengklaim barang yang ia laporkan sendiri.
- Petugas dan Admin tidak mengajukan klaim.
- Admin tetap bisa menghapus laporan bermasalah, termasuk yang berstatus `dalam_klaim`.
- Hanya role pengguna yang boleh mengajukan klaim.
- Akun yang dinonaktifkan tidak bisa login, dan sesi yang sedang berjalan (termasuk cookie "ingat saya") berakhir pada request berikutnya.
- Admin tidak bisa menonaktifkan dirinya sendiri dan tidak bisa menonaktifkan admin aktif yang terakhir.

## 4. Fitur

**Wajib:**

| ID | Fitur | Kriteria selesai |
|---|---|---|
| F1 | Autentikasi + middleware role | Register membuat Pengguna; login/logout berfungsi; halaman Admin/Petugas menolak role yang tidak berhak (403 atau redirect) |
| F2 | CRUD kategori dan lokasi (Admin) | Tambah, lihat, ubah, hapus; nama unik; data yang masih dipakai laporan tidak bisa dihapus dan menampilkan pesan jelas |
| F3 | CRUD laporan barang (hilang/temuan) + upload 1 foto | Pengguna bisa buat, lihat, ubah, hapus laporan miliknya; foto opsional; laporan orang lain tidak bisa diubah |
| F4 | Katalog, detail, cari dan filter tanpa reload (beranda = katalog) | Kartu grid; filter mengubah hasil lewat AJAX tanpa reload; detail menampilkan foto, lokasi, tanggal, status |
| F5 | REST API read-only (2 endpoint) | `GET /api/items` dan `GET /api/items/{id}` mengembalikan JSON; katalog memakai endpoint ini |
| F6 | Pengajuan klaim | Pengguna login bisa mengklaim barang temuan berstatus aktif dengan ciri dan bukti; barang menjadi `dalam_klaim` |
| F7 | Verifikasi klaim + serah terima (Petugas) | Daftar klaim; setuju → barang `selesai` + waktu serah terima tercatat; tolak → barang kembali `aktif` |
| F8 | Riwayat saya | Daftar laporan dan klaim milik pengguna beserta status terbaru dan catatan petugas |
| F9 | Validasi form sisi klien (jQuery) | Form register, login, laporan, klaim menampilkan pesan kesalahan sebelum dikirim; validasi server tetap ada |

**Tambahan (sudah dibuat):**

- O1: Dashboard admin, 4 angka, hanya `count()`. Definisi: total laporan = semua laporan semua status; total klaim = semua klaim semua status; barang selesai = laporan berstatus `selesai`; total pengguna = semua akun termasuk yang nonaktif.
- O2: Kelola/nonaktifkan pengguna: daftar pengguna (15 per halaman) dengan tombol Aktifkan/Nonaktifkan; menonaktifkan meminta konfirmasi.

## 5. Alur, status, dan aturan bisnis

**Alur utama:**

1. Pengguna login → buat laporan → langsung tampil di katalog.
2. Siapa saja mencari dan memfilter → buka detail.
3. Pemilik (login) menekan Klaim pada barang temuan → isi ciri dan bukti → barang `dalam_klaim`.
4. Petugas memeriksa bukti dan menemui pemilik di pos → setuju (barang `selesai`, waktu serah terima tercatat) atau tolak dengan catatan (barang kembali `aktif`).
5. Pengguna melihat hasil di Riwayat saya.
6. Laporan kehilangan: pemilik menandai sendiri `selesai` saat barangnya ketemu.

**Status barang (`items.status`):**

| Dari | Ke | Pemicu |
|---|---|---|
| (baru) | `aktif` | Laporan dibuat |
| `aktif` | `dalam_klaim` | Klaim diajukan (hanya barang temuan) |
| `dalam_klaim` | `selesai` | Petugas menyetujui klaim |
| `dalam_klaim` | `aktif` | Petugas menolak klaim |
| `aktif` | `selesai` | Pemilik menandai laporan hilangnya selesai |

**Status klaim (`claims.status`):** `menunggu` → `disetujui` atau `ditolak`.

**Aturan bisnis:**

- Hanya barang **temuan** berstatus **aktif** yang bisa diklaim.
- Satu klaim `menunggu` per barang pada satu waktu.
- Laporan `dalam_klaim` tidak bisa diubah atau dihapus pemiliknya (Admin tetap bisa menghapus).
- Katalog default menampilkan `aktif` dan `dalam_klaim`; `selesai` muncul bila difilter.
- Persetujuan dilakukan saat pemilik hadir di pos, jadi persetujuan = serah terima.
- Klaim hanya bisa disetujui atau ditolak saat berstatus `menunggu`.
- Admin boleh menghapus laporan apa pun, termasuk yang berstatus `dalam_klaim`.

## 6. Model data (5 tabel)

**users**: `id`, `name`, `email` (unik), `password` (hash bcrypt), `role` enum(`admin`,`petugas`,`pengguna`) default `pengguna`, `is_active` boolean default `true`, `timestamps`

**categories**: `id`, `name` (unik), `timestamps`

**locations**: `id`, `name` (unik), `timestamps`

**items**: `id`, `user_id` FK→users (pelapor), `type` enum(`hilang`,`temuan`), `title`, `description` text, `category_id` FK→categories (restrict), `location_id` FK→locations (restrict), `event_date` date (tidak boleh lebih dari hari ini), `photo_path` nullable, `status` enum(`aktif`,`dalam_klaim`,`selesai`) default `aktif`, `timestamps`. Kode tampil `LNF-` + id 4 digit (contoh `LNF-0007`), dibuat dari id (accessor), bukan kolom.

**claims**: `id`, `item_id` FK→items, `user_id` FK→users (pengklaim), `description` text (ciri menurut pengklaim), `proof` text (bukti berupa teks, tanpa foto), `status` enum(`menunggu`,`disetujui`,`ditolak`) default `menunggu`, `officer_note` nullable, `handled_by` FK→users nullable, `handled_at` datetime nullable, `timestamps`

Relasi: users 1–N items; users 1–N claims; categories 1–N items; locations 1–N items; items 1–N claims.

**Syarat model (dipakai oleh file desain di bagian 11):**

- `Item`: relasi `user()`, `location()`, `category()`; cast `protected $casts = ['event_date' => 'date'];`; accessor `code` yang menghasilkan `LNF-0007`. Saat menampilkan daftar, muat relasi dengan `Item::with('location', 'category')`.
- `Claim`: relasi `item()`, `user()` (pengklaim), `handler()` (petugas, lewat `handled_by`).


**Seeder:**

- Akun: `admin@lostnfound.test`, `petugas@lostnfound.test`, `pengguna@lostnfound.test` (satu password demo, khusus lokal).
- Kategori contoh: KTM/Kartu, Elektronik, Dokumen, Tas dan Dompet, Kunci, Pakaian dan Aksesori, Alat Tulis, Lainnya.
- Lokasi contoh: Perpustakaan, Kantin, Masjid, Parkiran, Lapangan Olahraga, Gedung Kuliah, Laboratorium, Pos Satpam (boleh diganti nama gedung kampus sebenarnya).
- Sekitar 12 laporan realistis (KTM, tumbler, charger, kunci motor, payung, dll.) dengan campuran jenis dan status, supaya katalog, filter, dan riwayat tidak kosong.

## 7. Tech stack

| Komponen | Keputusan |
|---|---|
| HTML5, CSS3 | Lewat Blade |
| Bootstrap 5 | Dipakai, variabelnya dioverride di satu `app.css` |
| JavaScript + jQuery | Filter AJAX (F4), validasi form (F9) |
| PHP + Laravel | Blade, Eloquent, migration, seeder, middleware, validation, storage. Samakan versi PHP dan Laravel di seluruh anggota (lihat catatan di bawah tabel) |
| MySQL | Database |
| Autentikasi | Manual (`Auth::attempt`), bukan Breeze/Jetstream |
| Leaflet, hosting | Tidak dipakai |

**Catatan versi dan pengaturan:**

- **Versi:** Laravel 13 membutuhkan PHP 8.3 atau lebih baru; Laravel 12 mendukung PHP 8.2 sampai 8.5. Semua anggota cek `php -v`. Kalau ada yang di bawah 8.3, buat proyek dengan `composer create-project laravel/laravel:^12.0 lostnfound`. Satu versi untuk semua anggota; `composer.lock` di-commit.
- **Kredensial:** `.env` tidak di-commit. `.env.example` di-commit dengan `DB_CONNECTION=mysql`, `DB_DATABASE=lostnfound`, `DB_USERNAME=root`, dan `DB_PASSWORD=` **dikosongkan**.
- **Tanpa build aset (Vite tidak dipakai):** file `package.json` bawaan Laravel boleh tetap ada. CSS ada di `public/css/app.css`. Bootstrap 5.3.3, Bootstrap Icons 1.11.3, jQuery 3.7.1, dan Google Fonts dimuat dari CDN (butuh internet saat demo).
- **Paginasi:** di `AppServiceProvider::boot()` tambahkan `Paginator::useBootstrapFive();` (`use Illuminate\Pagination\Paginator;`). Tanpa ini paginasi Laravel memakai gaya Tailwind.
- **Foto:** jalankan `php artisan storage:link`.

### Peta file aplikasi

Peta ini mencakup file khusus aplikasi yang dibuat atau diubah untuk fitur F1–F9 dan O1–O2, bukan seluruh file bawaan Laravel. Gunakan lokasi dan nama berikut agar anggota tim memakai struktur yang konsisten.

| Tanggung jawab | File atau folder |
|---|---|
| Model dan relasi | `app/Models/User.php`, `app/Models/Category.php`, `app/Models/Location.php`, `app/Models/Item.php`, `app/Models/Claim.php` |
| Autentikasi | `app/Http/Controllers/AuthController.php` — register, login, logout |
| Laporan barang | `app/Http/Controllers/ItemController.php` — katalog/detail, CRUD laporan, dan tandai laporan hilang selesai |
| Pengajuan klaim dan riwayat | `app/Http/Controllers/ClaimController.php` — pengajuan klaim; `app/Http/Controllers/RiwayatController.php` — riwayat pengguna |
| Verifikasi klaim petugas | `app/Http/Controllers/Petugas/ClaimController.php` — daftar, setujui, dan tolak klaim |
| CRUD master data admin | `app/Http/Controllers/Admin/CategoryController.php` dan `app/Http/Controllers/Admin/LocationController.php` |
| Dashboard dan kelola pengguna (admin) | `app/Http/Controllers/Admin/DashboardController.php`; `app/Http/Controllers/Admin/UserController.php` |
| Endpoint JSON barang | `app/Http/Controllers/Api/ItemController.php` — daftar dan detail barang, hanya baca |
| Pembatasan role dan akun nonaktif | `app/Http/Middleware/RoleMiddleware.php`; `app/Http/Middleware/EnsureUserIsActive.php` (alias `aktif`, memutus sesi akun nonaktif) |
| Paginasi Bootstrap | `app/Providers/AppServiceProvider.php` — aktifkan `Paginator::useBootstrapFive()` |
| Route halaman dan form | `routes/web.php` |
| Skema basis data | `database/migrations/` — migration untuk `users`, `categories`, `locations`, `items`, dan `claims` |
| Data demo | `database/seeders/DatabaseSeeder.php` |
| Halaman Blade | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`; `resources/views/items/index.blade.php`, `resources/views/items/show.blade.php`, `resources/views/items/create.blade.php`, `resources/views/items/edit.blade.php`; `resources/views/claims/create.blade.php`; `resources/views/riwayat/index.blade.php`; `resources/views/petugas/claims/index.blade.php`; `resources/views/admin/categories/index.blade.php`, `resources/views/admin/categories/create.blade.php`, `resources/views/admin/categories/edit.blade.php`; `resources/views/admin/locations/index.blade.php`, `resources/views/admin/locations/create.blade.php`, `resources/views/admin/locations/edit.blade.php` |
| Halaman Blade admin tambahan | `resources/views/admin/dashboard.blade.php`; `resources/views/admin/users/index.blade.php` |
| Halaman galat | `resources/views/errors/403.blade.php`, `resources/views/errors/404.blade.php` (memakai layout, lihat 11.1 pola h) |
| Layout dan komponen desain | `resources/views/layouts/app.blade.php`; `resources/views/partials/tiket.blade.php`, `status.blade.php`, `kosong.blade.php` (lihat bagian 11) |
| Validasi klien (F9) | `public/js/validasi.js`; dimuat lewat `@push('skrip')` di view register, login, form laporan, dan form klaim |
| CSS aplikasi | `public/css/app.css` — lokasi kanonis sesuai bagian 11; jangan menyimpan salinan kedua di `public/app.css` |
| Skrip halaman | `@push('skrip')` pada view terkait, sesuai bagian 11; gunakan jQuery yang dimuat layout, tanpa Vite/npm |
| Pengujian | `tests/Feature/` untuk alur dan akses fitur; `tests/Unit/` untuk logika unit bila diperlukan |

Nama controller dan path view di atas menjadi ketentuan struktur proyek. Jika belum tersedia, buat file dengan nama dan lokasi tersebut. Untuk layout dan partial desain bersama, gunakan path persis yang tercantum di bagian 11.

## 8. Halaman dan route

**Halaman:** (1) beranda/katalog, (2) detail barang, (3) login, (4) register, (5) form lapor barang, (6) riwayat saya, (7) form klaim, (8) dashboard admin, (9) admin kategori dan lokasi, (10) petugas daftar klaim + verifikasi, (11) admin kelola pengguna. Ditambah form edit laporan.

| Akses | Method dan path | Fungsi |
|---|---|---|
| Guest | `GET /` | Katalog |
| Guest | `GET /items/{item}` | Detail |
| Guest | `GET/POST /register`, `GET/POST /login` | Register dan login |
| Login | `POST /logout` | Logout |
| Pengguna, Petugas | `GET /items/create`, `POST /items` | Buat laporan |
| Pemilik laporan | `GET /items/{item}/edit`, `PUT /items/{item}` | Ubah |
| Pemilik laporan, Admin | `DELETE /items/{item}` | Hapus (pemilik: tidak bisa jika `dalam_klaim`; admin: selalu bisa) |
| Pemilik laporan hilang | `POST /items/{item}/selesai` | Tandai sudah ketemu |
| Pengguna | `GET /riwayat` | Riwayat saya |
| Pengguna | `GET /items/{item}/claims/create`, `POST /items/{item}/claims` | Ajukan klaim |
| Petugas | `POST /petugas/claims/{claim}/approve`, `POST /petugas/claims/{claim}/reject` | Putuskan klaim |
| Admin | `resource /admin/categories`, `resource /admin/locations` | CRUD master data |
| Admin | `GET /admin/dashboard` | Dashboard |
| Admin | `GET /admin/users`, `POST /admin/users/{user}/toggle` | Kelola/nonaktifkan pengguna |
| Petugas, Admin | `GET /petugas/claims` | Daftar klaim (admin hanya baca) |

## 9. REST API

Publik, hanya baca, tanpa token.

| Endpoint | Parameter | Respons |
|---|---|---|
| `GET /api/items` | `q` (kata kunci judul/deskripsi), `type`, `category_id`, `location_id`, `status`, `date_from`, `page` | Daftar terpaginasi, 9 per halaman |
| `GET /api/items/{id}` | - | Detail satu barang |

Field JSON per barang: `id`, `code`, `type`, `title`, `description`, `category`, `location`, `event_date`, `photo_url`, `status`, `created_at`. Data pengguna (email, password, role) **tidak** dikirim. Katalog memanggil `GET /api/items` lewat jQuery AJAX saat filter berubah.

## 10. Validasi dan keamanan

**Validasi** (klien jQuery dan server Laravel):

- Register: nama wajib, email valid dan unik, password minimal 8 karakter, konfirmasi cocok.
- Login: email dan password wajib.
- Laporan: judul, jenis, kategori, lokasi, tanggal wajib; tanggal tidak boleh melebihi hari ini; foto opsional jpg/jpeg/png maksimal 2 MB.
- Klaim: ciri minimal 20 karakter, bukti minimal 10 karakter.

**Keamanan dasar:**

- Password di-hash; CSRF aktif di semua form.
- Otorisasi di server: pengguna hanya bisa ubah/hapus laporan miliknya; route Admin dan Petugas dilindungi middleware role.
- Upload foto divalidasi tipe dan ukuran; disimpan di `storage/app/public` (jalankan `php artisan storage:link`).
- API tidak membocorkan data pengguna.
- Status `is_active` diperiksa pada setiap request lewat middleware `aktif`, bukan hanya saat login.
- Tes otomatis tidak boleh memakai database development; gunakan SQLite in-memory.

## 11. Desain UI

Tampilan sudah dirancang dan disediakan sebagai file. **Pakai persis, jangan diubah**, kecuali perubahan responsif (bagian 11.1) dan pembagian peran warna 60-30-10 (bagian 11.2). Gagasan desainnya: tiap laporan tampil sebagai **tiket barang** (kartu dengan garis sobekan, jenis laporan, kode, dan status). Itu satu-satunya elemen yang mencolok; sisanya sengaja tenang.

**Letak file di proyek Laravel:**

| File | Letak |
|---|---|
| `app.css` | `public/css/app.css` |
| Layout | `resources/views/layouts/app.blade.php` |
| Kartu tiket barang | `resources/views/partials/tiket.blade.php` |
| Badge status | `resources/views/partials/status.blade.php` |
| Keadaan kosong | `resources/views/partials/kosong.blade.php` |

**Cara memakai:**

- Setiap halaman: `@extends('layouts.app')`, judul lewat `@section('judul', '...')`, isi di `@section('isi')`. Skrip halaman lewat `@push('skrip')`.
- Daftar barang: `<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">` dan tiap barang `<div class="col">@include('partials.tiket', ['item' => $item])</div>`. Hasil AJAX di jQuery meniru struktur HTML `partials/tiket` persis.
- Status barang: `@include('partials.status', ['status' => $item->status])`.
- Daftar kosong: `@include('partials.kosong', [...])` (contoh parameter ada di komentar file).
- Tombol: `btn-primary` untuk aksi utama, `btn-outline-secondary` untuk Batal/Kembali/Tolak, `btn-outline-danger` untuk Hapus.
- Tabel admin dan petugas: `<div class="panel table-responsive"><table class="table tabel-admin">`.
- Pesan flash memakai kunci sesi `sukses` dan `galat`. Pesan validasi Laravel ditampilkan di bawah tiap kolom dengan `is-invalid` dan `invalid-feedback`.

**Token desain (ringkas):**

| Peran | Nilai |
|---|---|
| Merah Telkom (aksen, garis atas) | `#EC1C24` |
| Merah tekan (tombol utama, label "Ditemukan", penanda menu aktif) | `#C8101A` |
| Tinta (teks utama, navbar, footer, pita pencarian, label "Hilang") | `#26272A` |
| Tinta pudar (teks sekunder) | `#5B5D61` |
| Kertas (latar halaman) | `#EEF0F2` |
| Garis (border, garis sobekan) | `#D3D5D9` |
| Font judul / isi | Archivo / Plus Jakarta Sans |
| Skala teks | 14 / 16 / 20 / 28 / 40 px |
| Radius lencana / kontrol / tiket | 2 / 4 / 8 px |

**Aturan:**

- Tanpa gradient, glassmorphism, bayangan, atau gerak otomatis.
- Tanpa emoji sebagai ikon (Bootstrap Icons saja), tanpa label KAPITAL, tanpa `btn-primary` biru bawaan.
- Status dibedakan lewat ikon, bentuk garis, dan teks, bukan warna saja.
- Data seeder realistis, tanpa "Lorem ipsum" atau nama generik.
- Halaman baru memakai komponen yang sudah ada. Jangan menambah warna, font, atau gaya baru. Satu-satunya pengecualian: kelas kecil untuk pola responsif di bagian 11.1, yang memakai token yang sudah ada.

**Teks antarmuka (bahasa Indonesia, nama aksi sama di seluruh alur):**

- Tombol: Cari, Lapor barang, Simpan laporan, Simpan perubahan, Hapus laporan, Tandai sudah ketemu, Ajukan klaim, Kirim klaim, Setujui dan serahkan, Tolak klaim, Masuk, Buat akun, Keluar, Tambah kategori, Hapus filter.
- Error (spesifik, tidak minta maaf): "Email belum diisi.", "Format email tidak valid, contoh: nama@email.com.", "Password minimal 8 karakter.", "Konfirmasi password tidak sama.", "Email atau password salah. Periksa lagi, lalu coba masuk.", "Judul barang belum diisi.", "Tanggal tidak boleh lebih dari hari ini.", "Foto harus berformat JPG atau PNG, maksimal 2 MB.", "Ciri barang minimal 20 karakter. Sebutkan hal yang hanya diketahui pemilik, misalnya isi tas atau goresan.", "Kategori ini masih dipakai 5 laporan, jadi belum bisa dihapus. Pindahkan laporannya ke kategori lain dulu."
- Sukses: "Laporan disimpan.", "Klaim dikirim. Petugas akan memeriksa buktimu.", "Klaim disetujui. Serah terima tercatat."
- Kosong: katalog tanpa hasil: "Belum ada barang yang cocok. Ubah kata kunci atau hapus filter. Barangmu belum dilaporkan? Buat laporan baru." Katalog kosong total: "Belum ada laporan. Laporkan barang hilang atau temuan pertama." Riwayat: "Kamu belum punya laporan atau klaim. Kehilangan atau menemukan sesuatu? Buat laporan pertamamu." Klaim masuk (petugas): "Tidak ada klaim yang menunggu."

### 11.1 Responsif dan aksesibilitas

Berlaku untuk semua halaman di bagian 8. Mobile-first, breakpoint Bootstrap 576 / 768 / 992 / 1200 / 1400. Tampilan desktop (1280px ke atas) tidak berubah kecuali untuk memperbaiki pelanggaran yang terukur. Perubahan terutama di `public/css/app.css` dan sedikit markup Blade; route dan logika controller tidak berubah.

**Kriteria (lulus atau gagal), diuji pada lebar 320, 375, 414, 667 (landscape), 768, 1024, 1280, 1536, dan zoom 200% di 1280:**

1. Tidak ada gulir samping pada halaman (gulir di dalam `.table-responsive` boleh). Konten tetap terbaca di zoom 200%.
2. Semua elemen interaktif (tombol termasuk `.btn-sm`, tautan menu, kontrol filter, paginasi, ikon aksi) minimal 44 x 44 px dengan jarak antar-target minimal 8 px. Tautan di dalam kalimat dikecualikan.
3. Teks isi minimal 16px di HP, teks sekunder minimal 14px, dan semua input, select, textarea berukuran font minimal 16px.
4. Navbar di bawah 992px menjadi menu (hamburger): semua menu sesuai role terjangkau, menu aktif terlihat, tombol Keluar terjangkau, `aria-expanded` benar, bisa dioperasikan dengan keyboard, tombol hamburger 44 x 44 px.
5. Konten buatan pengguna yang panjang (judul, deskripsi, email, nama) tidak merusak tata letak (`overflow-wrap`).
6. Gambar tidak melonjak ukurannya saat dimuat (aspect-ratio atau dimensi tetap) dan tidak melebihi kontainer.
7. Kontras teks minimal 4,5:1 dan batas kontrol form minimal 3:1. Fokus keyboard terlihat di semua ukuran. Tidak ada fungsi yang hanya bisa lewat hover.
8. Di HP landscape (667 x 375) elemen yang menempel tidak menutup lebih dari sepertiga tinggi layar.
9. Tinggi penuh memakai `100dvh` dengan cadangan `100vh`.

**Pola yang ditetapkan:**

- **a. Katalog di HP (< 768px):** kolom pencarian selalu tampak; filter lain berada di panel buka-tutup bertombol "Filter" yang menampilkan jumlah filter aktif; tombol Cari dan Hapus filter selebar penuh. Boleh menambah JS kecil untuk menghitung filter aktif; request AJAX katalog tidak diubah.
- **b. Tiket katalog:** satu kolom di HP (foto di kiri), dua kolom di tablet, tiga kolom di desktop.
- **c. Detail barang:** ditumpuk di bawah 992px. Di bawah 768px aksi utama yang positif (misalnya Ajukan klaim) tampil sebagai bilah menempel di bawah layar, memakai `env(safe-area-inset-bottom)` dan padding bawah halaman agar isi tidak tertutup. Bilah dipisah dengan garis atas (tanpa bayangan). Jangan menduplikasi elemen: ubah posisi elemen yang sama dengan CSS. Aksi yang merusak (Hapus laporan) tetap di dalam sobekan tiket.
- **d. Tabel admin dan petugas di bawah 576px:** baris menjadi tumpukan berlabel lewat atribut `data-label` pada `<td>` dan CSS saja (struktur tabel tidak berubah). Label berupa kalimat biasa, bukan KAPITAL. Tombol aksi 44px dan tidak saling menempel.
- **e. Form:** satu kolom di HP dengan label di atas kolom; atribut yang tepat (`type="email"`, `autocomplete`, `inputmode`); tombol kirim selebar penuh di HP; pesan error tepat di bawah kolomnya dan tidak tertutup bilah menempel.
- **f. Paginasi:** boleh membungkus ke baris berikutnya; tiap tautan 44px.
- **g. Dashboard:** empat sel menjadi 2 x 2 di HP, tetap rata kiri.
- **h. Halaman galat 403 dan 404:** memakai layout aplikasi (bukan halaman bawaan Laravel).

**Catatan:** judul pada kartu katalog boleh dipotong dua baris karena isi lengkap ada di halaman detail. Di halaman detail dan di tabel, judul tidak dipotong. Bukti ukur dan screenshot (lebar 375, 768, 1280) disimpan di `docs/responsif/` dan boleh di-commit.

### 11.2 Komposisi warna 60-30-10

Tidak ada warna baru. Yang diatur hanya **pembagian peran** token yang sudah ada, supaya tampilan punya struktur dan aksen merah tetap bermakna.

| Porsi | Peran | Token | Dipakai untuk |
|---|---|---|---|
| 60% netral | Latar dan permukaan | Kertas, putih, sobekan, redup, garis | Latar halaman, panel, kartu tiket, form, tabel, garis pemisah |
| 30% sekunder | Struktur dan teks | Tinta, tinta pudar | Navbar, footer, pita pencarian katalog, teks, label "Hilang", garis bawah header tabel |
| 10% aksen | Perhatian dan aksi | Merah Telkom, merah tekan, merah gelap | Tombol utama, label "Ditemukan", tanda merek, penanda menu aktif, garis atas, pesan galat |

**Aturan:**

- **Area gelap (30%):** navbar (`data-bs-theme="dark"`), footer, dan pita pencarian di bagian atas katalog (selebar layar, rata kiri, berisi judul dan pencarian). Teks di area gelap berwarna putih (kontras minimal 4,5:1).
- **Merah bukan warna teks di atas area gelap** (kontras hanya sekitar 2,5:1). Penanda menu aktif di navbar gelap berupa garis bawah merah dengan teks putih.
- **Tautan di dalam teks** memakai tinta dengan garis bawah; merah hanya saat hover. Merah tidak dipakai sebagai warna tautan biasa.
- **Satu tombol utama merah per area pandang.** Tombol lain memakai gaya sekunder.
- **Ukuran komposisi:** ambil screenshot lebar 375 dan 1280 untuk katalog, detail barang, form lapor, tabel admin, dan login. Kelompokkan piksel ke token terdekat: netral = kertas, putih, sobekan, redup, garis; sekunder = tinta dan tinta pudar; aksen = tiga warna merah. Target: netral 55-70%, sekunder 20-35%, aksen 5-12%. Aksen tidak boleh melebihi 12% di halaman mana pun.
- Angka di atas adalah panduan, bukan hukum: teks dan foto ikut memengaruhi hitungan. Keputusan akhir tetap lewat penilaian mata.

## 12. Di luar scope

Integrasi sistem akademik; push/email/WhatsApp dan notifikasi dalam aplikasi (diganti Riwayat saya); pencocokan barang otomatis; peta (Leaflet/OSM); deploy; API untuk menulis data dan token API; foto bukti klaim; verifikasi laporan sebelum tampil; tombol "Hubungi Penemu".

## 13. Urutan dependensi build

Urutan ini murni soal ketergantungan antar fitur (bukan jadwal):

1. Setup Laravel, koneksi MySQL, migration 5 tabel, model dan relasi, seeder.
2. Pasang file desain dari bagian 11 (`app.css`, layout, partials), lalu uji dengan satu halaman katalog.
3. F1 autentikasi + middleware role (semua fitur lain bergantung padanya).
4. F2 CRUD kategori dan lokasi (dropdown laporan butuh datanya).
5. F3 CRUD laporan + upload foto.
6. F5 API + F4 katalog dan filter AJAX.
7. F6 klaim → F7 verifikasi dan serah terima → F8 riwayat.
8. F9 validasi jQuery di semua form.
9. O1 dashboard dan O2 kelola pengguna (sudah dibuat).

## 14. Definition of Done

- Proyek berjalan di laptop anggota mana pun dengan `php artisan migrate --seed` lalu `php artisan serve`.
- F1–F9 dan O1–O2 bisa didemokan memakai tiga akun seeder.
- `php artisan test` lulus semua.
- CRUD kategori, lokasi, dan laporan tersambung ke MySQL.
- Seluruh halaman di bagian 8 bisa diakses dengan data seeder.
- Semua halaman memakai layout dan partials dari bagian 11, tanpa warna biru Bootstrap bawaan, tanpa emoji sebagai ikon, dan tanpa teks placeholder.
- Kriteria responsif bagian 11.1 lulus pada lebar 320 sampai 1536px, dibuktikan dengan hasil ukur dan screenshot.
- Komposisi warna bagian 11.2 terpenuhi pada halaman yang diukur, dibuktikan dengan tabel persentase.
