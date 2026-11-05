# AGENTS.md

Panduan singkat untuk AI coding agent di proyek **Pengarsipan** (web arsip digital internal, Laravel 10). File ini berisi aturan kerja dan batasan. Fakta lengkap ada di `docs/` (lihat peta di bawah), jangan menyalinnya ke sini.

## Stack dan Lingkungan

- Laravel 10, PHP 8.3 (minimal 8.1), MySQL. Frontend: Blade + Bootstrap 5.3.3 + Bootstrap Icons lewat CDN. **Tanpa build step**, jangan menambah dependensi npm tanpa alasan kuat.
- Lokal: Laragon di Windows, `http://pengarsipan.test`, database `pengarsipan` (user `root`, tanpa password). Shell **PowerShell**: pakai `Rename-Item`, `Remove-Item`, `dir`, `findstr`, bukan perintah bash.
- Zona waktu `Asia/Jakarta`. Semua teks UI, pesan validasi, dan istilah domain di kode dalam **bahasa Indonesia** (`arsip`, `kategori`, `sampah`).

## Perintah

```powershell
php artisan migrate --seed          # tabel + akun dan kategori awal
php artisan storage:link
php artisan optimize:clear          # bersihkan cache
php artisan route:list --name=arsip
php artisan view:cache; php artisan view:clear   # cek semua view valid
php artisan arsip:bersihkan-sampah
```

**Jangan** menjalankan `migrate:fresh` atau hapus permanen massal tanpa konfirmasi pemilik proyek (menghapus data). Ubah skema lewat migration **baru**, jangan edit migration lama.

## Peta Dokumentasi (satu fakta, satu rumah)

| Butuh tahu | Baca |
|---|---|
| Instalasi, akun awal | `README.md` |
| Struktur kode, alur request, otorisasi, penyimpanan file | `docs/architecture.md` |
| Tabel, kolom, relasi, urutan migration, aturan integritas | `docs/database.md` |
| Fitur, aturan validasi, **matriks hak akses**, rencana fitur | `docs/features.md` |
| **Cara menulis kode backend** (controller, query, log, migration) | `docs/guidelines/backend.md` |
| **Cara menulis kode frontend** (Blade, kelas CSS, tabel, form, responsif) | `docs/guidelines/frontend.md` |
| **Keamanan, upload, akses dan penghapusan file** | `docs/guidelines/security-and-storage.md` |
| Alasan di balik keputusan, hal yang sudah ditolak | `docs/decisions.md` |
| Rencana dan hasil uji | `docs/testing.md` |

Dokumen `backup-and-recovery.md`, `troubleshooting.md`, dan `history.md` belum
tersedia di workspace ini; jangan merujuknya sebagai sumber fakta sampai dibuat.

## Sebelum Menulis Kode

Baca guidelines yang sesuai dengan pekerjaan, dan ikuti polanya:
- Controller, model, query, migration, log aktivitas: `docs/guidelines/backend.md`
- View Blade, CSS, tabel, form, tampilan HP dan cetak: `docs/guidelines/frontend.md`
- Upload, akses file, hak akses, input pengguna, penghapusan data: `docs/guidelines/security-and-storage.md`

## Aturan yang Tidak Boleh Dilanggar

1. **Hak akses** (2 role: `admin`, `user`) mengikuti matriks di `docs/features.md`. Jangan mengubahnya tanpa persetujuan. Inti: User bisa lihat, upload, dan edit **miliknya sendiri**; hapus arsip, kelola kategori dan akun, Sampah, serta Log Aktivitas khusus Admin.
2. **Otorisasi harus di server:** route (`role:admin`) dan `ArsipPolicy` lewat `$this->authorize()`. Menyembunyikan tombol di view bukan pengamanan.
3. **File arsip hanya diakses lewat** `ArsipController@preview` dan `@download`. Jangan membuat link langsung `asset('storage/...')` ke file arsip.
4. **Format file** punya satu sumber: `Arsip::JENIS` (PDF dan gambar). Jangan menambah format di tempat lain.
5. **Log aktivitas:** `ActivityLog::scopeArsipSaja()` menyaring berdasarkan awalan kalimat deskripsi. Kalau mengubah atau menambah kalimat log arsip, ubah scope itu juga.
6. **Favorit (pin)** bersifat pribadi per akun (tabel `arsip_pin`): tidak mengubah arsip dan tidak dicatat di log aktivitas.
7. `nomor_arsip` unik termasuk arsip di Sampah. Urutan migration `kategoris` sebelum `arsips` tidak boleh berubah.

## Fitur yang Sengaja Tidak Ada

Jangan ditambahkan tanpa diminta: export PDF/Excel dari laporan, upload Word/Excel/ZIP, registrasi publik atau reset password lewat email, hak akses per folder, hapus arsip oleh role User. Alasan dan riwayatnya ada di `docs/decisions.md`. Kalau ada permintaan yang bertabrakan dengan keputusan berstatus "Berlaku", **tanyakan dulu** sebelum mengubah.

## Aturan Dokumentasi

- Sebelum memperbarui dokumen, **baca kode aktual** (routes, controller, model, migration). Jangan menulis berdasarkan asumsi atau ingatan.
- Setiap perubahan fitur, tabel, atau hak akses harus diikuti pembaruan dokumen terkait di `docs/`. Kalau pola kode berubah, perbarui guidelines yang bersangkutan.
- Keputusan baru dicatat di `docs/decisions.md`. Masalah baru yang berulang dicatat di `docs/troubleshooting.md`.
- Tulis fakta di satu tempat saja. Di dokumen lain cukup menautkan.

## Definisi Selesai

1. `php artisan route:list` tanpa error, dan `php artisan view:cache` lalu `php artisan view:clear` bersih.
2. Fitur yang disentuh sudah diuji manual (skenario di `docs/testing.md`), minimal sebagai Admin dan sebagai User.
3. Dokumen terkait diperbarui (termasuk `docs/decisions.md` bila ada keputusan baru).
4. Tidak ada perubahan di luar permintaan.

## Utang Teknis yang Diketahui

- Status Private Storage terakhir diverifikasi 5 November 2026: 5 dari 5 record memakai `arsip_private`; 3 salinan fisik lama masih ada di disk `public`. GET dan HEAD URL arsip langsung menghasilkan 403 pada host Laragon yang diuji. Detail dan batas cakupan uji ada di `docs/guidelines/security-and-storage.md`; jangan menganggap konfigurasi web server lain sudah terlindungi.
- Cek `RouteServiceProvider::HOME`: sebaiknya `'/dashboard'`, supaya user yang sudah login lalu membuka `/login` tidak kena 404.
- Tes otomatis tersedia di `tests/Feature` dan `tests/Unit`. Jalankan tes yang memakai `RefreshDatabase` dengan SQLite in-memory sesuai `docs/testing.md`; jangan arahkan tes ke database pengembangan.
- Warning kuning di VS Code (`__blade_extends`, `withQueryString()`) adalah false positive ekstensi PHP, abaikan.

## Cara Bekerja dengan Pemilik Proyek

- Komunikasi pakai **bahasa Indonesia santai** (gaya bahasa gaul Jakarta). Kerjakan **bertahap** dan jelaskan singkat apa yang diubah.
- Kalau sebuah file diganti seluruhnya, berikan **kode lengkap** file itu beserta path-nya.
- Kalau langkah butuh **file contoh untuk tes** (misalnya upload PDF atau gambar), kasih tahu **di awal**.
