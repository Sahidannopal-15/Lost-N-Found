@extends('layouts.app')

@section('judul', 'Lapor barang')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Lapor barang</h1>
    </section>

    <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
        @csrf
        @include('items._form')
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan laporan</button>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
@endsection