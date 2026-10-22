@extends('layouts.app')

@section('title', 'Tambah Akun')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Tambah Akun</h3>
        <p class="text-muted mb-0">Buat akun baru untuk pengguna.</p>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('akun.store') }}">
                @csrf

                @include('akun._form', ['user' => null])

                <div class="mt-4 d-flex gap-2 form-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                    <a href="{{ route('akun.index') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection