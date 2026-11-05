# Skema Database

Dokumen ini merangkum struktur yang dibuat oleh migration di
`database/migrations/`. Migration tetap menjadi sumber definisi lengkap.

Urutan inti: `users` dibuat lebih dahulu, lalu `kategoris` dan `activity_logs`,
kemudian `arsips`, penambahan `file_disk`, dan terakhir pivot `arsip_pin`.
Urutan kategori sebelum arsip diperlukan oleh foreign key `arsips.kategori_id`.

## Tabel aplikasi

### `users`

- **Primary key:** `id`.
- Kolom: `nama`, `username` (unik), `password`, `role` (`user` atau `admin`,
  default `user`), `remember_token`, `created_at`, `updated_at`.
- Dipakai untuk akun dan autentikasi. Relasi Eloquent: `arsips()` melalui
  `arsips.created_by`, `activityLogs()` melalui `activity_logs.user_id`, dan
  `pins()` many-to-many melalui `arsip_pin`.

### `kategoris`

- **Primary key:** `id`.
- Kolom: `nama` (unik), `created_at`, `updated_at`.
- Menyimpan kategori arsip. Relasi `arsips()` menuju `arsips.kategori_id`.

### `arsips`

- **Primary key:** `id`.
- Kolom: `nomor_arsip` (unik), `judul`, `kategori_id`, `tanggal_arsip`,
  `deskripsi` (nullable), `file_path` (nullable), `file_disk` (default
  `public`), `created_by`, `deleted_at`, `created_at`, `updated_at`.
- Foreign key `kategori_id` ke `kategoris.id`; `created_by` ke `users.id`.
  Kedua foreign key tidak menetapkan cascade di migration.
- Menyimpan metadata arsip. Model memakai soft delete. Relasi `kategori()` ke
  `Kategori`, `pembuat()` ke `User`, dan `dipinOleh()` many-to-many melalui
  `arsip_pin`.

### `activity_logs`

- **Primary key:** `id`.
- Kolom: `user_id`, `aksi`, `deskripsi`, `created_at`, `updated_at`.
- Foreign key `user_id` ke `users.id`; migration tidak menetapkan cascade.
- Mencatat aksi aplikasi. Relasi `user()` ke `User`. Pin/unpin Favorit tidak
  dicatat sebagai activity log.

### `arsip_pin`

- **Primary key gabungan:** `(user_id, arsip_id)`, sehingga satu user hanya
  memiliki satu pin untuk arsip yang sama.
- Kolom: `user_id`, `arsip_id`, `created_at` dengan default waktu database;
  tidak ada `id` maupun `updated_at`.
- Foreign key `user_id` ke `users.id` dan `arsip_id` ke `arsips.id`, keduanya
  memakai `ON DELETE CASCADE`.
- Menyimpan Favorit privat per akun. Soft delete arsip mempertahankan pivot;
  restore membuat arsip kembali muncul dalam query relasi normal; force delete
  arsip dan penghapusan akun menghapus pivot melalui cascade.

## Tabel Laravel/framework

Migration project juga membuat tabel berikut:

| Tabel | Primary key dan kolom utama | Fungsi / relasi |
|---|---|---|
| `password_reset_tokens` | `email` sebagai primary key; `token`, `created_at` nullable | Penyimpanan token reset bawaan migration Laravel; tidak ada foreign key ke `users`. |
| `failed_jobs` | `id`; `uuid` unik, `connection`, `queue`, `payload`, `exception`, `failed_at` | Menyimpan job queue yang gagal. |
| `personal_access_tokens` | `id`; pasangan morph `tokenable_type`/`tokenable_id`, `name`, `token` unik, `abilities`, `last_used_at`, `expires_at`, timestamps | Token akses personal dengan relasi polimorfik; tidak ada foreign key biasa. |

Migration tidak membuat tabel `jobs` atau `job_batches`.
