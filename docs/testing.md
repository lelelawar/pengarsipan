# Pengujian

Dokumen ini mencatat pengujian yang benar-benar dijalankan dan cara mengisolasi pengujian database dari database pengembangan.

## Isolasi database

`phpunit.xml` saat ini mengomentari `DB_CONNECTION` dan `DB_DATABASE`. Karena itu, jangan menjalankan tes yang memakai `RefreshDatabase` tanpa menetapkan database test terpisah. Pengujian relasi penghapusan akun dijalankan dengan SQLite in-memory dari PowerShell:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
php artisan test tests\Feature\UserDeletionRelationsTest.php
```

Dengan konfigurasi tersebut, `RefreshDatabase` membuat skema hanya di database SQLite in-memory selama proses tes. Pengujian tidak memakai database pengembangan dan tidak mengubah `.env` atau `phpunit.xml`.

## Relasi penghapusan akun

File: `tests/Feature/UserDeletionRelationsTest.php`

Tes mencakup:

- Relasi `User::arsips()` dan `User::activityLogs()` serta foreign key masing-masing.
- Deteksi arsip aktif dan arsip soft deleted melalui `withTrashed()`.
- Penolakan penghapusan akun yang memiliki arsip aktif atau di Sampah.
- Penolakan penghapusan akun yang memiliki activity log.
- Penghapusan akun tanpa arsip dan log, termasuk log penghapusan yang dicatat untuk admin.
- Penolakan admin menghapus akunnya sendiri.

Hasil pada **5 November 2026**: **6 tes lulus, 21 assertion**.

## Relasi Pin/Favorit

File: `tests/Feature/ArsipPinRelationsTest.php`

Tes mencakup pembuatan tabel pivot dan foreign key cascade, relasi kedua arah,
penolakan pin duplikat, cascade saat arsip dihapus permanen atau akun yang
diizinkan dihapus, serta retensi pivot saat arsip masuk Sampah.

Jalankan terisolasi dari database pengembangan:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
php artisan test tests\Feature\ArsipPinRelationsTest.php
```

Hasil pada **5 November 2026**: **7 tes lulus, 23 assertion**.

## Route dan controller Pin/Favorit

File: `tests/Feature/FavoritControllerTest.php`

Tes mencakup perlindungan guest, perilaku Admin dan User, isolasi pemilik pin,
request `user_id` yang diabaikan, pin berulang, unpin idempoten, arsip Sampah
dan pemulihan, hak edit/hapus arsip yang tetap, serta tidak adanya Activity Log.
Data daftar diperiksa dari hasil paginasi controller tanpa merender view; view
Favorit akan dibuat pada tahap UI.

Jalankan terisolasi dari database pengembangan:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
php artisan test tests\Feature\FavoritControllerTest.php
```

Hasil pada **5 November 2026**: **8 tes lulus, 40 assertion**.

## UI Pin/Favorit

File: `tests/Feature/FavoritUiTest.php`

Tes mencakup render halaman untuk Admin dan User dengan data terpisah, empty
state, route dan method form pin/unpin pada daftar dan detail arsip, pagination,
serta pencarian daftar Favorit.

Jalankan terisolasi dari database pengembangan:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
php artisan test tests\Feature\FavoritUiTest.php
```

Hasil pada **5 November 2026**: **5 tes lulus, 32 assertion**.

Pengujian gabungan Tahap 1–3 pada **5 November 2026**: **20 tes lulus,
95 assertion**. `php artisan view:cache` dan `php artisan view:clear` juga
berhasil dijalankan.
