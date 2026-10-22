@extends('layouts.app')

@section('title', 'Kelola Akun')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Kelola Akun</h3>
            <p class="text-muted mb-0">Tambah, ubah, dan hapus akun pengguna.</p>
        </div>
        <a href="{{ route('akun.create') }}" class="btn btn-primary btn-mobile-full">
            <i class="bi bi-plus-lg me-1"></i> Tambah Akun
        </a>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('akun.index') }}" class="row g-2">
                <div class="col-sm-9">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau username...">
                </div>
                <div class="col-sm-3 d-grid">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
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
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td data-label="Nama">{{ $user->nama }}</td>
                                <td data-label="Username">{{ $user->username }}</td>
                                <td data-label="Role">
                                    <span class="badge {{ $user->isAdmin() ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td data-label="Dibuat">{{ $user->created_at->format('d M Y') }}</td>
                                <td data-label="Aksi" class="text-end text-nowrap">
                                    <a href="{{ route('akun.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('akun.destroy', $user) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin hapus akun {{ $user->username }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Akun tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>
@endsection