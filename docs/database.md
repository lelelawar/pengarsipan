# Skema Database

Migration di `database/migrations/` adalah sumber struktur database aktual.
Dokumen ini mencatat tabel Pin/Favorit yang ditambahkan; dokumentasi tabel lain
belum tersedia pada workspace ini.

## `arsip_pin`

Pivot privat antara `users` dan `arsips`:

| Kolom | Ketentuan |
|---|---|
| `user_id` | Foreign key ke `users.id`, cascade saat akun dihapus |
| `arsip_id` | Foreign key ke `arsips.id`, cascade saat arsip dihapus permanen |
| `created_at` | Waktu pin dibuat, default waktu database |

Primary key gabungan `(user_id, arsip_id)` mencegah akun mem-pin arsip yang sama
lebih dari sekali. Tidak ada `updated_at`. Soft delete arsip tidak menghapus
baris pivot; penghapusan permanen menjalankan cascade foreign key.
