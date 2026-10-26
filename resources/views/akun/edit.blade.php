@extends('layouts.app')

@section('title', 'Edit Akun')

@section('content')
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Edit Akun</h3>
        <p class="text-muted mb-0">{{ $user->username }}</p>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('akun.update', $user) }}">
                @csrf
                @method('PUT')

                @include('akun.form')

                <div class="mt-4 d-flex gap-2 form-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('akun.index') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection