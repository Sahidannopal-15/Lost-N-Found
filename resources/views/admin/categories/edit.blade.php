@extends('layouts.app')

@section('judul', 'Ubah kategori')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Ubah kategori</h1>
    </section>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}"
                   class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection