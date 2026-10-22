@extends('layouts.app')

@section('title', 'Edit Arsip')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Edit Arsip</h3>
        <p class="text-muted mb-0">{{ $arsip->nomor_arsip }}</p>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('arsip.update', $arsip) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('arsip._form')

                <div class="mt-4 d-flex gap-2 from-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('arsip.show', $arsip) }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection