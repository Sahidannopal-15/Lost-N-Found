@extends('layouts.app')

@section('judul', 'Ubah laporan')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Ubah laporan</h1>
    </section>

    <form method="POST" action="{{ route('items.update', $item) }}" id="form-items" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('items._form', ['item' => $item])
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="{{ route('items.show', $item) }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection