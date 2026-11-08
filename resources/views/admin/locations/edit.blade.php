@extends('layouts.app')

@section('judul', 'Ubah lokasi')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Ubah lokasi</h1>
    </section>

    <form method="POST" action="{{ route('admin.locations.update', $location) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name', $location->name) }}"
                   class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection