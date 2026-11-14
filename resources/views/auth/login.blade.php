@extends('layouts.app')

@section('judul', 'Masuk')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Masuk</h1>
        <p>Masuk untuk melapor dan mengklaim barang.</p>
    </section>

    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <form method="POST" action="{{ route('login') }}" id="form-login" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required
                           autocomplete="email" inputmode="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required minlength="8"
                           autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Masuk</button>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary">Buat akun</a>
                </div>
            </form>
        </div>
    </div>
    @push('skrip')
    <script src="{{ asset('js/validasi.js') }}"></script>
    @endpush
@endsection