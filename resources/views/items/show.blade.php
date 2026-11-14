@extends('layouts.app')

@section('judul', $item->code.' — '.$item->title)

@section('isi')
    <p class="mb-3">
        <a class="btn btn-outline-secondary" href="{{ route('items.index') }}">Kembali ke katalog</a>
    </p>

    <article class="panel detail">
        <div class="detail__foto">
            @if ($item->photo_path)
                <img src="{{ asset('storage/'.$item->photo_path) }}" alt="Foto barang: {{ $item->title }}">
            @else
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                <span class="visually-hidden">Belum ada foto</span>
            @endif
        </div>

        <div class="detail__isi">
            <h1 class="h2">{{ $item->title }}</h1>
            <p class="teks-pudar">{{ $item->code }}</p>
            <p>{{ $item->description }}</p>
            <dl class="data-barang mb-0">
                <dt>Kategori</dt>
                <dd>{{ $item->category->name }}</dd>
                <dt>Lokasi</dt>
                <dd>{{ $item->location->name }}</dd>
                <dt>Tanggal</dt>
                <dd>{{ $item->event_date->format('d/m/Y') }}</dd>
            </dl>

            {{-- Aksi positif: di HP menjadi bilah menempel di bawah layar (pola c, app.css). --}}
            <div class="aksi-positif">
                @auth
                    @if (auth()->id() === $item->user_id && auth()->user()->role !== 'admin')
                        @if ($item->type === 'hilang' && $item->status === 'aktif')
                            <form method="POST" action="{{ route('items.selesai', $item) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Tandai sudah ketemu</button>
                            </form>
                        @endif
                    @elseif ($item->type === 'temuan' && $item->status === 'aktif' && auth()->user()->role === 'pengguna')
                        <a class="btn btn-primary" href="{{ route('claims.create', $item) }}">Ajukan klaim</a>
                    @endif
                @endauth
            </div>
        </div>

        {{-- Sobekan tiket: jenis, status, dan aksi netral/merusak. --}}
        <footer class="detail__sobekan">
            <span class="tiket__jenis tiket__jenis--{{ $item->type }}">
                {{ $item->type === 'temuan' ? 'Ditemukan' : 'Hilang' }}
            </span>
            @include('partials/status', ['status' => $item->status])
            <div class="aksi">
                @auth
                    @if (auth()->id() === $item->user_id && auth()->user()->role !== 'admin')
                        @if ($item->status !== 'dalam_klaim')
                            <a class="btn btn-outline-secondary" href="{{ route('items.edit', $item) }}">Ubah</a>
                        @endif
                    @endif
                    @if (auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Hapus laporan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Hapus laporan</button>
                        </form>
                    @endif
                @endauth
            </div>
        </footer>
    </article>
@endsection
