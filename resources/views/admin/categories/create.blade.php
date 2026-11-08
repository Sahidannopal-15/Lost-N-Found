@extends('layouts.app')

@section('judul', 'Tambah kategori')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Tambah kategori</h1>
    </section>

    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection