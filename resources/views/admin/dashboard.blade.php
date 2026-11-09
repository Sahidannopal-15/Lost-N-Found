@extends('layouts.app')

@section('judul', 'Dashboard')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Dashboard</h1>
    </section>

    <div class="panel">
        <div class="dashboard">
            <div class="dashboard__sel">
                <div class="dashboard__label teks-pudar">Total laporan</div>
                <div class="dashboard__angka">{{ $totalLaporan }}</div>
            </div>
            <div class="dashboard__sel">
                <div class="dashboard__label teks-pudar">Total klaim</div>
                <div class="dashboard__angka">{{ $totalKlaim }}</div>
            </div>
            <div class="dashboard__sel">
                <div class="dashboard__label teks-pudar">Barang selesai</div>
                <div class="dashboard__angka">{{ $barangSelesai }}</div>
            </div>
            <div class="dashboard__sel">
                <div class="dashboard__label teks-pudar">Total pengguna</div>
                <div class="dashboard__angka">{{ $totalPengguna }}</div>
            </div>
        </div>
    </div>
@endsection