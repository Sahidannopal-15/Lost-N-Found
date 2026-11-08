@extends('layouts.app')

@section('judul', 'Lokasi')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Lokasi</h1>
    </section>

    <div class="mb-3">
        <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">Tambah lokasi</a>
    </div>

    <div class="panel table-responsive">
        <table class="table tabel-admin">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jumlah laporan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->items_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-outline-secondary btn-sm">Ubah</a>
                            <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada lokasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection