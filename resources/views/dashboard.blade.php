@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Halo, {{ auth()->user()->nama }} 👋</h3>
        <p class="text-muted mb-0">Ringkasan data pengarsipan hari ini.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-folder2-open"></i></div>
                    <div>
                        <div class="text-muted small">Total Arsip</div>
                        <div class="fs-4 fw-bold">{{ $totalArsip }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-calendar-plus"></i></div>
                    <div>
                        <div class="text-muted small">Arsip Bulan Ini</div>
                        <div class="fs-4 fw-bold">{{ $arsipBulanIni }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-tags"></i></div>
                    <div>
                        <div class="text-muted small">Kategori</div>
                        <div class="fs-4 fw-bold">{{ $totalKategori }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
            <div class="col-6 col-xl-3">
                <div class="card card-soft p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="text-muted small">Total Akun</div>
                            <div class="fs-4 fw-bold">{{ $totalUser }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Arsip Terbaru</h5>

            <div class="table-responsive">
                                <table class="table table-cards align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Arsip</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arsipTerbaru as $arsip)
                            <tr>
                                <td data-label="No. Arsip">{{ $arsip->nomor_arsip }}</td>
                                <td data-label="Judul">{{ $arsip->judul }}</td>
                                <td data-label="Kategori"><span class="badge text-bg-light border">{{ $arsip->kategori->nama }}</span></td>
                                <td data-label="Tanggal">{{ $arsip->tanggal_arsip->format('d M Y') }}</td>
                                <td data-label="Dibuat Oleh">{{ $arsip->pembuat->nama ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada arsip.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table
            </div>
        </div>
    </div>
@endsection