@extends('layouts.app')

@section('judul', 'Dashboard')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Dashboard</h1>
    </section>

    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="panel p-3 text-center">
                <div class="text-secondary small">Total laporan</div>
                <div class="h3 mb-0">{{ $totalLaporan }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="panel p-3 text-center">
                <div class="text-secondary small">Total klaim</div>
                <div class="h3 mb-0">{{ $totalKlaim }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="panel p-3 text-center">
                <div class="text-secondary small">Barang selesai</div>
                <div class="h3 mb-0">{{ $barangSelesai }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="panel p-3 text-center">
                <div class="text-secondary small">Total pengguna</div>
                <div class="h3 mb-0">{{ $totalPengguna }}</div>
            </div>
        </div>
    </div>
@endsection