@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Kategori</h3>
        <p class="text-muted mb-0">Kelola kategori arsip.</p>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('kategori.store') }}" class="row g-2">
                @csrf
                <div class="col-sm-9">
                    <input type="text" name="nama" value="{{ old('nama') }}"
                           class="form-control @error('nama') is-invalid @enderror"
                           placeholder="Nama kategori baru..." required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
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
                            <th>Nama Kategori</th>
                            <th>Jumlah Arsip</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $kategori)
                            <tr>
                                <td data-label="Nama">{{ $kategori->nama }}</td>
                                <td data-label="Jumlah Arsip">
                                    <span class="badge text-bg-light border">{{ $kategori->arsips_count }}</span>
                                </td>
                                <td data-label="Aksi" class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#edit-{{ $kategori->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin hapus kategori {{ $kategori->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Belum ada kategori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal edit per kategori --}}
    @foreach($kategoris as $kategori)
        <div class="modal fade" id="edit-{{ $kategori->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('kategori.update', $kategori) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-medium">Nama Kategori</label>
                        <input type="text" name="nama" value="{{ $kategori->nama }}" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection