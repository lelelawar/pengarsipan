@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Log Aktivitas</h3>
        <p class="text-muted mb-0">Riwayat tambah, edit, dan hapus oleh semua pengguna.</p>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('log.index') }}" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari deskripsi aktivitas...">
                </div>
                <div class="col-md-4">
                    <select name="aksi" class="form-select">
                        <option value="">Semua aksi</option>
                        @foreach(['tambah' => 'Tambah', 'edit' => 'Edit', 'hapus' => 'Hapus'] as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(request('aksi') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-cards align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $warna = ['tambah' => 'success', 'edit' => 'primary', 'hapus' => 'danger'][$log->aksi] ?? 'secondary';
                            @endphp
                            <tr>
                                <td data-label="Waktu" class="text-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td data-label="Pengguna">{{ $log->user->nama ?? '-' }}</td>
                                <td data-label="Aksi"><span class="badge text-bg-{{ $warna }}">{{ ucfirst($log->aksi) }}</span></td>
                                <td data-label="Deskripsi">{{ $log->deskripsi }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $logs->links() }}</div>
        </div>
    </div>
@endsection