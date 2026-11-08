@extends('layouts.app')

@section('judul', 'Riwayat saya')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Riwayat saya</h1>
    </section>

    @if ($items->isEmpty() && $claims->isEmpty())
        @include('partials.kosong', [
            'ikon' => 'bi-clock-history',
            'judul' => 'Kamu belum punya laporan atau klaim.',
            'teks' => 'Kehilangan atau menemukan sesuatu? Buat laporan pertamamu.',
        ])
    @else
        @if ($items->isNotEmpty())
            <h2 class="h3">Laporan saya</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
                @foreach ($items as $item)
                    <div class="col">@include('partials.tiket', ['item' => $item])</div>
                @endforeach
            </div>
        @endif

        @if ($claims->isNotEmpty())
            <h2 class="h3">Klaim saya</h2>
            <div class="panel table-responsive">
                <table class="table tabel-admin">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Status klaim</th>
                            <th>Catatan petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($claims as $claim)
                            <tr>
                                <td>{{ $claim->item->title }} ({{ $claim->item->code }})</td>
                                <td>{{ $claim->status }}</td>
                                <td>{{ $claim->officer_note }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
@endsection