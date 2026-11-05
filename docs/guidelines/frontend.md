# Guidelines Frontend

**Tujuan:** semua halaman baru memakai pola, kelas, dan tampilan yang sama dengan halaman yang sudah ada, dan tetap nyaman di HP maupun saat dicetak.

Frontend memakai **Blade + Bootstrap 5.3.3 + Bootstrap Icons 1.11.3 + font Inter**, semuanya lewat CDN. Tidak ada build step.

## Wajib

- Halaman dalam aplikasi memakai `@extends('layouts.app')` dengan `@section('title', '...')` dan `@section('content')`.
- CSS atau JS khusus halaman diletakkan lewat `@push('styles')` dan `@push('scripts')`. Jangan menambah CSS global di view, CSS global ada di `layouts/app.blade.php`.
- Semua teks yang terlihat pengguna dalam **bahasa Indonesia** yang singkat dan jelas.
- Setiap `<td>` di tabel `table-cards` punya **`data-label`**.
- Tombol berbentuk ikon punya atribut `title` (dan `aria-label` bila tidak ada teks).
- Aksi yang mengubah data memakai form `POST`, `PUT`, atau `DELETE` dengan `@csrf`.
- Aksi yang menghapus atau tidak bisa dibatalkan diberi konfirmasi.
- Halaman dicek di laptop, HP (DevTools device toolbar), dan tampilan cetak bila relevan.

## Dilarang

- Menambah framework atau library (jQuery, Tailwind, Alpine, dan sebagainya) tanpa persetujuan.
- Menulis `{!! !!}` untuk data dari pengguna (judul, deskripsi, nama).
- Link langsung ke file arsip lewat `asset('storage/...')`. Pakai route `arsip.preview` dan `arsip.download`.
- Menulis query Eloquent baru di view. Siapkan datanya di controller. (Pengecualian yang diketahui ada di bagian bawah.)
- `<td>` tanpa `data-label` pada tabel `table-cards`, atau tabel baru tanpa kelas `table-cards`.
- Teks UI berbahasa Inggris.

## Layout dan Halaman

Struktur halaman standar:

```blade
@extends('layouts.app')

@section('title', 'Daftar Arsip')

@section('content')
    {{-- 1. Kepala halaman --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Daftar Arsip</h3>
            <p class="text-muted mb-0">Semua arsip yang tersimpan.</p>
        </div>
        <a href="{{ route('arsip.create') }}" class="btn btn-primary btn-mobile-full">
            <i class="bi bi-plus-lg me-1"></i> Tambah Arsip
        </a>
    </div>

    {{-- 2. Kartu filter (bila ada) --}}
    <div class="card card-soft mb-3">
        <div class="card-body"> <form method="GET" class="row g-2"> ... </form> </div>
    </div>

    {{-- 3. Kartu isi --}}
    <div class="card card-soft">
        <div class="card-body"> ... </div>
    </div>
@endsection
```

Halaman login berdiri sendiri (HTML penuh, tidak memakai layout utama). Halaman lain selalu memakai layout.

## Kelas yang Sudah Ada

Pakai ulang, jangan membuat padanan baru.

| Kelas | Fungsi |
|---|---|
| `card-soft` | Kartu standar (border tipis dan bayangan lembut) |
| `table-cards` | Tabel yang jadi kartu di layar di bawah 768 px (wajib `data-label` di tiap `td`) |
| `btn-mobile-full` | Tombol lebar penuh di HP, otomatis di layar besar |
| `form-actions` | Pembungkus tombol Simpan dan Batal, jadi vertikal dan lebar penuh di HP |
| `no-print` | Disembunyikan saat dicetak |
| `stat-card`, `hero`, `feed`, `empty-state` | Khusus dashboard, didefinisikan di `@push('styles')` dashboard |
| `dropzone` | Khusus form arsip, didefinisikan di `@push('styles')` form |

Variabel warna ada di `:root` layout: `--primary` (`#4f46e5`), `--primary-soft`, `--bg`, `--border`, `--muted`. Pakai variabel, jangan menulis ulang kode warna.

## Konvensi Tombol dan Ikon

| Aksi | Gaya | Ikon |
|---|---|---|
| Aksi utama (simpan, tambah, cari) | `btn btn-primary` | `bi-save`, `bi-plus-lg`, `bi-search` |
| Detail | `btn btn-sm btn-outline-secondary` | `bi-eye` |
| Pratinjau | `btn btn-sm btn-outline-info` | `bi-eye-fill` |
| Unduh, pulihkan | `btn btn-sm btn-outline-success` | `bi-download`, `bi-arrow-counterclockwise` |
| Edit | `btn btn-sm btn-outline-primary` | `bi-pencil` |
| Hapus | `btn btn-sm btn-outline-danger` | `bi-trash` |
| Pin/Favorit | `btn btn-sm btn-warning` (sudah dipin) atau `btn btn-sm btn-outline-warning` (belum dipin) | `bi-star-fill`, `bi-star`; label `Unpin` atau `Pin` |
| Batal, reset | `btn btn-light border` | |

Warna status log: tambah `#10b981`, edit `#6366f1`, hapus `#ef4444`, pulihkan `#0ea5e9`, permanen `#6b7280`.

## Pola Tabel

```blade
<div class="table-responsive">
    <table class="table table-cards align-middle mb-0">
        <thead class="table-light">
            <tr><th>No. Arsip</th><th>Judul</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($arsips as $arsip)
                <tr>
                    <td data-label="No. Arsip">{{ $arsip->nomor_arsip }}</td>
                    <td data-label="Judul">{{ $arsip->judul }}</td>
                    <td data-label="Aksi" class="text-end text-nowrap"> ...tombol... </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">Belum ada arsip.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">{{ $arsips->links() }}</div>
```

- Baris kosong memakai `colspan` dan **tanpa** `data-label`.
- Kolom aksi: `text-end text-nowrap`, tombol ikon kecil dengan `title`.
- Pagination memakai `{{ $x->links() }}` (Bootstrap 5 sudah diatur di `AppServiceProvider`).

## Pola Form

```blade
<form method="POST" action="{{ route('arsip.store') }}" enctype="multipart/form-data">
    @csrf

    <label class="form-label fw-medium">Judul</label>
    <input type="text" name="judul" value="{{ old('judul', $arsip?->judul) }}"
           class="form-control @error('judul') is-invalid @enderror" required>
    @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror

    <div class="mt-4 d-flex gap-2 form-actions">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
        <a href="{{ route('arsip.index') }}" class="btn btn-light border">Batal</a>
    </div>
</form>
```

- Selalu `old('nama', $model?->nama)` agar input tidak hilang saat validasi gagal dan form bisa dipakai untuk tambah dan edit.
- Form edit menambah `@method('PUT')`. Form dengan file wajib `enctype="multipart/form-data"`.
- Partial form (`arsip/form.blade.php`, `akun/form.blade.php`) dipanggil `@include('arsip.form', ['arsip' => null])` untuk tambah dan `@include('arsip.form')` untuk edit (variabel dari controller). Nama file **tanpa underscore**.
- Kolom wajib diberi atribut `required`. Pesan kesalahan server tetap ditampilkan lewat `@error`.

Konfirmasi aksi merusak:

```blade
<form action="{{ route('arsip.destroy', $arsip) }}" method="POST" class="d-inline"
      onsubmit="return confirm('Pindahkan arsip ini ke Sampah? Arsip bisa dipulihkan dalam 30 hari.')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
</form>
```

## Responsif

| Lebar layar | Perilaku |
|---|---|
| Di bawah 992 px | Sidebar menjadi drawer (tombol hamburger), header desktop disembunyikan |
| Di bawah 768 px | `table-cards` menjadi kartu |
| Di bawah 576 px | Tombol lebih tinggi, `form-actions` vertikal dan lebar penuh |

Uji tiap halaman baru dengan DevTools (F12, ikon device toolbar). Tidak boleh ada geser horizontal di halaman.

Pratinjau PDF tertanam (`iframe`) hanya ditampilkan di layar medium ke atas (`d-none d-md-block`). Di HP diganti tombol Buka PDF.

## Cetak

- `window.print()` lewat tombol Cetak. Elemen yang tidak boleh tercetak diberi `no-print` (sidebar, header, tombol, form filter, pratinjau tertanam).
- Kop khusus cetak: `<div class="d-none d-print-block">` berisi judul, ringkasan filter, waktu cetak, dan nama pencetak (lihat `laporan/index.blade.php`).

## JavaScript

- Vanilla JS, ditulis di `@push('scripts')` dan dibungkus fungsi langsung dijalankan (`(function () { ... })();`) agar tidak mengotori lingkup global.
- Data dari PHP dikirim dengan `@json(...)`.
- Validasi di browser hanya kemudahan pengguna. Validasi sebenarnya tetap di server.
- Contoh yang ada: area upload di `arsip/form.blade.php`, buka tutup sidebar di layout.

## Anti-pattern

| Salah | Benar |
|---|---|
| `<td>{{ $x }}</td>` di `table-cards` | `<td data-label="Judul">{{ $x }}</td>` |
| `<a href="{{ asset('storage/'.$arsip->file_path) }}">` | `<a href="{{ route('arsip.download', $arsip) }}">` |
| `{!! $arsip->deskripsi !!}` | `{{ $arsip->deskripsi }}` |
| Menulis `#4f46e5` berulang di CSS halaman | `var(--primary)` |
| `<button>Save</button>` | `<button>Simpan</button>` |
| CSS global baru di view | Tambahkan di layout atau `@push('styles')` bila khusus halaman |
| Tombol ikon tanpa `title` | Tambahkan `title="Hapus"` |

## Pengecualian yang Diketahui

Sidebar di `layouts/app.blade.php` menjalankan query langsung untuk badge jumlah arsip di Sampah (`Arsip::onlyTrashed()->count()`). Ini jalan pintas yang diketahui, **bukan contoh yang ditiru**. Kalau menyentuh bagian itu, pindahkan ke view composer atau data yang dibagikan dari satu tempat. Status Favorit untuk halaman arsip disiapkan controller melalui `withExists`; jangan menjalankan query relasi pin dari Blade. Keputusan fitur ada di `docs/decisions.md`.

## Checklist Sebelum Selesai

1. Memakai layout, section `title` dan `content`, dan kelas yang sudah ada.
2. Semua `td` punya `data-label`, baris kosong memakai `colspan`.
3. Form memakai `@csrf`, `old()`, `@error`, dan `enctype` bila ada file.
4. Aksi merusak punya konfirmasi berbahasa Indonesia.
5. Tombol ikon punya `title`. Teks UI bahasa Indonesia.
6. Dicek di HP (tanpa geser horizontal) dan, bila relevan, di tampilan cetak.
7. `php artisan view:cache` bersih, lalu `php artisan view:clear`.
