<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('findo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('findo.png') }}">
    <title>@yield('judul', 'Findo')</title>

    {{-- Font: Archivo (judul) + Plus Jakarta Sans (isi). Ada font cadangan di app.css. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Urutan penting: Bootstrap dulu, app.css terakhir supaya override-nya menang. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('gaya')
</head>
<body>
    <a class="lewati-ke-konten" href="#konten">Lewati ke konten</a>

    @php
        // [label, path, pola untuk menandai menu aktif, role yang boleh melihat (null = semua)]
        $peran = auth()->check() ? auth()->user()->role : null;
        $menu = [
                    ['Katalog',      '/',                 '/',                 null],
                    ['Lapor barang', '/items/create',     'items/create',      ['pengguna', 'petugas']],
                    ['Riwayat saya', '/riwayat',          'riwayat',           ['pengguna']],
                    ['Klaim masuk',  '/petugas/claims',   'petugas/*',         ['petugas']],
                    ['Dashboard',    '/admin/dashboard',  'admin/dashboard',   ['admin']],
                    ['Kategori',     '/admin/categories', 'admin/categories*', ['admin']],
                    ['Lokasi',       '/admin/locations',  'admin/locations*',  ['admin']],
                    ['Pengguna',     '/admin/users',      'admin/users*',      ['admin']],
                ];
    @endphp

    <header class="bar-atas">
        <nav class="navbar navbar-expand-lg" data-bs-theme="dark" aria-label="Menu utama">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <span class="merek-tanda" aria-hidden="true"><img src="{{ asset('findo.png') }}" alt="" width="28" height="28"></span>
                    Findo
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#menu-utama" aria-controls="menu-utama"
                        aria-expanded="false" aria-label="Buka menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menu-utama">
                    <ul class="navbar-nav me-auto">
                        @foreach ($menu as [$label, $path, $pola, $boleh])
                            @if ($boleh === null || in_array($peran, $boleh))
                                <li class="nav-item">
                                    <a @class(['nav-link', 'active' => request()->is($pola)])
                                       href="{{ url($path) }}"
                                       @if (request()->is($pola)) aria-current="page" @endif>{{ $label }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-2">
                        @auth
                            <li class="nav-item">
                                <span class="nav-link nama-pengguna">{{ auth()->user()->name }}</span>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ url('/logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link nav-tombol">Keluar</button>
                                </form>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/login') }}">Masuk</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-primary btn-sm" href="{{ url('/register') }}">Buat akun</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="konten" class="halaman">
            {{-- Pita selebar layar (mis. pita pencarian katalog), di luar container. --}}
            @yield('pita', '')
            <div class="container">
            @if (session('sukses'))
                <div class="alert alert-success" role="status">{{ session('sukses') }}</div>
            @endif
            @if (session('galat'))
                <div class="alert alert-danger" role="alert">{{ session('galat') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->count() }} isian perlu diperbaiki. Lihat pesan di bawah tiap kolom.
                </div>
            @endif

            @yield('isi')
        </div>
    </main>

    <footer class="kaki">
        <div class="container">
            <div class="row g-4 kaki__grid">
                <div class="col-12 col-md-5">
                    <a class="kaki__merek" href="{{ url('/') }}">
                        <span class="merek-tanda" aria-hidden="true"><img src="{{ asset('findo.png') }}" alt="" width="28" height="28"></span>
                        Findo
                    </a>
                    <p class="kaki__deskripsi mt-2 mb-0">
                        Platform pelaporan dan pencarian barang hilang &amp; temuan di lingkungan Telkom University.
                        Membantu menghubungkan pemilik dengan penemu secara cepat dan aman.
                    </p>
                </div>
                <div class="col-6 col-md-3">
                    <h3 class="kaki__judul">Navigasi</h3>
                    <ul class="kaki__tautan">
                        <li><a href="{{ url('/') }}">Katalog</a></li>
                        <li><a href="{{ url('/login') }}">Masuk</a></li>
                        <li><a href="{{ url('/register') }}">Buat akun</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-4">
                    <h3 class="kaki__judul">Kontak</h3>
                    <ul class="kaki__tautan kaki__info">
                        <li><i class="bi bi-geo-alt" aria-hidden="true"></i> Telkom University, Bandung</li>
                        <li><i class="bi bi-envelope" aria-hidden="true"></i> support@findo.id</li>
                    </ul>
                </div>
            </div>
            <hr class="kaki__pemisah">
            <div class="kaki__bawah d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-1">
                <p class="mb-0">&copy; {{ date('Y') }} Findo. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('skrip')
</body>
</html>