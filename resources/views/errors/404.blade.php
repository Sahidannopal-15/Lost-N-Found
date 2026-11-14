@extends('layouts.app')

@section('judul', '404 - Halaman tidak ditemukan')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Halaman tidak ditemukan</h1>
        <p>Halaman yang kamu cari tidak ada atau sudah dipindahkan.</p>
    </section>
    <p>
        <a class="btn btn-primary" href="{{ url('/') }}">Kembali ke katalog</a>
    </p>
@endsection