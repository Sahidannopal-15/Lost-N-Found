{{-- Keadaan kosong (katalog tanpa hasil, riwayat kosong, dst.).
     Pakai:
       @include('partials.kosong', [
           'ikon'   => 'bi-search',
           'judul'  => 'Belum ada barang yang cocok',
           'teks'   => 'Ubah kata kunci atau hapus filter. Barangmu belum dilaporkan? Buat laporan baru.',
           'tombol' => [
               ['label' => 'Hapus filter', 'url' => url('/'), 'gaya' => 'outline-secondary'],
               ['label' => 'Lapor barang', 'url' => url('/items/create'), 'gaya' => 'primary'],
           ],
       ]) --}}
<div class="kosong">
    <i class="bi {{ $ikon ?? 'bi-inbox' }}" aria-hidden="true"></i>
    <h2 class="h3">{{ $judul }}</h2>
    <p class="prose">{{ $teks }}</p>
    @if (!empty($tombol))
        <div class="kosong__aksi">
            @foreach ($tombol as $t)
                <a class="btn btn-{{ $t['gaya'] ?? 'primary' }}" href="{{ $t['url'] }}">{{ $t['label'] }}</a>
            @endforeach
        </div>
    @endif
</div>