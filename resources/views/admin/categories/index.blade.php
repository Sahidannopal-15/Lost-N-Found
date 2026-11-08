@extends('layouts.app')

@section('judul', 'Kategori')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Kategori</h1>
    </section>

    <div class="mb-3">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Tambah kategori</a>
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
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->items_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline-secondary btn-sm">Ubah</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection