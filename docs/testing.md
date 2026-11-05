# Pengujian

## Isolasi database

`phpunit.xml` tidak menetapkan `DB_CONNECTION` atau `DB_DATABASE`. Sebelum
menjalankan suite yang memakai `RefreshDatabase`, set lingkungan PowerShell ke
SQLite in-memory:

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = ':memory:'
php artisan test
```

Dengan konfigurasi tersebut, skema tes hanya hidup di SQLite in-memory selama
proses pengujian. Perintah tidak memakai database pengembangan dan tidak
mengubah `.env` atau `phpunit.xml`.

Pemeriksaan Blade dan route yang tidak memerlukan database:

```powershell
php artisan view:cache
php artisan view:clear
php artisan route:list
```

## Cakupan tes

| File | Cakupan utama |
|---|---|
| `tests/Feature/ExampleTest.php` | Root mengarahkan ke dashboard; dashboard mengarahkan guest ke login. |
| `tests/Feature/ArsipPinRelationsTest.php` | Struktur pivot, relasi, duplikasi, cascade, soft delete. |
| `tests/Feature/FavoritControllerTest.php` | Guest, Admin/User, privasi pemilik, manipulasi `user_id`, pin/unpin, Sampah/restore, hak arsip, Activity Log. |
| `tests/Feature/FavoritUiTest.php` | Render halaman, privasi, empty state, form Pin/Unpin, pagination, pencarian. |
| `tests/Feature/PrivateStorageArsipTest.php` | Upload, preview/download, edit file, siklus Sampah, migrasi storage, dashboard. |
| `tests/Feature/UserDeletionRelationsTest.php` | Relasi akun, larangan penghapusan, izin penghapusan, akun sendiri. |
| `tests/Unit/ExampleTest.php` | Assertion dasar PHPUnit. |

## Hasil terakhir

Pada **5 November 2026**, seluruh suite menghasilkan **38 tes lulus dan 169
assertion**, tanpa tes gagal. `php artisan view:cache` dan
`php artisan view:clear` berhasil; `php artisan route:list` juga berhasil dan
menampilkan 38 route.
