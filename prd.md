# PRD: Lost n Found Kampus (panduan developer)

## 0. Cara memakai dokumen ini

Dokumen ini adalah satu-satunya sumber kebenaran untuk pengembangan. Kalau dipakai bersama AI coding tool:

- Kerjakan **hanya** fitur di bagian 4. Jangan menambah fitur, library, atau halaman di luar dokumen ini.
- Kerjakan per langkah kecil (lihat bagian 13), uji tiap langkah sebelum lanjut.
- Pakai nama tabel, kolom, status, dan route persis seperti tertulis.
- Jangan memakai Breeze/Jetstream (Tailwind). Auth ditulis manual.
- Tampilan memakai file desain di bagian 11. Jangan mengubah warna, font, atau nama kelas komponennya. Halaman baru memakai komponen yang sudah ada.

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
| **Admin** | Seeder | CRUD kategori dan lokasi, hapus laporan bermasalah, lihat semua laporan dan klaim (hanya baca), dashboard (opsional) |

- Register selalu menghasilkan role `pengguna`.
- Pengguna tidak boleh mengklaim barang yang ia laporkan sendiri.
- Petugas dan Admin tidak mengajukan klaim.

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

**Opsional (hanya jika waktu longgar):**

- O1: Dashboard admin, 4 angka (total laporan, total klaim, barang selesai, total pengguna), hanya `count()`.
- O2: Kelola/nonaktifkan pengguna.

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

## 6. Model data (5 tabel)

**users**: `id`, `name`, `email` (unik), `password` (hash bcrypt), `role` enum(`admin`,`petugas`,`pengguna`) default `pengguna`, `timestamps`

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
- **Tanpa Vite/npm:** CSS ada di `public/css/app.css`. Bootstrap 5.3.3, Bootstrap Icons 1.11.3, jQuery 3.7.1, dan Google Fonts dimuat dari CDN (butuh internet saat demo).
- **Paginasi:** di `AppServiceProvider::boot()` tambahkan `Paginator::useBootstrapFive();` (`use Illuminate\Pagination\Paginator;`). Tanpa ini paginasi Laravel memakai gaya Tailwind.
- **Foto:** jalankan `php artisan storage:link`.

## 8. Halaman dan route

**Halaman:** (1) beranda/katalog, (2) detail barang, (3) login, (4) register, (5) form lapor barang, (6) riwayat saya, (7) form klaim, (8) dashboard admin (opsional), (9) admin kategori dan lokasi, (10) petugas daftar klaim + verifikasi. Ditambah form edit laporan.

| Akses | Method dan path | Fungsi |
|---|---|---|
| Guest | `GET /` | Katalog |
| Guest | `GET /items/{item}` | Detail |
| Guest | `GET/POST /register`, `GET/POST /login` | Register dan login |
| Login | `POST /logout` | Logout |
| Pengguna, Petugas | `GET /items/create`, `POST /items` | Buat laporan |
| Pemilik laporan | `GET /items/{item}/edit`, `PUT /items/{item}`, `DELETE /items/{item}` | Ubah dan hapus |
| Pemilik laporan hilang | `POST /items/{item}/selesai` | Tandai sudah ketemu |
| Pengguna | `GET /riwayat` | Riwayat saya |
| Pengguna | `GET /items/{item}/claims/create`, `POST /items/{item}/claims` | Ajukan klaim |
| Petugas | `GET /petugas/claims` | Daftar klaim |
| Petugas | `POST /petugas/claims/{claim}/approve`, `POST /petugas/claims/{claim}/reject` | Putuskan klaim |
| Admin | `resource /admin/categories`, `resource /admin/locations` | CRUD master data |
| Admin | `GET /admin/dashboard` | Dashboard (opsional) |

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

## 11. Desain UI

Tampilan sudah dirancang dan disediakan sebagai file. **Pakai persis, jangan diubah.** Gagasan desainnya: tiap laporan tampil sebagai **tiket barang** (kartu dengan garis sobekan, jenis laporan, kode, dan status). Itu satu-satunya elemen yang mencolok; sisanya sengaja tenang.

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
| Merah tekan (tombol, tautan, label "Ditemukan") | `#C8101A` |
| Tinta (teks utama, label "Hilang") | `#26272A` |
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
- Halaman baru memakai komponen yang sudah ada. Jangan menambah warna, font, atau gaya baru.

**Teks antarmuka (bahasa Indonesia, nama aksi sama di seluruh alur):**

- Tombol: Cari, Lapor barang, Simpan laporan, Simpan perubahan, Hapus laporan, Tandai sudah ketemu, Ajukan klaim, Kirim klaim, Setujui dan serahkan, Tolak klaim, Masuk, Buat akun, Keluar, Tambah kategori, Hapus filter.
- Error (spesifik, tidak minta maaf): "Email belum diisi.", "Format email tidak valid, contoh: nama@email.com.", "Password minimal 8 karakter.", "Konfirmasi password tidak sama.", "Email atau password salah. Periksa lagi, lalu coba masuk.", "Judul barang belum diisi.", "Tanggal tidak boleh lebih dari hari ini.", "Foto harus berformat JPG atau PNG, maksimal 2 MB.", "Ciri barang minimal 20 karakter. Sebutkan hal yang hanya diketahui pemilik, misalnya isi tas atau goresan.", "Kategori ini masih dipakai 5 laporan, jadi belum bisa dihapus. Pindahkan laporannya ke kategori lain dulu."
- Sukses: "Laporan disimpan.", "Klaim dikirim. Petugas akan memeriksa buktimu.", "Klaim disetujui. Serah terima tercatat."
- Kosong: katalog tanpa hasil: "Belum ada barang yang cocok. Ubah kata kunci atau hapus filter. Barangmu belum dilaporkan? Buat laporan baru." Katalog kosong total: "Belum ada laporan. Laporkan barang hilang atau temuan pertama." Riwayat: "Kamu belum punya laporan atau klaim. Kehilangan atau menemukan sesuatu? Buat laporan pertamamu." Klaim masuk (petugas): "Tidak ada klaim yang menunggu."

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
9. Opsional: O1 dashboard, O2 kelola pengguna.

## 14. Definition of Done

- Proyek berjalan di laptop anggota mana pun dengan `php artisan migrate --seed` lalu `php artisan serve`.
- F1–F9 bisa didemokan memakai tiga akun seeder.
- CRUD kategori, lokasi, dan laporan tersambung ke MySQL.
- Seluruh halaman di bagian 8 bisa diakses dengan data seeder.
- Semua halaman memakai layout dan partials dari bagian 11, tanpa warna biru Bootstrap bawaan, tanpa emoji sebagai ikon, dan tanpa teks placeholder.
