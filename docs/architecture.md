# Arsitektur Aplikasi

Pengarsipan adalah aplikasi internal Laravel 10 dengan route web, controller,
Eloquent model, policy, dan Blade. UI memakai Bootstrap dan Bootstrap Icons
melalui CDN; tidak ada proses build frontend. Detail tabel ada di
`docs/database.md`, hak akses dan perilaku fitur ada di `docs/features.md`,
serta aturan penyimpanan file ada di `docs/guidelines/security-and-storage.md`.

## Alur request

1. `routes/web.php` mendaftarkan login di middleware `guest`; route aplikasi
   berada di middleware `auth`; area akun, kategori, Sampah, dan log memakai
   tambahan `role:admin`.
2. Route root mengarahkan ke route dashboard. Dashboard dan halaman aplikasi
   membutuhkan autentikasi.
3. Controller memvalidasi input dan menjalankan query atau perubahan lewat
   Eloquent. `ArsipPolicy` menjadi otorisasi per arsip; route model binding
   mengabaikan arsip soft-delete untuk route arsip biasa.
4. Controller mengirim data ke Blade. View memakai `layouts.app`, dan daftar
   menggunakan pagination Bootstrap.

## Area kode utama

| Area | Implementasi |
|---|---|
| Autentikasi | `AuthController`, middleware `auth`/`guest`, model `User` |
| Arsip | `ArsipController`, model `Arsip`, `ArsipPolicy`, `resources/views/arsip` |
| Favorit | `FavoritController`, relasi `User::pins()` dan `Arsip::dipinOleh()`, tabel `arsip_pin`, `resources/views/favorit` |
| Kategori dan akun | `KategoriController`, `UserController`, middleware `role:admin` |
| Sampah | `SampahController`, soft delete `Arsip`, perintah `arsip:bersihkan-sampah` |
| Laporan dan aktivitas | `LaporanController`, `LogController`, model `ActivityLog` |
| UI bersama | `resources/views/layouts/app.blade.php`, Bootstrap CDN |

Keputusan proyek saat ini menempatkan logika bisnis di controller dan tidak
menggunakan service layer. Log perubahan arsip, akun, dan kategori dicatat
melalui `ActivityLog`; pin/unpin Favorit tidak dicatat.

## Penyimpanan arsip

File baru disimpan di disk privat dan dilayani melalui
`ArsipController@preview` atau `ArsipController@download`. Record menyimpan disk
dan path file. Saat arsip di-soft-delete file tetap disimpan; force delete
menghapus file lewat event model. Detail konfigurasi, verifikasi akses, dan
batas hasil audit web server tercatat di guideline keamanan.

## Relasi utama

- `Kategori::arsips()` satu-ke-banyak ke arsip.
- `User::arsips()` melalui `created_by`; `User::activityLogs()` melalui `user_id`.
- `User::pins()` dan `Arsip::dipinOleh()` many-to-many melalui `arsip_pin`.
- `Arsip::kategori()` dan `Arsip::pembuat()` adalah relasi belongs-to.

`RouteServiceProvider::HOME` saat ini bernilai `/home`, sementara route yang
tersedia bernama `dashboard` di `/dashboard`. Perilaku ini dipertahankan dalam
dokumentasi; penyesuaiannya merupakan pekerjaan terpisah.
