@extends('layouts.app')

@section('judul', '403 - Akses ditolak')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Akses ditolak</h1>
        <p>Kamu tidak memiliki izin untuk membuka halaman ini.</p>
    </section>
    <p>
        <a class="btn btn-primary" href="{{ url('/') }}">Kembali ke katalog</a>
    </p>
@endsection