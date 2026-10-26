@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .hero {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 1.2rem; color: #fff;
        padding: 1.75rem 2rem;
        display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;
        box-shadow: 0 12px 30px rgba(79, 70, 229, .25);
    }
    .hero::before, .hero::after {
        content: ""; position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .08);
    }
    .hero::before { width: 220px; height: 220px; right: -60px; top: -90px; }
    .hero::after  { width: 140px; height: 140px; right: 140px; bottom: -80px; }
    .hero > * { position: relative; z-index: 1; }
    .hero .sub { font-size: .85rem; opacity: .85; }
    .hero h3 { font-weight: 700; margin: .15rem 0 .35rem; }
    .hero p { margin: 0; opacity: .85; font-size: .93rem; }

    .stat-card { padding: 1.1rem 1.2rem; transition: transform .15s, box-shadow .15s; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(30, 41, 80, .09); }
    .stat-tile {
        width: 50px; height: 50px; border-radius: .9rem; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;
    }
    .c-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .c-emerald { background: linear-gradient(135deg, #34d399, #059669); }
    .c-amber { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .c-rose { background: linear-gradient(135deg, #fb7185, #e11d48); }
    .stat-num { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
    .stat-label { color: var(--muted); font-size: .85rem; }

    .progress { background: #eef0f7; border-radius: 1rem; }
    .progress-bar { background: linear-gradient(90deg, #6366f1, #8b5cf6); border-radius: 1rem; }

    .feed { list-style: none; margin: 0; padding: 0; }
    .feed li { display: flex; gap: .75rem; padding: .65rem 0; border-bottom: 1px dashed var(--border); }
    .feed li:last-child { border-bottom: 0; }
    .feed .dot { width: 10px; height: 10px; border-radius: 50%; margin-top: .4rem; flex-shrink: 0; }
    .feed .txt { font-size: .88rem; line-height: 1.35; }
    .feed .meta { color: var(--muted); font-size: .76rem; }

    .empty-state { text-align: center; padding: 2.2rem 1rem; color: var(--muted); }
    .empty-state i { font-size: 2.4rem; color: #c7d2fe; display: block; margin-bottom: .5rem; }

    @media (max-width: 575.98px) { .hero { padding: 1.25rem; } }
</style>
@endpush

@section('content')
    @php
        $jam   = now('Asia/Jakarta')->hour;
        $salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
        $admin = auth()->user()->isAdmin();
        $maks  = max(1, $perKategori->max('arsips_count'));
        $warnaAksi = ['tambah' => '#10b981', 'edit' => '#6366f1', 'hapus' => '#ef4444'];
    @endphp

    <div class="hero mb-4">
        <div>
            <div class="sub">{{ $salam }}</div>
            <h3>Halo, {{ auth()->user()->nama }} 👋</h3>
            <p>Ada {{ $totalArsip }} arsip tersimpan. {{ $arsipBulanIni > 0 ? $arsipBulanIni . ' di antaranya ditambahkan bulan ini.' : 'Belum ada arsip baru bulan ini.' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('arsip.create') }}" class="btn btn-light fw-semibold">
                <i class="bi bi-plus-lg me-1"></i> Tambah Arsip
            </a>
            <a href="{{ route('arsip.index') }}" class="btn btn-outline-light">
                <i class="bi bi-folder2-open me-1"></i> Lihat Arsip
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-{{ $admin ? 3 : 4 }}">
            <div class="card-soft stat-card d-flex align-items-center gap-3">
                <div class="stat-tile c-indigo"><i class="bi bi-folder2-open"></i></div>
                <div>
                    <div class="stat-num">{{ $totalArsip }}</div>
                    <div class="stat-label">Total Arsip</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-{{ $admin ? 3 : 4 }}">
            <div class="card-soft stat-card d-flex align-items-center gap-3">
                <div class="stat-tile c-emerald"><i class="bi bi-calendar-plus"></i></div>
                <div>
                    <div class="stat-num">{{ $arsipBulanIni }}</div>
                    <div class="stat-label">Arsip Bulan Ini</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-{{ $admin ? 3 : 4 }}">
            <div class="card-soft stat-card d-flex align-items-center gap-3">
                <div class="stat-tile c-amber"><i class="bi bi-tags"></i></div>
                <div>
                    <div class="stat-num">{{ $totalKategori }}</div>
                    <div class="stat-label">Kategori</div>
                </div>
            </div>
        </div>

        @if($admin)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card-soft stat-card d-flex align-items-center gap-3">
                    <div class="stat-tile c-rose"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-num">{{ $totalUser }}</div>
                        <div class="stat-label">Total Akun</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card-soft h-100">
                <div class="p-3 p-md-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Arsip Terbaru</h5>
                    <a href="{{ route('arsip.index') }}" class="small text-decoration-none fw-medium">Lihat semua <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="p-3 p-md-4 pt-3">
                    @if($arsipTerbaru->isEmpty())
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <div class="fw-medium mb-1">Belum ada arsip</div>
                            <div class="small mb-3">Mulai dengan menambahkan arsip pertama.</div>
                            <a href="{{ route('arsip.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Arsip
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
                                        <th>Tanggal</th>
                                        <th>Dibuat Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($arsipTerbaru as $arsip)
                                        <tr>
                                            <td data-label="No. Arsip" class="fw-medium">{{ $arsip->nomor_arsip }}</td>
                                            <td data-label="Judul">
                                                <a href="{{ route('arsip.show', $arsip) }}" class="text-decoration-none text-dark">{{ $arsip->judul }}</a>
                                            </td>
                                            <td data-label="Kategori"><span class="badge text-bg-light border">{{ $arsip->kategori->nama }}</span></td>
                                            <td data-label="Tanggal">{{ $arsip->tanggal_arsip->format('d M Y') }}</td>
                                            <td data-label="Dibuat Oleh">{{ $arsip->pembuat->nama ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-3">
            <div class="card-soft p-3 p-md-4">
                <h5 class="fw-bold mb-3">Arsip per Kategori</h5>

                @forelse($perKategori as $kat)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-medium">{{ $kat->nama }}</span>
                            <span class="text-muted">{{ $kat->arsips_count }}</span>
                        </div>
                        <div class="progress" style="height: 8px">
                            <div class="progress-bar" style="width: {{ ($kat->arsips_count / $maks) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">Belum ada kategori.</div>
                @endforelse
            </div>

            @if($admin)
                <div class="card-soft p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0">Aktivitas Terbaru</h5>
                        <a href="{{ route('log.index') }}" class="small text-decoration-none fw-medium">Semua</a>
                    </div>

                    <ul class="feed">
                        @forelse($logTerbaru as $log)
                            <li>
                                <span class="dot" style="background: {{ $warnaAksi[$log->aksi] ?? '#9ca3af' }}"></span>
                                <div>
                                    <div class="txt">{{ $log->deskripsi }}</div>
                                    <div class="meta">{{ $log->user->nama ?? '-' }} · {{ $log->created_at->locale('id')->diffForHumans() }}</div>
                                </div>
                            </li>
                        @empty
                            <li><span class="text-muted small">Belum ada aktivitas.</span></li>
                        @endforelse
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection