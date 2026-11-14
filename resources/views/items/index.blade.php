@extends('layouts.app')

@section('judul', 'Katalog barang hilang dan ditemukan')

@section('pita')
{{-- Form pencarian dibungkus pita gelap; panel filter lanjutan di bawahnya.
     Semua id dan name tidak berubah; JavaScript katalog tetap membaca #filter-katalog. --}}
<form id="filter-katalog" method="get" action="{{ route('items.index') }}">
    <section class="pita-pencarian">
        <div class="container">
            <section class="judul-halaman">
                <h1 class="h2">Barang hilang dan ditemukan di kampus</h1>
                <p>Lihat laporan barang yang hilang atau ditemukan.</p>
            </section>

            <div class="row g-3 align-items-end">
                <div class="col-9 col-md-6 col-lg-4">
                    <label class="form-label" for="q">Kata kunci</label>
                    <input class="form-control" id="q" name="q" type="search" value="{{ request('q') }}"
                           placeholder="Judul atau ciri barang">
                </div>
                <div class="col-3 col-md-auto">
                    <button class="btn btn-outline-secondary tombol-filter w-100" type="button"
                            data-bs-toggle="collapse" data-bs-target="#filter-lanjutan"
                            aria-expanded="false" aria-controls="filter-lanjutan">
                        Filter <span id="jumlah-filter" class="badge text-bg-secondary"></span>
                    </button>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit">Cari</button>
                    <button class="btn btn-outline-secondary" id="hapus-filter" type="button">Hapus filter</button>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="collapse" id="filter-lanjutan">
            <div class="penyaring-lanjutan">
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="type">Jenis laporan</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Semua jenis</option>
                            <option value="hilang" @selected(request('type') === 'hilang')>Hilang</option>
                            <option value="temuan" @selected(request('type') === 'temuan')>Temuan</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="category_id">Kategori</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="location_id">Lokasi</label>
                        <select class="form-select" id="location_id" name="location_id">
                            <option value="">Semua lokasi</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected((string) request('location_id') === (string) $location->id)>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Aktif dan dalam klaim</option>
                            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                            <option value="dalam_klaim" @selected(request('status') === 'dalam_klaim')>Dalam klaim</option>
                            <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label" for="date_from">Sejak tanggal</label>
                        <input class="form-control" id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('isi')
    <p class="visually-hidden" id="status-katalog" role="status" aria-live="polite"></p>

    <div id="hasil-katalog" aria-live="polite">
        @if ($items->isNotEmpty())
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
                @foreach ($items as $item)
                    <div class="col">@include('partials.tiket', ['item' => $item])</div>
                @endforeach
            </div>
        @elseif ($hasFilters)
            @include('partials.kosong', [
                'ikon' => 'bi-search',
                'judul' => 'Belum ada barang yang cocok.',
                'teks' => 'Ubah kata kunci atau hapus filter. Barangmu belum dilaporkan? Buat laporan baru.',
            ])
        @else
            @include('partials.kosong', [
                'ikon' => 'bi-inbox',
                'judul' => 'Belum ada laporan.',
                'teks' => 'Laporkan barang hilang atau temuan pertama.',
            ])
        @endif
    </div>

    <div class="mt-4" id="paginasi-katalog">
        {{ $items->links() }}
    </div>
@endsection

@push('skrip')
<script>
    $(function () {
        const $form = $('#filter-katalog');
        // Hitung filter aktif
        function perbaruiJumlahFilter() {
            const jumlah = $form.find('select, input[type="date"], input[name="q"]').filter(function () {
                return $(this).val() !== '';
            }).length;
            $('#jumlah-filter').text(jumlah ? jumlah : '');
        }
        perbaruiJumlahFilter();
        $form.on('change input', perbaruiJumlahFilter);
        const $hasil = $('#hasil-katalog');
        const $paginasi = $('#paginasi-katalog');
        const $status = $('#status-katalog');
        const endpoint = @json(route('api.items.index'));
        const itemBaseUrl = @json(url('/items'));
        let timerPencarian;
        let requestSerial = 0;

        function buatTiket(item) {
            const $tiket = $('<article>').addClass('tiket');
            const $tautan = $('<a>')
                .addClass('tiket__tautan')
                .attr('href', itemBaseUrl + '/' + encodeURIComponent(item.id));
            const $foto = $('<div>').addClass('tiket__foto');

            if (item.photo_url) {
                $foto.append($('<img>').attr({ src: item.photo_url, alt: '', loading: 'lazy' }));
            } else {
                $foto.append(
                    $('<i>').addClass('bi bi-box-seam').attr('aria-hidden', 'true'),
                    $('<span>').addClass('visually-hidden').text('Belum ada foto')
                );
            }

            const $isi = $('<div>').addClass('tiket__isi');
            $isi.append($('<h3>').addClass('tiket__judul').text(item.title));
            $isi.append($('<p>')
                .addClass('tiket__info')
                .append($('<i>').addClass('bi bi-geo-alt').attr('aria-hidden', 'true'))
                .append($('<span>').text(item.location)));

            const bagianTanggal = String(item.event_date || '').slice(0, 10).split('-');
            const tanggal = bagianTanggal.length === 3
                ? bagianTanggal[2] + '/' + bagianTanggal[1] + '/' + bagianTanggal[0]
                : item.event_date;
            $isi.append($('<p>')
                .addClass('tiket__info')
                .append($('<i>').addClass('bi bi-calendar3').attr('aria-hidden', 'true'))
                .append($('<span>').text(tanggal)));

            $tautan.append($foto, $isi);

            const $sobekan = $('<footer>').addClass('tiket__sobekan');
            const jenis = item.type === 'temuan' ? 'Ditemukan' : 'Hilang';
            const jenisKelas = item.type === 'temuan' ? 'temuan' : 'hilang';
            $sobekan.append($('<span>')
                .addClass('tiket__jenis tiket__jenis--' + jenisKelas)
                .text(jenis));
            $sobekan.append($('<span>').addClass('tiket__kode').text(item.code));

            const petaStatus = {
                aktif: ['Aktif', 'bi-circle-fill'],
                dalam_klaim: ['Dalam klaim', 'bi-hourglass-split'],
                selesai: ['Selesai', 'bi-check2-circle']
            };
            const statusItem = petaStatus[item.status] || ['Status tidak diketahui', 'bi-circle'];
            const statusKelas = ['aktif', 'dalam_klaim', 'selesai'].includes(item.status)
                ? item.status.replace(/_/g, '-')
                : 'tidak-diketahui';
            $sobekan.append($('<span>')
                .addClass('status status--' + statusKelas)
                .append($('<i>').addClass('bi ' + statusItem[1]).attr('aria-hidden', 'true'))
                .append(document.createTextNode(statusItem[0])));

            return $tiket.append($tautan, $sobekan);
        }

        function tampilkanKosong(adaFilter) {
            const judul = adaFilter ? 'Belum ada barang yang cocok.' : 'Belum ada laporan.';
            const teks = adaFilter
                ? 'Ubah kata kunci atau hapus filter. Barangmu belum dilaporkan? Buat laporan baru.'
                : 'Laporkan barang hilang atau temuan pertama.';
            const ikon = adaFilter ? 'bi-search' : 'bi-inbox';

            $hasil.empty().append(
                $('<div>').addClass('kosong')
                    .append($('<i>').addClass('bi ' + ikon).attr('aria-hidden', 'true'))
                    .append($('<h2>').addClass('h3').text(judul))
                    .append($('<p>').addClass('prose').text(teks))
            );
        }

        function tampilkanPaginasi(data) {
            $paginasi.empty();
            if (data.last_page <= 1) return;

            const $nav = $('<nav>').attr('aria-label', 'Paginasi katalog');
            const $daftar = $('<ul>').addClass('pagination justify-content-center');
            const tautanPaginasi = [
                ['Sebelumnya', data.prev_page_url],
                ['Berikutnya', data.next_page_url]
            ];

            tautanPaginasi.forEach(function ([label, url]) {
                const $li = $('<li>').addClass('page-item' + (url ? '' : ' disabled'));
                if (url) {
                    $li.append($('<button>').attr('type', 'button')
                        .addClass('page-link tombol-halaman')
                        .data('url', url)
                        .text(label));
                } else {
                    $li.append($('<span>').addClass('page-link').text(label));
                }
                $daftar.append($li);
            });

            $nav.append($daftar);
            $paginasi.append($nav);
        }

        function muatBarang(url) {
            const nomorPermintaan = ++requestSerial;
            const adaFilter = $form.serializeArray().some(function (bagian) {
                return bagian.value !== '';
            });
            $status.text('Memuat laporan.');

            $.getJSON(url || endpoint, url ? {} : $form.serialize())
                .done(function (data) {
                    if (nomorPermintaan !== requestSerial) {
                        return;
                    }

                    if (data.data.length) {
                        const $grid = $('<div>').addClass('row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3');
                        data.data.forEach(function (item) {
                            $grid.append($('<div>').addClass('col').append(buatTiket(item)));
                        });
                        $hasil.empty().append($grid);
                        $status.text(data.total + ' laporan ditemukan.');
                    } else {
                        tampilkanKosong(adaFilter);
                        $status.text('Tidak ada laporan yang cocok.');
                    }

                    tampilkanPaginasi(data);
                })
                .fail(function () {
                    $hasil.empty().append(
                        $('<div>').addClass('alert alert-danger').attr('role', 'alert')
                            .text('Daftar barang tidak dapat dimuat. Coba lagi.')
                    );
                    $paginasi.empty();
                    $status.text('Daftar barang tidak dapat dimuat.');
                });
        }

        $form.on('submit', function (event) {
            event.preventDefault();
            muatBarang();
        });

        $form.find('select, input[type="date"]').on('change', function () {
            muatBarang();
        });

        $form.find('input[name="q"]').on('input', function () {
            clearTimeout(timerPencarian);
            timerPencarian = setTimeout(function () {
                muatBarang();
            }, 300);
        });

        $('#hapus-filter').on('click', function () {
            $form.find('select').val('');
            $form.find('input[name="q"], input[type="date"]').val('');
            muatBarang();
        });

        $paginasi.on('click', '.tombol-halaman', function () {
            muatBarang($(this).data('url'));
        });
    });
</script>
@endpush

