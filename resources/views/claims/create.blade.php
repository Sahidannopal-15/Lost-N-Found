@extends('layouts.app')

@section('judul', 'Ajukan klaim')

@section('isi')
    <section class="judul-halaman">
        <h1 class="h2">Ajukan klaim</h1>
        <p>{{ $item->title }} ({{ $item->code }})</p>
    </section>

    <form method="POST" action="{{ route('claims.store', $item) }}" id="form-claims">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="description">Ciri barang</label>
            <textarea id="description" name="description" rows="4"
                      class="form-control @error('description') is-invalid @enderror" required minlength="20">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="proof">Bukti</label>
            <textarea id="proof" name="proof" rows="4"
                      class="form-control @error('proof') is-invalid @enderror" required minlength="10">{{ old('proof') }}</textarea>
            @error('proof')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Kirim klaim</button>
            <a href="{{ route('items.show', $item) }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
    @push('skrip')
    <script src="{{ asset('js/validasi.js') }}"></script>
    @endpush
@endsection