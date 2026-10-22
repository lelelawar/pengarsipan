@extends('layouts.app')

@section('title', 'Tambah Arsip')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Tambah Arsip</h3>
        <p class="text-muted mb-0">Isi data arsip dan upload filenya.</p>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('arsip.store') }}" enctype="multipart/form-data">
                @csrf

                @include('arsip._form', ['arsip' => null])

                <div class="mt-4 d-flex gap-2 from-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                    <a href="{{ route('arsip.index') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection