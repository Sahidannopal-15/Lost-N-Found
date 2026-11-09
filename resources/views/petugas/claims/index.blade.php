@extends('layouts.app')

@section('judul', 'Klaim masuk')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Klaim masuk</h1>
    </section>

    @if ($claims->isEmpty())
        @include('partials.kosong', [
            'ikon' => 'bi-inbox',
            'judul' => 'Tidak ada klaim yang menunggu.',
            'teks' => '',
        ])
    @else
        <div class="panel table-responsive">
            <table class="table tabel-admin">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Pengklaim</th>
                        <th>Ciri</th>
                        <th>Bukti</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($claims as $claim)
                        <tr>
                            <td>{{ $claim->item->title }} ({{ $claim->item->code }})</td>
                            <td>{{ $claim->user->name }}</td>
                            <td>{{ $claim->description }}</td>
                            <td>{{ $claim->proof }}</td>
                            <td class="text-end">
                                @if (auth()->user()->role === 'petugas')
                                    <form method="POST" action="{{ route('petugas.claims.approve', $claim) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Setujui dan serahkan</button>
                                    </form>
                                    <form method="POST" action="{{ route('petugas.claims.reject', $claim) }}" class="d-inline">
                                        @csrf
                                        <div class="input-group input-group-sm mt-2">
                                            <input type="text" name="officer_note" class="form-control" placeholder="Catatan penolakan" required>
                                            <button type="submit" class="btn btn-outline-danger">Tolak klaim</button>
                                        </div>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection