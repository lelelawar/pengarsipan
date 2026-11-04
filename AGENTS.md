# AGENTS.md

Panduan untuk AI coding agent dan kontributor di proyek **Pengarsipan**: web arsip digital internal untuk tim kecil (upload, cari, kelola, dan cetak arsip). Baca file ini sebelum mengubah kode.

## Stack dan Lingkungan

- **Backend:** Laravel 10, PHP 8.3 (minimal 8.1), MySQL
- **Frontend:** Blade + Bootstrap 5.3.3 + Bootstrap Icons + font Inter, semuanya lewat CDN. **Tidak ada build step** (Vite bawaan Laravel tidak dipakai, jangan menambah dependensi npm tanpa alasan kuat).
- **Lokal:** Laragon di Windows, alamat `http://pengarsipan.test`, database `pengarsipan` (user `root`, tanpa password). Shell yang dipakai PowerShell, jadi gunakan perintah PowerShell (`Rename-Item`, `Remove-Item`, `dir`, `findstr`), bukan perintah bash.
- **Zona waktu:** `Asia/Jakarta` (di `config/app.php`). Format tanggal pakai `->locale('id')->translatedFormat(...)`.
- **Bahasa:** semua teks UI, pesan validasi, dan pesan flash dalam **bahasa Indonesia**. Istilah domain di kode juga bahasa Indonesia (`arsip`, `kategori`, `nomor_arsip`, `sampah`).

## Perintah Umum (PowerShell)

```powershell
composer install
copy .env.example .env          # isi DB_DATABASE=pengarsipan lalu:
php artisan key:generate
php artisan migrate --seed      # buat tabel + akun dan kategori awal
php artisan storage:link        # wajib, supaya folder storage terbaca
php artisan serve               # atau buka http://pengarsipan.test lewat Laragon
```

Perintah rutin:

```powershell
php artisan optimize:clear                 # bersihkan cache config/route/view
php artisan route:list --name=arsip        # cek route
php artisan view:cache; php artisan view:clear   # cek semua view Blade valid
php artisan arsip:bersihkan-sampah         # hapus permanen arsip di Sampah > 30 hari
php artisan arsip:migrasikan-ke-private --dry-run # audit migrasi file tanpa menulis
php artisan arsip:migrasikan-ke-private --rollback --dry-run # audit rencana rollback
```

Gunakan `php artisan arsip:migrasikan-ke-private --apply` hanya setelah dry-run ditinjau dan jadwal pemeliharaan disetujui. Command menyalin dan memverifikasi file, lalu mengalihkan `file_disk`; file sumber di disk `public` **tidak dihapus**. Rollback diperiksa dengan `--rollback --dry-run` dan dijalankan dengan `--rollback --apply`; jika public berisi versi lama, rollback menyalin versi privat terbaru ke path public baru agar sumber lama tetap utuh.

**Hati-hati:** `php artisan migrate:fresh --seed` menghapus **semua data dan tabel**. Hanya jalankan kalau pemilik proyek setuju. Untuk menambah tabel atau kolom, buat migration baru lalu `php artisan migrate`.

Pengaturan `php.ini` (Laragon: Menu, PHP, php.ini): `upload_max_filesize` dan `post_max_size` minimal `12M` karena batas upload 10 MB.

Akun awal dari seeder (khusus development, **wajib diganti** sebelum dipakai sungguhan):

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| User | `user` | `user123` |

Kategori awal: Surat Masuk, Surat Keluar, Laporan, Lainnya.

## Peran dan Hak Akses

Hanya ada 2 role (kolom `users.role`: `admin` atau `user`). Tidak ada registrasi publik, akun hanya dibuat admin. **Jangan mengubah aturan ini tanpa persetujuan pemilik proyek.**

| Aksi | Admin | User |
|---|---|---|
| Login, dashboard, lihat daftar dan detail arsip | Ya | Ya |
| Pratinjau, unduh, cetak | Ya | Ya |
| Upload arsip baru | Ya | Ya |
| Edit arsip | Semua arsip | Hanya arsip miliknya (`created_by`) |
| Hapus arsip (soft delete ke Sampah) | Ya | **Tidak** |
| Laporan (filter dan cetak) | Ya | Ya |
| Kelola akun, kategori | Ya | Tidak |
| Sampah (pulihkan, hapus permanen, kosongkan) | Ya | Tidak |
| Halaman Log Aktivitas lengkap | Ya | Tidak |

Cara penegakannya:
- Middleware `role:admin` (alias di `app/Http/Kernel.php`, `$middlewareAliases`) membungkus route admin di `routes/web.php`.
- Aturan per arsip ada di `app/Policies/ArsipPolicy.php` (`update`: admin atau pemilik, `delete`: admin saja). Controller memakai `$this->authorize(...)`, view memakai `@can`.
- Menyembunyikan tombol di view **bukan** pengamanan. Pengecekan harus selalu ada di route atau controller.

## Struktur Kode

| Lokasi | Isi |
|---|---|
| `app/Models/` | `User`, `Arsip`, `Kategori`, `Tag`, `ActivityLog` |
| `app/Policies/ArsipPolicy.php` | Hak akses edit dan hapus arsip |
| `app/Http/Middleware/RoleMiddleware.php` | Cek role (`role:admin`) |
| `app/Http/Controllers/` | `AuthController`, `DashboardController`, `ArsipController`, `UserController` (menu Akun), `KategoriController`, `LaporanController`, `LogController`, `SampahController` |
| `app/Console/Commands/BersihkanSampahArsip.php` | Command `arsip:bersihkan-sampah` |
| `app/Console/Commands/MigrasikanArsipKePrivate.php` | Copy, verifikasi, alih file arsip, dan rollback tanpa menghapus sumber |
| `resources/views/layouts/app.blade.php` | Layout utama (sidebar, header, flash message, semua CSS global) |
| `resources/views/{auth,arsip,akun,kategori,laporan,log,sampah}/` | View per modul |
| `routes/web.php` | Semua route |

Nama route memakai bahasa Indonesia: `arsip.*`, `akun.*`, `kategori.*`, `sampah.*`, `laporan.index`, `log.index`, `dashboard`, `login`, `logout`.

Catatan route:
- `arsip.preview` dan `arsip.download` didaftarkan **sebelum** `Route::resource('arsip', ...)`.
- `akun` memakai `->parameters(['akun' => 'user'])` dan `->except(['show'])`. `kategori` hanya `index/store/update/destroy`.
- Route Sampah memakai `{id}` (bukan route model binding) karena arsip yang dihapus tidak ikut binding default. Pakai `Arsip::onlyTrashed()->findOrFail($id)`.

## Model Data

- **users**: `nama`, `username` (unik), `password` (di-hash lewat cast `hashed`), `role`. Helper: `isAdmin()`.
- **kategoris**: `nama` (unik).
- **arsips**: `nomor_arsip` (unik), `judul`, `kategori_id`, `tanggal_arsip`, `deskripsi`, `file_path`, `file_disk`, `created_by`, `softDeletes`. Relasi: `kategori()`, `pembuat()`, `tags()`.
- **tags** dan pivot **arsip_tag**: tag dinormalisasi (huruf kecil, spasi jadi `-`, tanpa `#`, maksimal 30 karakter, maksimal 10 tag per arsip). Disimpan lewat `ArsipController::sinkronTag()`.
- **activity_logs**: `user_id`, `aksi`, `deskripsi`.

Hal yang sering terlewat:
- `nomor_arsip` unik **termasuk arsip di Sampah**. Nomor baru bisa dipakai lagi setelah arsip lama dihapus permanen atau dipulihkan.
- File migration `arsips` bertimestamp `2026_10_22_235900` (sengaja diubah) supaya jalan **setelah** `kategoris`. Jangan mengubah urutan ini.
- Kategori tidak boleh dihapus kalau masih dipakai arsip (termasuk yang di Sampah, pakai `withTrashed()`).
- Akun tidak boleh dihapus kalau sudah punya arsip atau riwayat aktivitas, admin tidak boleh menghapus atau menurunkan role akunnya sendiri.

## File Arsip

- **Format yang boleh:** PDF dan gambar (`jpg`, `jpeg`, `png`, `gif`, `webp`). Maksimal **10 MB**. Format lain (Word, Excel, ZIP) sengaja tidak didukung.
- Daftar format ada di konstanta `Arsip::JENIS`. Validasi memakai closure yang mengecek ekstensi nama file terhadap `Arsip::semuaEkstensi()` (bukan rule `mimes`).
- File baru disimpan di disk `arsip_private`, root `storage/app/private` (di luar public web root), folder `arsip/`, memakai **nama acak (UUID)** dan ekstensi yang diizinkan (`ArsipController::simpanFile()`). Kolom `file_disk` menentukan lokasi file per record; record lama bernilai `public` sampai berhasil dimigrasikan.
- Nilai `file_disk` hanya boleh `public` atau `arsip_private`. Model `Arsip::diskArsip()` menjadi resolver lokasi file; operasi upload, preview, unduh, ganti file, dan force delete harus menggunakan disk sesuai record.
- Akses file hanya lewat `ArsipController@preview` dan `@download` (wajib login, dicek policy `view`). Respons memakai header `X-Content-Type-Options: nosniff`. Jangan membuat link langsung `asset('storage/...')` ke file arsip di view.
- Accessor di `Arsip`: `ekstensi`, `jenis_file`, `ikon`, `warna_ikon`, `bisa_pratinjau`. Scope: `jenis()`.
- Mengganti file saat edit menyimpan file baru ke private disk, memperbarui record dalam transaksi, lalu menghapus file lama setelah transaksi berhasil. Jika transaksi gagal, file lama tetap direferensikan dan file baru dibersihkan. Menghapus arsip hanya **soft delete** (file tetap ada). Hapus permanen (`forceDelete`) menghapus file pada disk yang tercatat lewat event `forceDeleted` di `Arsip::booted()`.
- Upload memakai area drag and drop **satu file per arsip** (di `resources/views/arsip/form.blade.php`).

## Sampah (Trash)

- Arsip yang dihapus masuk Sampah selama `Arsip::MASA_SAMPAH_HARI` (30 hari), lalu dihapus permanen oleh `Arsip::bersihkanSampahKedaluwarsa()`.
- Pembersihan jalan otomatis setiap admin membuka halaman Sampah, dan bisa dijalankan manual lewat `php artisan arsip:bersihkan-sampah`. Penjadwalan harian lewat `app/Console/Kernel.php` opsional.

## Log Aktivitas

Catat setiap aksi penting dengan `ActivityLog::catat($aksi, $deskripsi)`.

- Nilai `aksi` yang dipakai: `tambah`, `edit`, `hapus`, `pulihkan`, `permanen`.
- Anggota biasa melihat panel aktivitas di dashboard yang **hanya berisi aktivitas arsip**, disaring oleh `ActivityLog::scopeArsipSaja()` berdasarkan **awalan deskripsi** (`Menambah arsip`, `Mengubah arsip`, `Menghapus arsip`, `Memulihkan arsip`). Kalau menambah jenis log arsip baru atau mengubah kalimat deskripsi, **update pola di scope itu**.
- Aktivitas akun dan kategori hanya terlihat admin.

## Konvensi UI

- Semua halaman memakai `@extends('layouts.app')` dengan `@section('title')` dan `@section('content')`. CSS atau JS khusus halaman lewat `@push('styles')` dan `@push('scripts')`.
- Kelas yang sudah ada dan sebaiknya dipakai ulang: `card-soft`, `table-cards`, `btn-mobile-full`, `form-actions`, `no-print`, `tag-chip`, `stat-card`, `hero`. Warna utama indigo (`#4f46e5`).
- **Tabel responsif:** tabel diberi kelas `table table-cards` dan **setiap `<td>` wajib punya `data-label`** supaya tampil sebagai kartu di HP. Baris kosong memakai `<td colspan>` tanpa `data-label`.
- Pesan sukses dan error lewat `session('success')` dan `session('error')` (sudah ditampilkan di layout).
- Pagination memakai Bootstrap 5 (`Paginator::useBootstrapFive()` di `AppServiceProvider`). Selalu tambahkan `->withQueryString()` supaya filter tidak hilang saat pindah halaman.
- Form partial bernama `form.blade.php` (**tanpa underscore**), dipanggil `@include('arsip.form')` dan `@include('akun.form')`. Jangan diganti `_form` tanpa mengubah semua `@include`.
- Layout: sidebar jadi drawer di bawah 992 px, tabel jadi kartu di bawah 768 px. Cetak memakai `window.print()` dan elemen non-cetak diberi `no-print`.
- Teks tombol, label, dan pesan pakai bahasa Indonesia yang singkat dan jelas.

## Fitur Laporan

Halaman Laporan (`LaporanController@index`, semua user login) punya filter tanggal, kategori, pembuat, dan kata kunci, ringkasan per kategori, serta tombol **Cetak** dari browser. Arsip yang ada di Sampah tidak ikut dihitung.

## Fitur yang Sengaja Tidak Ada

Jangan ditambahkan tanpa diminta:
- Export PDF/Excel dari laporan (dibatalkan, cukup filter dan cetak). Paket `barryvdh/laravel-dompdf` dan `maatwebsite/excel` tidak dipakai.
- Upload format Word, Excel, atau ZIP.
- Registrasi publik atau reset password lewat email.
- Hak akses per folder atau per dokumen.
- Hapus arsip oleh role `user`.

## Status dan Roadmap

Sudah dikerjakan: login dan role, dashboard, CRUD arsip, kategori, kelola akun, log aktivitas, laporan (filter dan cetak), pratinjau dan unduh, upload drag and drop (PDF dan gambar), filter format dan tanggal unggah, search bar di header, widget penyimpanan, Sampah dengan auto-hapus 30 hari, tagging, UI responsif.

Rencana berikutnya (urut):
1. **Fase D:** Pin / akses cepat (bintang di arsip, widget favorit di dashboard)
2. **Fase E:** Folder hierarkis dan navigasi folder di sidebar (kategori tetap ada sebagai filter)
3. **Fase F:** Riwayat versi dokumen (v1, v2, v3 pada satu arsip)
4. **Step 9, finishing:** halaman Profil Saya (ganti nama dan password sendiri), halaman error 403/404/419/500 yang rapi, batasi percobaan login, ganti akun default, `APP_DEBUG=false` untuk produksi, seeder data demo, panduan backup, checklist uji akhir

Fase B (Sampah) dan Fase C (tagging) sudah diimplementasi. Pengujian lengkapnya masih perlu dikonfirmasi pemilik proyek.

## Batasan dan Utang Teknis yang Diketahui

- File lama di disk `public` berpotensi tetap bisa dibuka lewat URL langsung `/storage/arsip/<uuid>.<ext>` selama salinan sumber dipertahankan untuk rollback. Periksa aturan web server sebelum menutup akses URL agar file publik non-arsip tidak ikut terdampak.
- Widget storage dashboard (kuota tampilan 1 GB masih konstanta di `DashboardController`) menghitung file yang dirujuk record, termasuk Sampah, lintas disk `public` dan `arsip_private`. Salinan migrasi yang belum direferensikan tidak menggandakan hitungan file arsip.
- File sumber public tidak dihapus oleh command migrasi. Rollback skema `file_disk` ditolak selama ada record yang menunjuk ke `arsip_private`; alihkan record ke `public` sebelum melepas kolom.
- Tes fitur private storage ada di `tests/Feature/PrivateStorageArsipTest.php` dan memakai `Storage::fake`.
- Warning kuning di VS Code seperti `__blade_extends`, `__blade_error`, `__blade_render`, dan `withQueryString()` adalah **false positive** dari ekstensi PHP, bukan bug. Untuk memastikan view valid jalankan `php artisan view:cache` lalu `php artisan view:clear`.

## Checklist Uji Manual

Setelah mengubah fitur, uji minimal:

- **Admin:** login, tambah/edit/hapus arsip, pulihkan dan hapus permanen dari Sampah (file fisik ikut hilang), kelola akun dan kategori, Log Aktivitas, Laporan dengan filter, cetak.
- **User:** login, upload arsip, edit arsip **milik sendiri** (milik orang lain harus 403), tidak ada tombol Hapus, menu admin tidak muncul, `/akun`, `/kategori`, `/log`, `/sampah` harus 403.
- **File:** PDF dan gambar berhasil, DOCX/XLSX/ZIP ditolak, file di atas 10 MB ditolak, pratinjau dan unduh jalan, akses tanpa login diarahkan ke halaman login.
- **Tag dan filter:** tag tersimpan dan ternormalisasi, filter tag, kategori, format, dan periode jalan, pencarian lewat search bar header ikut mencari tag.
- **Mobile:** sidebar jadi drawer, tabel jadi kartu, tombol Simpan dan Batal full-width.

## Cara Bekerja dengan Pemilik Proyek

- Komunikasi pakai **bahasa Indonesia santai** (gaya bahasa gaul Jakarta).
- Kerjakan **bertahap** (step by step) dan jelaskan singkat apa yang diubah.
- Kalau sebuah file diganti seluruhnya, berikan **kode lengkap** file itu (jangan potongan), dan sebutkan path-nya dengan jelas.
- Kalau suatu langkah butuh **file contoh untuk tes** (misalnya upload PDF atau gambar), kasih tahu **di awal** sebelum langkahnya.
- Kalau ada perubahan yang bertabrakan dengan keputusan sebelumnya (hak akses, format file, fitur yang sengaja tidak ada), **tanyakan dulu** sebelum mengubah.
- Jangan menjalankan perintah yang menghapus data (`migrate:fresh`, hapus permanen massal) tanpa konfirmasi.
