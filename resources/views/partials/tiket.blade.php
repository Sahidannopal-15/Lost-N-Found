{{-- Tiket barang: pola kartu untuk SEMUA daftar barang (katalog, riwayat, hasil AJAX).
     Pakai di dalam grid:
       <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
         @foreach ($items as $item)
           <div class="col">@include('partials.tiket', ['item' => $item])</div>
         @endforeach
       </div>
     Syarat model Item:
       - relasi location() dan category(); muat dengan Item::with('location', 'category')
       - cast: protected $casts = ['event_date' => 'date'];
       - accessor $item->code (hasilnya "LNF-0007"), lihat PRD bagian 6
     Untuk hasil AJAX (jQuery) di F4: tiru struktur HTML ini persis di JavaScript. --}}
<article class="tiket">
    <a class="tiket__tautan" href="{{ url('/items/'.$item->id) }}">
        <div class="tiket__foto">
            @if ($item->photo_path)
                <img src="{{ asset('storage/'.$item->photo_path) }}" alt="" loading="lazy">
            @else
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span class="visually-hidden">Belum ada foto</span>
            @endif
        </div>
        <div class="tiket__isi">
            <h3 class="tiket__judul">{{ $item->title }}</h3>
            <p class="tiket__info"><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ $item->location->name }}</p>
            <p class="tiket__info"><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $item->event_date->format('d/m/Y') }}</p>
        </div>
    </a>
    <footer class="tiket__sobekan">
        <span class="tiket__jenis tiket__jenis--{{ $item->type }}">{{ $item->type === 'temuan' ? 'Ditemukan' : 'Hilang' }}</span>
        <span class="tiket__kode">{{ $item->code }}</span>
        @include('partials.status', ['status' => $item->status])
    </footer>
</article>