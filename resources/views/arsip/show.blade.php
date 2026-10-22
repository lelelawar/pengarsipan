@extends('layouts.app')

@section('title', 'Detail Arsip')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-1">Detail Arsip</h3>
            <p class="text-muted mb-0">{{ $arsip->nomor_arsip }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('arsip.index') }}" class="btn btn-light border">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
            @can('update', $arsip)
                <a href="{{ route('arsip.edit', $arsip) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endcan
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <h4 class="fw-bold mb-4">{{ $arsip->judul }}</h4>

            <dl class="row mb-0">
                <dt class="col-sm-3">Nomor Arsip</dt>
                <dd class="col-sm-9">{{ $arsip->nomor_arsip }}</dd>

                <dt class="col-sm-3">Kategori</dt>
                <dd class="col-sm-9">{{ $arsip->kategori->nama }}</dd>

                <dt class="col-sm-3">Tanggal Arsip</dt>
                <dd class="col-sm-9">{{ $arsip->tanggal_arsip->format('d F Y') }}</dd>

                <dt class="col-sm-3">Dibuat Oleh</dt>
                <dd class="col-sm-9">{{ $arsip->pembuat->nama ?? '-' }}</dd>

                <dt class="col-sm-3">Deskripsi</dt>
                <dd class="col-sm-9">{{ $arsip->deskripsi ?: '-' }}</dd>

                <dt class="col-sm-3">File</dt>
                <dd class="col-sm-9">
                    @if($arsip->file_path)
                        <a href="{{ asset('storage/' . $arsip->file_path) }}" target="_blank" class="no-print">
                            <i class="bi bi-paperclip"></i> {{ basename($arsip->file_path) }}
                        </a>
                    @else
                        <span class="text-muted">Tidak ada file.</span>
                    @endif
                </dd>
            </dl>
        </div>
    </div>
@endsection