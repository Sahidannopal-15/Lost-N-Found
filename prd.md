PRD: Lost n Found Kampus (panduan developer)
0. Cara memakai dokumen ini

Dokumen ini adalah satu-satunya sumber kebenaran untuk pengembangan. Kalau dipakai bersama AI coding tool:

Kerjakan hanya fitur di bagian 4. Jangan menambah fitur, library, atau halaman di luar dokumen ini.
Kerjakan per langkah kecil (lihat bagian 13), uji tiap langkah sebelum lanjut.
Pakai nama tabel, kolom, status, dan route persis seperti tertulis.
Jangan memakai Breeze/Jetstream (Tailwind). Auth ditulis manual.
1. Ringkasan

Aplikasi web untuk melaporkan, mencari, dan mengklaim barang hilang atau ditemukan di kampus. Pengguna membuat laporan (foto, kategori, lokasi, tanggal), semua laporan tampil di katalog yang bisa dicari dan difilter, dan pemilik barang mengajukan klaim yang diverifikasi petugas sebelum barang diserahkan.

2. Batasan teknis
Gratis: seluruh teknologi open source.
Berjalan lokal (php artisan serve). Tidak ada deploy/hosting.
Stack: HTML, CSS, Bootstrap 5, JavaScript + jQuery, PHP, Laravel, MySQL, REST API.
Tidak ada minimal jumlah CRUD, tapi semua CRUD yang ada harus tersambung ke database.
3. Role dan hak akses
Role	Cara mendapatkan	Hak akses
Guest	Belum login	Lihat katalog dan detail, register, login
Pengguna	Register sendiri	Hak Guest + buat laporan hilang/temuan, ubah/hapus laporan miliknya, tandai laporan hilang miliknya selesai, ajukan klaim, lihat riwayat
Petugas	Seeder	Hak Pengguna (kecuali mengklaim) + lihat daftar klaim, setujui/tolak klaim dengan catatan (setuju = serah terima), catat laporan temuan
Admin	Seeder	CRUD kategori dan lokasi, hapus laporan bermasalah, lihat semua laporan dan klaim (hanya baca), dashboard (opsional)
Register selalu menghasilkan role pengguna.
Pengguna tidak boleh mengklaim barang yang ia laporkan sendiri.
Petugas dan Admin tidak mengajukan klaim.
4. Fitur

Wajib:

ID	Fitur	Kriteria selesai
F1	Autentikasi + middleware role	Register membuat Pengguna; login/logout berfungsi; halaman Admin/Petugas menolak role yang tidak berhak (403 atau redirect)
F2	CRUD kategori dan lokasi (Admin)	Tambah, lihat, ubah, hapus; nama unik; data yang masih dipakai laporan tidak bisa dihapus dan menampilkan pesan jelas
F3	CRUD laporan barang (hilang/temuan) + upload 1 foto	Pengguna bisa buat, lihat, ubah, hapus laporan miliknya; foto opsional; laporan orang lain tidak bisa diubah
F4	Katalog, detail, cari dan filter tanpa reload (beranda = katalog)	Kartu grid; filter mengubah hasil lewat AJAX tanpa reload; detail menampilkan foto, lokasi, tanggal, status
F5	REST API read-only (2 endpoint)	GET /api/items dan GET /api/items/{id} mengembalikan JSON; katalog memakai endpoint ini
F6	Pengajuan klaim	Pengguna login bisa mengklaim barang temuan berstatus aktif dengan ciri dan bukti; barang menjadi dalam_klaim
F7	Verifikasi klaim + serah terima (Petugas)	Daftar klaim; setuju → barang selesai + waktu serah terima tercatat; tolak → barang kembali aktif
F8	Riwayat saya	Daftar laporan dan klaim milik pengguna beserta status terbaru dan catatan petugas
F9	Validasi form sisi klien (jQuery)	Form register, login, laporan, klaim menampilkan pesan kesalahan sebelum dikirim; validasi server tetap ada

Opsional (hanya jika waktu longgar):

O1: Dashboard admin, 4 angka (total laporan, total klaim, barang selesai, total pengguna), hanya count().
O2: Kelola/nonaktifkan pengguna.
5. Alur, status, dan aturan bisnis

Alur utama:

Pengguna login → buat laporan → langsung tampil di katalog.
Siapa saja mencari dan memfilter → buka detail.
Pemilik (login) menekan Klaim pada barang temuan → isi ciri dan bukti → barang dalam_klaim.
Petugas memeriksa bukti dan menemui pemilik di pos → setuju (barang selesai, waktu serah terima tercatat) atau tolak dengan catatan (barang kembali aktif).
Pengguna melihat hasil di Riwayat saya.
Laporan kehilangan: pemilik menandai sendiri selesai saat barangnya ketemu.

Status barang (items.status):

Dari	Ke	Pemicu
(baru)	aktif	Laporan dibuat
aktif	dalam_klaim	Klaim diajukan (hanya barang temuan)
dalam_klaim	selesai	Petugas menyetujui klaim
dalam_klaim	aktif	Petugas menolak klaim
aktif	selesai	Pemilik menandai laporan hilangnya selesai

Status klaim (claims.status): menunggu → disetujui atau ditolak.

Aturan bisnis:

Hanya barang temuan berstatus aktif yang bisa diklaim.
Satu klaim menunggu per barang pada satu waktu.
Laporan dalam_klaim tidak bisa diubah atau dihapus pemiliknya (Admin tetap bisa menghapus).
Katalog default menampilkan aktif dan dalam_klaim; selesai muncul bila difilter.
Persetujuan dilakukan saat pemilik hadir di pos, jadi persetujuan = serah terima.
6. Model data (5 tabel)

users: id, name, email (unik), password (hash bcrypt), role enum(admin,petugas,pengguna) default pengguna, timestamps

categories: id, name (unik), timestamps

locations: id, name (unik), timestamps

items: id, user_id FK→users (pelapor), type enum(hilang,temuan), title, description text, category_id FK→categories (restrict), location_id FK→locations (restrict), event_date date (tidak boleh lebih dari hari ini), photo_path nullable, status enum(aktif,dalam_klaim,selesai) default aktif, timestamps. Kode tampil LNF- + id 4 digit (contoh LNF-0007), dibuat dari id (accessor), bukan kolom.

claims: id, item_id FK→items, user_id FK→users (pengklaim), description text (ciri menurut pengklaim), proof text (bukti berupa teks, tanpa foto), status enum(menunggu,disetujui,ditolak) default menunggu, officer_note nullable, handled_by FK→users nullable, handled_at datetime nullable, timestamps

Relasi: users 1–N items; users 1–N claims; categories 1–N items; locations 1–N items; items 1–N claims.

Seeder:

Akun: admin@lostnfound.test, petugas@lostnfound.test, pengguna@lostnfound.test (satu password demo, khusus lokal).
Kategori contoh: KTM/Kartu, Elektronik, Dokumen, Tas dan Dompet, Kunci, Pakaian dan Aksesori, Alat Tulis, Lainnya.
Lokasi contoh: Perpustakaan, Kantin, Masjid, Parkiran, Lapangan Olahraga, Gedung Kuliah, Laboratorium, Pos Satpam (boleh diganti nama gedung kampus sebenarnya).
Sekitar 12 laporan realistis (KTM, tumbler, charger, kunci motor, payung, dll.) dengan campuran jenis dan status, supaya katalog, filter, dan riwayat tidak kosong.
7. Tech stack
Komponen	Keputusan
HTML5, CSS3	Lewat Blade
Bootstrap 5	Dipakai, variabelnya dioverride di satu app.css
JavaScript + jQuery	Filter AJAX (F4), validasi form (F9)
PHP + Laravel	Blade, Eloquent, migration, seeder, middleware, validation, storage. Samakan versi PHP dan Laravel di seluruh anggota
MySQL	Database
Autentikasi	Manual (Auth::attempt), bukan Breeze/Jetstream
Leaflet, hosting	Tidak dipakai
8. Halaman dan route

Halaman: (1) beranda/katalog, (2) detail barang, (3) login, (4) register, (5) form lapor barang, (6) riwayat saya, (7) form klaim, (8) dashboard admin (opsional), (9) admin kategori dan lokasi, (10) petugas daftar klaim + verifikasi. Ditambah form edit laporan.

Akses	Method dan path	Fungsi
Guest	GET /	Katalog
Guest	GET /items/{item}	Detail
Guest	GET/POST /register, GET/POST /login	Register dan login
Login	POST /logout	Logout
Pengguna, Petugas	GET /items/create, POST /items	Buat laporan
Pemilik laporan	GET /items/{item}/edit, PUT /items/{item}, DELETE /items/{item}	Ubah dan hapus
Pemilik laporan hilang	POST /items/{item}/selesai	Tandai sudah ketemu
Pengguna	GET /riwayat	Riwayat saya
Pengguna	GET /items/{item}/claims/create, POST /items/{item}/claims	Ajukan klaim
Petugas	GET /petugas/claims	Daftar klaim
Petugas	POST /petugas/claims/{claim}/approve, POST /petugas/claims/{claim}/reject	Putuskan klaim
Admin	resource /admin/categories, resource /admin/locations	CRUD master data
Admin	GET /admin/dashboard	Dashboard (opsional)
9. REST API

Publik, hanya baca, tanpa token.

Endpoint	Parameter	Respons
GET /api/items	q (kata kunci judul/deskripsi), type, category_id, location_id, status, date_from, page	Daftar terpaginasi, 9 per halaman
GET /api/items/{id}	-	Detail satu barang

Field JSON per barang: id, code, type, title, description, category, location, event_date, photo_url, status, created_at. Data pengguna (email, password, role) tidak dikirim. Katalog memanggil GET /api/items lewat jQuery AJAX saat filter berubah.

10. Validasi dan keamanan

Validasi (klien jQuery dan server Laravel):

Register: nama wajib, email valid dan unik, password minimal 8 karakter, konfirmasi cocok.
Login: email dan password wajib.
Laporan: judul, jenis, kategori, lokasi, tanggal wajib; tanggal tidak boleh melebihi hari ini; foto opsional jpg/jpeg/png maksimal 2 MB.
Klaim: ciri minimal 20 karakter, bukti minimal 10 karakter.

Keamanan dasar:

Password di-hash; CSRF aktif di semua form.
Otorisasi di server: pengguna hanya bisa ubah/hapus laporan miliknya; route Admin dan Petugas dilindungi middleware role.
Upload foto divalidasi tipe dan ukuran; disimpan di storage/app/public (jalankan php artisan storage:link).
API tidak membocorkan data pengguna.
11. Arah desain UI (anti template generik)
Rasa: papan pengumuman / label barang kampus; fungsional dan tenang, bukan landing page startup.
Warna: satu aksen merah Telkom (dari logo, sekitar 
#EC1C24), latar putih hangat 
#F6F3EE, teks charcoal 
#1F1F1F. Tanpa gradient.
Tipografi: judul Bricolage Grotesque, isi DM Sans (Google Fonts). Skala 14/16/20/28/40.
Bentuk: radius 4 px, border tipis 1 px, nyaris tanpa bayangan.
Komponen khas: kartu barang seperti “tag” dengan kode laporan (LNF-0001); status sebagai badge/stempel teks (AKTIF, DALAM KLAIM, SELESAI).
Ikon: Bootstrap Icons saja, tanpa emoji.
Bootstrap: override variabel (--bs-primary dll.) di satu app.css; tidak ada btn-primary biru bawaan.
Data: pakai data seeder realistis, tanpa “Lorem ipsum” atau nama generik.
Dilarang: gradient, glassmorphism, emoji sebagai ikon, hero tengah “Welcome to…”, semua kartu bulat besar, lebih dari satu warna aksen.
Proses: buat layout dan app.css dulu, lalu satu halaman (katalog), tinjau, baru halaman lain memakai komponen yang sama.
12. Di luar scope

Integrasi sistem akademik; push/email/WhatsApp dan notifikasi dalam aplikasi (diganti Riwayat saya); pencocokan barang otomatis; peta (Leaflet/OSM); deploy; API untuk menulis data dan token API; foto bukti klaim; verifikasi laporan sebelum tampil; tombol “Hubungi Penemu”.

13. Urutan dependensi build

Urutan ini murni soal ketergantungan antar fitur (bukan jadwal):

Setup Laravel, koneksi MySQL, migration 5 tabel, model dan relasi, seeder.
Layout Blade + app.css (bagian 11).
F1 autentikasi + middleware role (semua fitur lain bergantung padanya).
F2 CRUD kategori dan lokasi (dropdown laporan butuh datanya).
F3 CRUD laporan + upload foto.
F5 API + F4 katalog dan filter AJAX.
F6 klaim → F7 verifikasi dan serah terima → F8 riwayat.
F9 validasi jQuery di semua form.
Opsional: O1 dashboard, O2 kelola pengguna.
14. Definition of Done
Proyek berjalan di laptop anggota mana pun dengan php artisan migrate --seed lalu php artisan serve.
F1–F9 bisa didemokan memakai tiga akun seeder.
CRUD kategori, lokasi, dan laporan tersambung ke MySQL.
Seluruh halaman di bagian 8 bisa diakses dengan data seeder.
Desain mengikuti bagian 11 (tanpa warna biru Bootstrap default, tanpa emoji sebagai ikon, tanpa teks placeholder).