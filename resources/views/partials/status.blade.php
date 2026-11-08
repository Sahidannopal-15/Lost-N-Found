{{-- Badge status barang.
     Pakai: @include('partials.status', ['status' => $item->status])
     Nilai: aktif | dalam_klaim | selesai (persis nama di PRD).
     Dibedakan lewat ikon + bentuk garis + teks, bukan warna saja. --}}
@php
    $peta = [
        'aktif'       => ['Aktif',       'bi-circle-fill'],
        'dalam_klaim' => ['Dalam klaim', 'bi-hourglass-split'],
        'selesai'     => ['Selesai',     'bi-check2-circle'],
    ];
    [$labelStatus, $ikonStatus] = $peta[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'bi-circle'];
@endphp
<span class="status status--{{ str_replace('_', '-', $status) }}">
    <i class="bi {{ $ikonStatus }}" aria-hidden="true"></i>{{ $labelStatus }}
</span>