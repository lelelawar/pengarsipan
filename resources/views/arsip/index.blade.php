@extends('layouts.app')

@section('title', 'Daftar Arsip')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Daftar Arsip</h3>
            <p class="text-muted mb-0">Semua arsip yang tersimpan.</p>
        </div>
        <a href="{{ route('arsip.create') }}" class="btn btn-primary btn-mobile-full">
            <i class="bi bi-plus-lg me-1"></i> Tambah Arsip
        </a>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('arsip.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul atau nomor arsip...">
                </div>
                <div class="col-6 col-md-2">
                    <select name="kategori_id" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" @selected(request('kategori_id') == $kategori->id)>{{ $kategori->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="jenis" class="form-select">
                        <option value="">Semua format</option>
                        @foreach(\App\Models\Arsip::JENIS as $kode => $info)
                            <option value="{{ $kode }}" @selected(request('jenis') === $kode)>{{ $info['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="periode" class="form-select">
                        <option value="">Semua waktu</option>
                        <option value="bulan_ini" @selected(request('periode') === 'bulan_ini')>Bulan ini</option>
                        <option value="tahun_ini" @selected(request('periode') === 'tahun_ini')>Tahun ini</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i> Cari</button>
                    <a href="{{ route('arsip.index') }}" class="btn btn-light border" title="Reset filter"><i class="bi bi-x-lg"></i></a>
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
                            <th>No. Arsip</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Dibuat Oleh</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arsips as $arsip)
                            <tr>
                                <td data-label="No. Arsip">{{ $arsip->nomor_arsip }}</td>
                                <td data-label="Judul">
                                    @if($arsip->file_path)
                                        <i class="bi {{ $arsip->ikon }} me-1" style="color: {{ $arsip->warna_ikon }}" title="{{ strtoupper($arsip->ekstensi) }}"></i>
                                    @endif
                                    {{ $arsip->judul }}
                                </td>
                                <td data-label="Kategori"><span class="badge text-bg-light border">{{ $arsip->kategori->nama }}</span></td>
                                <td data-label="Tanggal">{{ $arsip->tanggal_arsip->format('d M Y') }}</td>
                                <td data-label="Dibuat Oleh">{{ $arsip->pembuat->nama ?? '-' }}</td>
                                <td data-label="Aksi" class="text-end text-nowrap">
                                    <a href="{{ route('arsip.show', $arsip) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if($arsip->file_path)
                                        @if($arsip->bisa_pratinjau)
                                            <a href="{{ route('arsip.preview', $arsip) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Pratinjau">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('arsip.download', $arsip) }}" class="btn btn-sm btn-outline-success" title="Unduh">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif

                                    @can('update', $arsip)
                                        <a href="{{ route('arsip.edit', $arsip) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('delete', $arsip)
                                        <form action="{{ route('arsip.destroy', $arsip) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Pindahkan arsip ini ke Sampah? Arsip bisa dipulihkan dalam 30 hari.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada arsip yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $arsips->links() }}
            </div>
        </div>
    </div>
@endsection