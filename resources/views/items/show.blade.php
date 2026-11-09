@extends('layouts.app')

@section('judul', $item->code.' — '.$item->title)

@section('isi')
    <p class="mb-3">
        <a class="btn btn-outline-secondary" href="{{ route('items.index') }}">Kembali ke katalog</a>
    </p>

    <article class="panel panel--isi">
        <div class="row g-4">
            <div class="col-12 col-md-5">
                @if ($item->photo_path)
                    <img class="img-fluid rounded" src="{{ asset('storage/'.$item->photo_path) }}"
                         alt="Foto barang: {{ $item->title }}">
                @else
                    <div class="ratio ratio-4x3 bg-light rounded d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-seam fs-1 text-secondary" aria-hidden="true"></i>
                        <span class="visually-hidden">Belum ada foto</span>
                    </div>
                @endif
            </div>
            <div class="col-12 col-md-7">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="tiket__jenis tiket__jenis--{{ $item->type }}">
                        {{ $item->type === 'temuan' ? 'Ditemukan' : 'Hilang' }}
                    </span>
                    @include('partials.status', ['status' => $item->status])
                </div>
                <h1 class="h2">{{ $item->title }}</h1>
                <p class="text-secondary">{{ $item->code }}</p>
                <p>{{ $item->description }}</p>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kategori</dt>
                    <dd class="col-sm-8">{{ $item->category->name }}</dd>
                    <dt class="col-sm-4">Lokasi</dt>
                    <dd class="col-sm-8">{{ $item->location->name }}</dd>
                    <dt class="col-sm-4">Tanggal</dt>
                    <dd class="col-sm-8">{{ $item->event_date->format('d/m/Y') }}</dd>
                </dl>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    @auth
                        @if (auth()->id() === $item->user_id && auth()->user()->role !== 'admin')
                            @if ($item->status !== 'dalam_klaim')
                                <a class="btn btn-outline-secondary" href="{{ route('items.edit', $item) }}">Ubah</a>
                                <form method="POST" action="{{ route('items.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus laporan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">Hapus laporan</button>
                                </form>
                            @endif
                            @if ($item->type === 'hilang' && $item->status === 'aktif')
                                <form method="POST" action="{{ route('items.selesai', $item) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Tandai sudah ketemu</button>
                                </form>
                            @endif
                        @elseif ($item->type === 'temuan' && $item->status === 'aktif' && auth()->user()->role === 'pengguna')
                            <a class="btn btn-primary" href="{{ route('claims.create', $item) }}">Ajukan klaim</a>
                        @endif
                        @if (auth()->user()->role === 'admin')
                            <form method="POST" action="{{ route('items.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Hapus laporan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Hapus laporan</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </article>
@endsection
