# Guidelines Backend

**Tujuan:** semua kode backend baru (controller, model, query, migration, log) punya bentuk yang sama dengan kode yang sudah ada, supaya mudah dibaca dan tidak merusak fitur lain (terutama scope log aktivitas dan aturan hak akses).

Fakta sistem ada di `docs/architecture.md` dan `docs/database.md`. Dokumen ini hanya soal **cara menulis kode**.

## Wajib

- Satu aksi, satu urutan: **otorisasi, validasi, proses, catat log, redirect dengan pesan**.
- Validasi hanya lewat `$request->validate(...)` dengan **pesan bahasa Indonesia**. Pakai data hasil validasi, bukan `$request->all()`.
- Route baru milik arsip didaftarkan di `routes/web.php` **sebelum** `Route::resource('arsip', ...)`, di grup yang benar (`auth` untuk semua user, `role:admin` untuk khusus Admin).
- Eager loading (`with`) untuk relasi yang dipakai di view, dan `->paginate(n)->withQueryString()` untuk daftar yang punya filter.
- Skema database hanya diubah lewat **migration baru**.
- Setiap perubahan fitur, tabel, atau hak akses diikuti pembaruan `docs/`.

## Dilarang

- `$request->all()` atau mengisi model dari input mentah.
- Mengedit migration lama yang sudah dijalankan, atau menjalankan `migrate:fresh` tanpa konfirmasi pemilik proyek.
- Menghapus baris `arsips` lewat query builder massal (`DB::table('arsips')->delete()` atau `Arsip::query()->forceDelete()`). Itu melewati event `forceDeleted`, sehingga file fisiknya jadi yatim. Hapus lewat model, satu per satu.
- Mencatat aksi `pin` (Favorit) di log aktivitas.
- Menaruh logika bisnis di `routes/web.php` (selain redirect sederhana).
- Menulis role sebagai string di banyak tempat. Pakai `isAdmin()`, middleware `role:admin`, atau policy.

## Penamaan

| Hal | Aturan | Contoh |
|---|---|---|
| Route | Bahasa Indonesia, `modul.aksi` | `arsip.index`, `sampah.pulihkan`, `arsip.pin` |
| Controller | `NamaModulController`. Menu Akun dilayani `UserController` (route `akun`) | `ArsipController`, `SampahController` |
| Model | Tunggal, PascalCase | `Arsip`, `Kategori`, `ActivityLog` |
| Tabel | Jamak. Pivot memakai nama eksplisit di relasi bila tidak standar | `arsips`, `kategoris`, `arsip_pin` (rencana Favorit) |
| Variabel dan method buatan sendiri | Bahasa Indonesia, camelCase | `$arsipTerbaru`, `hapusPermanen()`, `bersihkanSampahKedaluwarsa()` |
| Method standar Laravel | Tetap Inggris | `index`, `store`, `update`, `destroy` |
| Pesan ke pengguna | Kalimat Indonesia diakhiri titik | `Arsip berhasil ditambahkan.` |

## Pola Controller

Contoh dari `ArsipController@store`:

```php
public function store(Request $request)
{
    $this->authorize('create', Arsip::class);                 // 1. otorisasi

    $data = $request->validate([                              // 2. validasi
        'nomor_arsip' => ['required', 'string', 'max:100', 'unique:arsips,nomor_arsip'],
        'judul'       => ['required', 'string', 'max:255'],
        'file'        => $this->aturanFile(true),
    ], $this->pesanFile());

    $data['created_by'] = auth()->id();                       // 3. proses
    $arsip = Arsip::create($data);

    ActivityLog::catat('tambah', "Menambah arsip {$arsip->nomor_arsip} - {$arsip->judul}"); // 4. log

    return redirect()->route('arsip.show', $arsip)            // 5. redirect + pesan
        ->with('success', 'Arsip berhasil ditambahkan.');
}
```

Catatan:
- Untuk update, unik diabaikan untuk baris sendiri: `Rule::unique('arsips', 'nomor_arsip')->ignore($arsip->id)`.
- Penolakan manual memakai `abort_unless(kondisi, 404, 'Pesan.')`, penolakan hak akses memakai `authorize()` (menghasilkan 403).
- Pesan sukses lewat `->with('success', ...)`, pesan gagal lewat `->with('error', ...)`. Layout menampilkan keduanya.

## Pola Query

```php
Arsip::with(['kategori', 'pembuat'])
    ->when($request->kategori_id, fn ($query, $id) => $query->where('kategori_id', $id))
    ->when($request->jenis, fn ($query, $jenis) => $query->jenis($jenis))   // scope di model
    ->latest()
    ->paginate(10)
    ->withQueryString();
```

- Filter opsional memakai `when()`, jangan `if` bertingkat.
- Logika yang dipakai ulang jadi **scope** atau **accessor** di model (`jenis()`, `arsipSaja()`, `ikon`, `bisa_pratinjau`).
- Untuk fitur penanda per pengguna yang dibuat kelak (misalnya Favorit), gunakan `withExists` untuk status seperti `dipin`; jumlah relasi memakai `withCount`.
- `Arsip::query()` otomatis mengecualikan arsip di Sampah. Pakai `onlyTrashed()` untuk halaman Sampah dan `withTrashed()` bila yang di Sampah juga harus dihitung (cek pemakaian kategori atau akun, keunikan nomor).
- Pencarian teks memakai `like` dengan parameter binding (`where('judul', 'like', "%{$cari}%")`). Jangan menyusun SQL mentah dari input.

## Model

- Selalu isi `$fillable` secara eksplisit. Tambahkan `$casts` bila perlu.
- `User::arsips()` adalah relasi `hasMany` ke `Arsip` dengan foreign key `created_by`.
- `User::activityLogs()` adalah relasi `hasMany` ke `ActivityLog` dengan foreign key `user_id`.
- Pemeriksaan penghapusan akun memakai kedua relasi tersebut. `arsips()->withTrashed()->exists()` sengaja ikut mendeteksi arsip soft deleted agar akun yang masih punya arsip aktif atau di Sampah tidak terhapus.
- Daftar tetap yang dipakai banyak tempat jadi **konstanta** (`Arsip::JENIS`, `Arsip::MASA_SAMPAH_HARI`), bukan angka atau array yang diulang.
- Helper tampilan sederhana (ikon, warna, label) boleh jadi accessor di model.
- Penghapusan file fisik saat hapus permanen ada di event `forceDeleted` di `Arsip::booted()`. Jangan menduplikasinya di controller.

## Log Aktivitas

Panggil `ActivityLog::catat($aksi, $deskripsi)` **setelah** aksi berhasil.

| Aksi | Kalimat baku | Terlihat anggota biasa |
|---|---|---|
| `tambah` | `Menambah arsip {nomor} - {judul}` | Ya |
| `edit` | `Mengubah arsip {nomor} - {judul}` | Ya |
| `hapus` | `Menghapus arsip {nomor} - {judul}` | Ya |
| `pulihkan` | `Memulihkan arsip {nomor} - {judul}` | Ya (pola ada di scope) |
| `permanen` | `Menghapus permanen arsip ...` atau `Mengosongkan Sampah (...)` | Tidak |
| akun dan kategori | `Menambah akun ...`, `Mengubah kategori ...`, dan seterusnya | Tidak |

Scope `ActivityLog::scopeArsipSaja()` mencocokkan **awalan** kalimat. Kalau menambah jenis log arsip yang harus terlihat anggota, tambahkan polanya di scope itu. Kalau mengubah kalimat baku, ubah scope dan dokumen ini bersamaan. Aksi baru di halaman Log juga perlu ditambah ke filter dan warna di `log/index.blade.php` serta `dashboard.blade.php`.

## Migration

- Buat dengan `php artisan make:migration nama_perubahan`, jalankan dengan `php artisan migrate`.
- Migration yang punya foreign key harus bertimestamp **lebih baru** dari tabel yang dirujuk (contoh: `arsips` setelah `kategoris`).
- Kolom baru di tabel yang sudah berisi data: buat `nullable` atau beri default.
- Tabel pivot memakai primary key gabungan dan `cascadeOnDelete()`.
- Setelah migration: perbarui model (`$fillable`, relasi), `docs/database.md`, dan backup dulu bila datanya penting.

## Gaya Kode

- Ikuti PSR-12. Kalau Laravel Pint terpasang (cek `composer.json`), jalankan `vendor\bin\pint` pada file yang diubah.
- Method helper di controller bersifat `private` dan diberi tipe parameter dan return (`private function namaFile(Arsip $arsip): string`).
- Closure pendek (`fn`) untuk transformasi kecil. Method terpisah untuk logika lebih dari beberapa baris.
- Komentar pendek berbahasa Indonesia yang menjelaskan **alasan**, bukan mengulang kode.

## Anti-pattern

| Salah | Benar |
|---|---|
| `Arsip::create($request->all())` | `Arsip::create($data)` dengan `$data = $request->validate([...])` |
| `if ($user->role == 'admin')` di banyak tempat | `$user->isAdmin()`, `role:admin`, atau policy |
| `Arsip::onlyTrashed()->forceDelete()` | Loop model lalu `$arsip->forceDelete()` (file ikut terhapus) |
| Mengirim pesan error bahasa Inggris | Pesan Indonesia lewat array pesan validasi |
| Mengubah migration lama | Migration baru |
| Menambah kalimat log baru tanpa cek `arsipSaja()` | Sesuaikan scope dan filter log |

## Checklist Sebelum Selesai

1. Route ada di grup yang benar dan terdaftar sebelum `resource` bila milik arsip.
2. `authorize()` atau middleware role sudah dipasang.
3. Validasi memakai pesan Indonesia, tanpa `$request->all()`.
4. Log dicatat dengan kalimat baku dan scope dicek.
5. Redirect membawa pesan `success` atau `error`.
6. Relasi di-eager load, daftar memakai `paginate` dan `withQueryString`.
7. Kalau skema berubah: migration baru, `docs/database.md` diperbarui.
8. `php artisan route:list` dan `php artisan view:cache` bersih, diuji sebagai Admin dan sebagai User.
