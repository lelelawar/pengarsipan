@props(['nama'])

@php
    $kelasWarna = match ($nama) {
        'Surat Masuk' => 'badge-kategori--surat-masuk',
        'Laporan' => 'badge-kategori--laporan',
        'Surat Kerja Sama' => 'badge-kategori--surat-kerja-sama',
        default => '',
    };
@endphp

<span {{ $attributes->class(['badge', 'badge-kategori', $kelasWarna]) }}>{{ $slot->isEmpty() ? $nama : $slot }}</span>
