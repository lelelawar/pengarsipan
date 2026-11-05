@extends('layouts.app')

@section('title', 'Favorit')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Favorit</h3>
            <p class="text-muted mb-0">Arsip yang kamu pin untuk akses cepat.</p>
        </div>
        <a href="{{ route('arsip.index') }}" class="btn btn-outline-primary btn-mobile-full">
            <i class="bi bi-folder2-open me-1"></i> Lihat Daftar Arsip
        </a>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('favorit.index') }}" class="row g-2">
                <div class="col-12 col-md-9">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul atau nomor arsip..." aria-label="Cari Favorit">
                </div>
                <div class="col-8 col-md-2 d-grid">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
                <div class="col-4 col-md-1 d-grid">
                    <a href="{{ route('favorit.index') }}" class="btn btn-light border" title="Reset pencarian" aria-label="Reset pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            @if($arsips->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-star"></i>
                    <h5 class="fw-bold text-dark">{{ request('q') ? 'Favorit tidak ditemukan.' : 'Belum ada Favorit.' }}</h5>
                    <p class="mb-3">{{ request('q') ? 'Coba kata pencarian lain.' : 'Pin arsip dari daftar atau halaman detail untuk menyimpannya di sini.' }}</p>
                    <a href="{{ route('arsip.index') }}" class="btn btn-primary">
                        <i class="bi bi-folder2-open me-1"></i> Jelajahi Arsip
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-cards align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Arsip</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Tanggal Arsip</th>
                                <th>Dibuat Oleh</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arsips as $arsip)
                                <tr>
                                    <td data-label="No. Arsip">{{ $arsip->nomor_arsip }}</td>
                                    <td data-label="Judul">{{ $arsip->judul }}</td>
                                    <td data-label="Kategori"><span class="badge text-bg-light border">{{ $arsip->kategori->nama }}</span></td>
                                    <td data-label="Tanggal Arsip">{{ $arsip->tanggal_arsip->format('d M Y') }}</td>
                                    <td data-label="Dibuat Oleh">{{ $arsip->pembuat->nama ?? '-' }}</td>
                                    <td data-label="Aksi" class="text-end text-nowrap">
                                        <a href="{{ route('arsip.show', $arsip) }}" class="btn btn-sm btn-outline-secondary" title="Detail arsip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('arsip.unpin', $arsip) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-warning" title="Hapus dari Favorit">
                                                <i class="bi bi-star-fill me-1"></i> Unpin
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $arsips->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
