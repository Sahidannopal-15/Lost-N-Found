<div class="mb-3">
    <label class="form-label" for="type">Jenis laporan</label>
    <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
        <option value="">Pilih jenis</option>
        <option value="hilang" @selected(old('type', $item->type ?? '') === 'hilang')>Hilang</option>
        <option value="temuan" @selected(old('type', $item->type ?? '') === 'temuan')>Temuan</option>
    </select>
    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="title">Judul barang</label>
    <input type="text" id="title" name="title" value="{{ old('title', $item->title ?? '') }}"
           class="form-control @error('title') is-invalid @enderror" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="description">Deskripsi</label>
    <textarea id="description" name="description" rows="4"
              class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $item->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="category_id">Kategori</label>
    <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
        <option value="">Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $item->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="location_id">Lokasi</label>
    <select id="location_id" name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
        <option value="">Pilih lokasi</option>
        @foreach ($locations as $location)
            <option value="{{ $location->id }}" @selected((string) old('location_id', $item->location_id ?? '') === (string) $location->id)>{{ $location->name }}</option>
        @endforeach
    </select>
    @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="event_date">Tanggal kejadian</label>
    <input type="date" id="event_date" name="event_date" value="{{ old('event_date', isset($item) ? $item->event_date?->format('Y-m-d') : '') }}"
           class="form-control @error('event_date') is-invalid @enderror" required>
    @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="photo">Foto (opsional)</label>
    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png"
           class="form-control @error('photo') is-invalid @enderror">
    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if (! empty($item?->photo_path))
        <div class="form-text">Foto saat ini: {{ $item->photo_path }}</div>
    @endif
</div>