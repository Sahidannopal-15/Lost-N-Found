@extends('layouts.app')

@section('judul', 'Buat akun')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Buat akun</h1>
        <p>Daftar sebagai pengguna.</p>
    </section>

    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Nama</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required minlength="8">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Konfirmasi password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" required minlength="8">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Buat akun</button>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">Masuk</a>
                </div>
            </form>
        </div>
    </div>
@endsection