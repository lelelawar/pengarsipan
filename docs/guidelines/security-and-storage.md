# Guidelines Keamanan dan Penyimpanan File

**Tujuan:** arsip adalah data internal. Dokumen ini menjaga agar hak akses, upload, akses file, dan penghapusan data selalu ditangani dengan cara yang aman dan konsisten.

Gambaran sistem ada di `docs/architecture.md`. Hak akses lengkap ada di `docs/features.md`. Dokumen ini berisi **aturan menulis kode**.

## Wajib

- Setiap endpoint baru ditempatkan di grup route yang benar (`auth` untuk semua user login, `role:admin` untuk khusus Admin), dan bila terkait satu data, dicek lewat **policy** (`$this->authorize()`).
- Aksi yang mengubah data memakai `POST`, `PUT`, `PATCH`, atau `DELETE` dengan `@csrf`, **bukan** `GET`.
- Data masuk divalidasi di server, dan hanya hasil validasi yang dipakai.
- File arsip hanya diakses lewat controller yang mengecek login dan policy.
- Setiap perubahan aturan hak akses atau penyimpanan file diperbarui di `docs/` dan dicatat di `docs/decisions.md`.

## Dilarang

- Mengandalkan tampilan (menyembunyikan tombol) sebagai pengamanan.
- `$request->all()` untuk mengisi model.
- `{!! !!}` untuk data dari pengguna.
- Link atau path langsung ke file arsip (`asset('storage/...')`, `/storage/arsip/...`).
- Memakai nama file dari pengguna sebagai nama atau path penyimpanan.
- Aksi yang mengubah data lewat `GET`.
- SQL mentah yang disusun dari input pengguna (`whereRaw` dengan variabel langsung).
- Menulis atau mencatat password (termasuk di log aktivitas atau pesan error).
- Meng-commit `.env`.

## Otorisasi

Dua lapis, dua-duanya harus ada:

1. **Route:** `role:admin` untuk halaman khusus Admin (`akun`, `kategori`, `sampah`, `log`).
2. **Policy:** `ArsipPolicy` untuk aturan per arsip (edit milik sendiri, hapus khusus Admin).

```php
public function update(Request $request, Arsip $arsip)
{
    $this->authorize('update', $arsip);   // 403 kalau bukan Admin dan bukan pemilik
    ...
}
```

Untuk model atau aturan baru yang bergantung pada kepemilikan, buat policy, jangan menulis `if` role di controller. Setiap endpoint baru diuji sebagai Admin, sebagai User, dan sebagai tamu (harus diarahkan ke login).

Favorit disimpan sebagai pivot, bukan record Favorit mandiri: endpoint arsip
membatasi baca/tulis pivot lewat relasi user yang sedang login dan memakai
`ArsipPolicy` untuk izin melihat arsip. Karena itu endpoint tidak menerima ID
pemilik pin; jangan memperluasnya menjadi akses ke pin akun lain.

### Favorit (Pin)

- `favorit.index`, `arsip.pin`, dan `arsip.unpin` berada di grup `auth`.
- Pemilik pivot selalu akun yang sedang login (`$request->user()` atau
  `auth()->user()`); jangan menerima `user_id` dari route atau form.
- Pin/unpin memeriksa hak `view` arsip yang sudah ada. `ArsipPolicy` tetap
  menentukan hak edit dan hapus; memiliki pin tidak memberi izin tambahan.
- Route model binding dan global scope `SoftDeletes` mengecualikan arsip yang
  sudah masuk Sampah. Relasi Favorit mengambil pivot milik akun itu saja.
- Gunakan POST untuk pin dan DELETE untuk unpin dengan CSRF. Aksi ini tidak
  menghasilkan Activity Log.

## Autentikasi dan Akun

- Login memakai username dan password lewat `Auth::attempt`. Setelah berhasil, `session()->regenerate()`. Saat logout, `invalidate()` dan `regenerateToken()`.
- Password diisi **teks biasa** ke model, karena cast `hashed` di `User` yang meng-hash. Jangan menghash manual lalu mengisinya lagi. `password` dan `remember_token` ada di `$hidden`.
- Admin tidak boleh menghapus atau menurunkan role akunnya sendiri. Akun yang sudah punya arsip atau riwayat aktivitas tidak boleh dihapus.
- Akun awal dari seeder (`admin123`, `user123`) **wajib diganti** sebelum dipakai sungguhan.
- Belum ada pembatasan percobaan login (rencana tahap finishing). Saat menambahnya, pakai middleware `throttle` pada route `login.process`.

## Input dan Output

- Isi model hanya dari `$data = $request->validate([...])`, dan `$fillable` selalu eksplisit. Buang kunci yang bukan kolom (contoh: `unset($data['file'])`).
- Tampilkan data pengguna dengan `{{ }}` (di-escape otomatis). `{!! !!}` hanya untuk HTML yang dibuat sendiri dan aman.
- Query memakai Eloquent atau parameter binding. Pencarian `like` memakai binding.

## Upload File

Aturan saat ini (sumber tunggal format: `Arsip::JENIS`):

| Aturan | Nilai |
|---|---|
| Format | PDF dan gambar: `pdf`, `jpg`, `jpeg`, `png`, `gif`, `webp` |
| Ukuran | Maksimal 10 MB (`max:10240`) |
| Validasi | Closure yang mengecek ekstensi nama file terhadap daftar yang diizinkan, ditambah `file` dan `max` |
| Nama di server | UUID acak + ekstensi tervalidasi, disimpan di disk `arsip_private` |
| Nama saat diunduh | Dibuat dari nomor dan judul (`Str::slug`), bukan nama asli file |

Catatan penting:
- Validasi bergantung pada **ekstensi** yang dikirim klien, bukan isi file. Karena itu keamanan tambahan ada di sisi penyajian: `Content-Type` ditentukan server dari ekstensi tervalidasi, dan header `X-Content-Type-Options: nosniff` selalu dikirim.
- Kalau memperketat validasi (misalnya memeriksa `getMimeType()` untuk PDF dan gambar), lakukan di `aturanFile()` dan uji dengan file asli, karena deteksi MIME bisa berbeda antar server.
- Menambah format baru: ubah `Arsip::JENIS`, tambah `MIME_PRATINJAU` bila bisa dipratinjau, dan perbarui teks petunjuk di form. Format baru butuh persetujuan (`docs/decisions.md`, keputusan 4).

## Akses File

Satu-satunya jalan ke file arsip:

```php
public function preview(Arsip $arsip)
{
    $this->authorize('view', $arsip);                  // wajib login + policy
    $this->pastikanFileAda($arsip);                    // 404 bila file tidak ada
    abort_unless($arsip->bisa_pratinjau, 404, '...');

    return Storage::disk($arsip->diskArsip())->response(
        $arsip->file_path,
        $this->namaFile($arsip),
        ['Content-Type' => self::MIME_PRATINJAU[$arsip->ekstensi] ?? 'application/octet-stream',
         'X-Content-Type-Options' => 'nosniff'],
        'inline'
    );
}
```

- Route `arsip.preview` dan `arsip.download` ada di grup `auth`. Arsip yang ada di Sampah menghasilkan 404 karena route model binding mengecualikannya.
- Jangan menampilkan `file_path` mentah ke pengguna dan jangan membuat URL file langsung.

## Penghapusan dan Siklus File

| Aksi | Baris database | File fisik |
|---|---|---|
| Hapus arsip (Admin) | Soft delete (`deleted_at` terisi) | **Tetap ada** |
| Pulihkan | `deleted_at` dikosongkan | Tidak berubah |
| Ganti file saat edit | `file_path` diperbarui | File lama **dihapus** |
| Hapus permanen, kosongkan Sampah, lewat 30 hari | Baris dihapus | **Ikut dihapus** (event `forceDeleted`) |

Aturan:
- Penghapusan permanen **harus lewat model** (`$arsip->forceDelete()`), supaya event menghapus file fisiknya. Query builder massal membuat file yatim.
- Backup database serta folder arsip pada disk yang dirujuk record **sebelum** menghapus permanen dalam jumlah banyak. Dokumen prosedur backup khusus belum tersedia. Selama masih ada salinan lama, pertimbangkan juga `storage/app/public/arsip`.
- Kuota penyimpanan di dashboard menghitung semua file di folder `arsip`, termasuk milik arsip di Sampah.

## Status Private Storage

Konfigurasi `arsip_private` memakai root `storage/app/private`, di luar web root. Upload baru ditandai `file_disk=arsip_private`. Model `Arsip::diskArsip()` memilih disk berdasarkan `file_disk`; nilai kosong dianggap `public` untuk kompatibilitas record lama. Migration kolom `file_disk` memberi default `public`. Symlink `public/storage` tetap menuju `storage/app/public`.

**Snapshot audit 5 November 2026:** database memiliki 5 record arsip dan kelimanya memakai `arsip_private`; tidak ada record `public` atau `file_disk` kosong. File pada disk yang dirujuk tersedia untuk semua record tersebut. Tiga salinan fisik lama masih ada di `storage/app/public/arsip`, dan path ketiganya cocok dengan path record yang kini menunjuk ke disk privat. Command `arsip:migrasikan-ke-private --apply` menyalin dan memverifikasi file, lalu mengalihkan referensi record; command memang mempertahankan sumber publik.

Pada host Laragon `http://pengarsipan.test` yang diuji pada tanggal tersebut, HTTP GET dan HEAD ke URL langsung `/storage/arsip/...` menghasilkan **403**; GET ke `/robots.txt` menghasilkan **200**. Aturan di `public/.htaccess` memblokir path arsip melalui `mod_rewrite`. Hasil ini hanya membuktikan perilaku host Laragon yang diuji; **jangan menyimpulkan semua web server atau konfigurasi deployment sudah terlindungi**. Server yang tidak menerapkan aturan `.htaccess` dapat berperilaku berbeda.

Langkah lanjutan untuk salinan publik:
1. Pertahankan larangan URL langsung dan verifikasi konfigurasi setara pada setiap web server yang dipakai.
2. Backup dan verifikasi file sebelum menghapus salinan lama. Hapus hanya salinan yang sudah dipastikan aman untuk dibersihkan; jangan menghapus seluruh symlink `public/storage` bila masih dipakai untuk aset publik lain.
3. Uji GET dan HEAD lagi pada host yang benar-benar digunakan setelah tindakan pembersihan.
4. Perbarui dokumentasi backup dan catatan keputusan terkait bila strategi penyimpanan berubah.

Kode baru tetap mengakses file melalui `ArsipController@preview` atau `@download`, dengan disk dipilih lewat `$arsip->diskArsip()`. Jangan menambah URL langsung `/storage` untuk file arsip.

## Rahasia dan Konfigurasi

- `.env` tidak di-commit. `APP_KEY` dijaga dan ikut dibackup bersama `.env`.
- Produksi: `APP_DEBUG=false` dan `APP_ENV=production`.
- Jangan menaruh kata sandi, token, atau data pribadi di deskripsi log aktivitas.

## Anti-pattern

| Salah | Benar |
|---|---|
| Tombol Hapus disembunyikan untuk User, tanpa cek di server | `authorize('delete', $arsip)` di controller dan `@can` di view |
| `<a href="{{ asset('storage/' . $arsip->file_path) }}">` | `<a href="{{ route('arsip.download', $arsip) }}">` |
| Upload file arsip ke disk `public` | Simpan upload baru dengan nama UUID di disk `arsip_private` |
| `Route::get('arsip/{arsip}/pin', ...)` yang mengubah data | `Route::post(...)` dengan `@csrf` |
| `Arsip::onlyTrashed()->forceDelete()` | Loop model, `$arsip->forceDelete()` |
| `User::create($request->all())` | `User::create($data)` dari `validate()` |
| `{!! $arsip->deskripsi !!}` | `{{ $arsip->deskripsi }}` |

## Checklist Sebelum Selesai

1. Endpoint ada di grup route yang benar dan diuji sebagai Admin, User, dan tamu.
2. Aturan per data memakai policy, bukan `if` role di controller.
3. Aksi yang mengubah data bukan `GET` dan memakai `@csrf`.
4. Hanya data hasil `validate()` yang dipakai, tanpa `$request->all()`.
5. Tidak ada link langsung ke file, tidak ada `{!! !!}` untuk data pengguna.
6. Alur file (upload, ganti, hapus, hapus permanen) diuji, dan file fisik ikut terhapus saat hapus permanen.
7. Tidak ada rahasia yang masuk Git atau log.
8. Dokumen dan `docs/decisions.md` diperbarui bila aturan berubah.
