<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Kampus',
            'email' => 'admin@lostnfound.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $petugas = User::create([
            'name' => 'Petugas Pos',
            'email' => 'petugas@lostnfound.test',
            'password' => 'password',
            'role' => 'petugas',
        ]);

        $pengguna = User::create([
            'name' => 'Budi Santoso',
            'email' => 'pengguna@lostnfound.test',
            'password' => 'password',
            'role' => 'pengguna',
        ]);

        $kategoriNama = ['KTM/Kartu', 'Elektronik', 'Dokumen', 'Tas dan Dompet', 'Kunci', 'Pakaian dan Aksesori', 'Alat Tulis', 'Lainnya'];
        $kategori = [];
        foreach ($kategoriNama as $nama) {
            $kategori[$nama] = Category::create(['name' => $nama]);
        }

        $lokasiNama = ['Perpustakaan', 'Kantin', 'Masjid', 'Parkiran', 'Lapangan Olahraga', 'Gedung Kuliah', 'Laboratorium', 'Pos Satpam'];
        $lokasi = [];
        foreach ($lokasiNama as $nama) {
            $lokasi[$nama] = Location::create(['name' => $nama]);
        }

        $laporan = [
            ['user' => $pengguna, 'type' => 'hilang', 'title' => 'KTM atas nama Budi', 'description' => 'KTM warna putih dengan foto, hilang sekitar kantin.', 'category' => 'KTM/Kartu', 'location' => 'Kantin', 'event_date' => now()->subDays(2)->toDateString(), 'status' => 'aktif'],
            ['user' => $petugas, 'type' => 'temuan', 'title' => 'Tumbler stainless', 'description' => 'Tumbler warna biru ditemukan di meja baca perpustakaan.', 'category' => 'Lainnya', 'location' => 'Perpustakaan', 'event_date' => now()->subDays(1)->toDateString(), 'status' => 'aktif'],
            ['user' => $pengguna, 'type' => 'hilang', 'title' => 'Charger laptop', 'description' => 'Charger 65W dengan kabel USB-C.', 'category' => 'Elektronik', 'location' => 'Gedung Kuliah', 'event_date' => now()->subDays(3)->toDateString(), 'status' => 'dalam_klaim'],
            ['user' => $admin, 'type' => 'temuan', 'title' => 'Kunci motor Honda', 'description' => 'Gantungan kunci berbentuk helm, ditemukan di parkiran.', 'category' => 'Kunci', 'location' => 'Parkiran', 'event_date' => now()->subDays(4)->toDateString(), 'status' => 'aktif'],
            ['user' => $pengguna, 'type' => 'temuan', 'title' => 'Payung lipat hitam', 'description' => 'Payung lipat warna hitam ditemukan di ruang baca.', 'category' => 'Pakaian dan Aksesori', 'location' => 'Perpustakaan', 'event_date' => now()->subDays(5)->toDateString(), 'status' => 'selesai'],
            ['user' => $petugas, 'type' => 'hilang', 'title' => 'Tas ransel abu-abu', 'description' => 'Tas ransel berisi buku dan payung.', 'category' => 'Tas dan Dompet', 'location' => 'Laboratorium', 'event_date' => now()->subDays(6)->toDateString(), 'status' => 'aktif'],
            ['user' => $pengguna, 'type' => 'temuan', 'title' => 'Dompet kulit coklat', 'description' => 'Dompet berisi KTP dan sedikit uang.', 'category' => 'Tas dan Dompet', 'location' => 'Kantin', 'event_date' => now()->subDays(2)->toDateString(), 'status' => 'dalam_klaim'],
            ['user' => $admin, 'type' => 'hilang', 'title' => 'Kacamata baca', 'description' => 'Kacamata frame hitam, lensa minus.', 'category' => 'Lainnya', 'location' => 'Masjid', 'event_date' => now()->subDays(7)->toDateString(), 'status' => 'selesai'],
            ['user' => $petugas, 'type' => 'temuan', 'title' => 'Buku catatan kalkulus', 'description' => 'Buku catatan dengan nama di sampul.', 'category' => 'Alat Tulis', 'location' => 'Gedung Kuliah', 'event_date' => now()->subDays(1)->toDateString(), 'status' => 'aktif'],
            ['user' => $pengguna, 'type' => 'hilang', 'title' => 'Jaket hoodie biru', 'description' => 'Hoodie ukuran M, ada goresan di lengan.', 'category' => 'Pakaian dan Aksesori', 'location' => 'Lapangan Olahraga', 'event_date' => now()->subDays(8)->toDateString(), 'status' => 'aktif'],
            ['user' => $admin, 'type' => 'temuan', 'title' => 'Flashdisk 32GB', 'description' => 'Flashdisk warna merah, ditemukan di lab komputer.', 'category' => 'Elektronik', 'location' => 'Laboratorium', 'event_date' => now()->subDays(3)->toDateString(), 'status' => 'aktif'],
            ['user' => $petugas, 'type' => 'hilang', 'title' => 'STNK motor', 'description' => 'STNK atas nama pemilik, hilang di parkiran.', 'category' => 'Dokumen', 'location' => 'Parkiran', 'event_date' => now()->subDays(9)->toDateString(), 'status' => 'selesai'],
        ];

        foreach ($laporan as $data) {
            Item::create([
                'user_id' => $data['user']->id,
                'type' => $data['type'],
                'title' => $data['title'],
                'description' => $data['description'],
                'category_id' => $kategori[$data['category']]->id,
                'location_id' => $lokasi[$data['location']]->id,
                'event_date' => $data['event_date'],
                'photo_path' => null,
                'status' => $data['status'],
            ]);
        }

        // Klaim contoh
        $itemTemuan = Item::where('type', 'temuan')->where('user_id', '!=', $pengguna->id)->first();
        \App\Models\Claim::create([
            'item_id' => $itemTemuan->id,
            'user_id' => $pengguna->id,
            'description' => 'Ciri khusus yang hanya diketahui pemilik barang temuan.',
            'proof' => 'Bukti kepemilikan berupa foto dan keterangan.',
            'status' => 'menunggu',
        ]);
        $itemTemuan->update(['status' => 'dalam_klaim']);

        $item2 = Item::where('type', 'temuan')->where('user_id', '!=', $pengguna->id)->skip(1)->first();
        $claim2 = \App\Models\Claim::create([
            'item_id' => $item2->id,
            'user_id' => $pengguna->id,
            'description' => 'Ciri khusus lain yang hanya diketahui pemilik.',
            'proof' => 'Bukti tambahan untuk klaim disetujui.',
            'status' => 'disetujui',
            'handled_by' => $petugas->id,
            'handled_at' => now(),
        ]);
        $item2->update(['status' => 'selesai']);

        $item3 = Item::where('type', 'temuan')->where('user_id', '!=', $pengguna->id)->skip(2)->first();
        \App\Models\Claim::create([
            'item_id' => $item3->id,
            'user_id' => $pengguna->id,
            'description' => 'Ciri barang yang diajukan untuk klaim ditolak.',
            'proof' => 'Bukti yang tidak cukup meyakinkan.',
            'status' => 'ditolak',
            'officer_note' => 'Bukti tidak mencukupi.',
            'handled_by' => $petugas->id,
            'handled_at' => now(),
        ]);
        $item3->update(['status' => 'aktif']);
    }
}
