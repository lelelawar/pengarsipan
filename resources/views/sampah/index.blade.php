@extends('layouts.app')

@section('title', 'Sampah')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Sampah</h3>
            <p class="text-muted mb-0">
                Arsip yang dihapus disimpan di sini selama {{ \App\Models\Arsip::MASA_SAMPAH_HARI }} hari,
                setelah itu dihapus permanen secara otomatis.
            </p>
        </div>

        @if($totalSampah > 0)
            <form method="POST" action="{{ route('sampah.kosongkan') }}"
                  onsubmit="return confirm('Hapus permanen SEMUA arsip di Sampah? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-mobile-full">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan Sampah
                </button>
            </form>
        @endif
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('sampah.index') }}" class="row g-2">
                <div class="col-sm-9">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul atau nomor arsip di Sampah...">
                </div>
                <div class="col-sm-3 d-grid">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            @if($arsips->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-trash3 d-block mb-2" style="font-size: 2.4rem; color: #c7d2fe;"></i>
                    <div class="fw-medium">{{ request('q') ? 'Tidak ada arsip yang cocok.' : 'Sampah kosong.' }}</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-cards align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Arsip</th>
                                <th>Judul</th>
                                <th>Kategori</th>
                                <th>Dihapus</th>
                                <th>Sisa Waktu</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arsips as $arsip)
                                @php
                                    $kedaluwarsa = $arsip->deleted_at->copy()->addDays(\App\Models\Arsip::MASA_SAMPAH_HARI);
                                    $sisa        = max(0, (int) now()->diffInDays($kedaluwarsa, false));
                                @endphp
                                <tr>
                                    <td data-label="No. Arsip">{{ $arsip->nomor_arsip }}</td>
                                    <td data-label="Judul">
                                        @if($arsip->file_path)
                                            <i class="bi {{ $arsip->ikon }} me-1" style="color: {{ $arsip->warna_ikon }}"></i>
                                        @endif
                                        {{ $arsip->judul }}
                                    </td>
                                    <td data-label="Kategori"><span class="badge text-bg-light border">{{ $arsip->kategori->nama }}</span></td>
                                    <td data-label="Dihapus" class="text-nowrap">{{ $arsip->deleted_at->format('d M Y, H:i') }}</td>
                                    <td data-label="Sisa Waktu">
                                        <span class="badge {{ $sisa <= 7 ? 'text-bg-danger' : 'text-bg-light border' }}">
                                            {{ $sisa }} hari lagi
                                        </span>
                                    </td>
                                    <td data-label="Aksi" class="text-end text-nowrap">
                                        <form action="{{ route('sampah.pulihkan', $arsip->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Pulihkan">
                                                <i class="bi bi-arrow-counterclockwise"></i> <span class="d-none d-xl-inline">Pulihkan</span>
                                            </button>
                                        </form>

                                        <form action="{{ route('sampah.hapus', $arsip->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus permanen arsip ini beserta filenya? Tindakan ini tidak bisa dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus permanen">
                                                <i class="bi bi-x-octagon"></i> <span class="d-none d-xl-inline">Hapus Permanen</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $arsips->links() }}</div>
            @endif
        </div>
    </div>
@endsection