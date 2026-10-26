@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1">Laporan Arsip</h3>
            <p class="text-muted mb-0">Filter data, lalu cetak.</p>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-mobile-full" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>

    {{-- Kop khusus saat dicetak --}}
    <div class="d-none d-print-block mb-3">
        <h3 class="fw-bold mb-1">Laporan Arsip</h3>
        <div class="small">{{ implode('  |  ', $infoFilter) }}</div>
        <div class="small">Dicetak {{ now()->format('d M Y, H:i') }} oleh {{ auth()->user()->nama }}</div>
        <hr>
    </div>

    @if($errors->any())
        <div class="alert alert-danger no-print">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card card-soft mb-3 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-2">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-medium mb-1">Dari tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-medium mb-1">Sampai tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">Kategori</label>
                    <select name="kategori_id" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" @selected(request('kategori_id') == $kategori->id)>{{ $kategori->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium mb-1">Dibuat oleh</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua pengguna</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium mb-1">Kata kunci</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Judul atau nomor arsip...">
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-light border flex-grow-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-soft p-3 h-100">
                <div class="text-muted small">Total Hasil</div>
                <div class="fs-3 fw-bold">{{ $total }}</div>
            </div>
        </div>
        <div class="col-6 col-md-9">
            <div class="card card-soft p-3 h-100">
                <div class="text-muted small mb-2">Per Kategori</div>
                <div class="d-flex flex-wrap gap-2">
                    @forelse($perKategori as $row)
                        <span class="badge text-bg-light border fs-6 fw-medium">
                            {{ $row->kategori->nama }}: {{ $row->jumlah }}
                        </span>
                    @empty
                        <span class="text-muted">-</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-cards align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No. Arsip</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arsips as $arsip)
                            <tr>
                                <td data-label="No">{{ $arsips->firstItem() + $loop->index }}</td>
                                <td data-label="No. Arsip">{{ $arsip->nomor_arsip }}</td>
                                <td data-label="Judul">{{ $arsip->judul }}</td>
                                <td data-label="Kategori"><span class="badge text-bg-light border">{{ $arsip->kategori->nama }}</span></td>
                                <td data-label="Tanggal">{{ $arsip->tanggal_arsip->format('d M Y') }}</td>
                                <td data-label="Dibuat Oleh">{{ $arsip->pembuat->nama ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Tidak ada arsip yang cocok dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 no-print">{{ $arsips->links() }}</div>
        </div>
    </div>
@endsection