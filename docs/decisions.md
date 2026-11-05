# Catatan Keputusan

Daftar keputusan proyek beserta alasannya. Tujuannya mencegah pembahasan ulang dan mencegah fitur yang sudah ditolak ditambahkan lagi.

Cara memakai:
- Tambahkan satu baris setiap ada keputusan baru.
- Kalau keputusan berubah, **jangan hapus baris lama**. Ubah statusnya jadi "Diganti" dan tambahkan baris baru.
- Kalau sebuah permintaan bertabrakan dengan baris yang berstatus "Berlaku", tanyakan dulu ke pemilik proyek.

| No | Keputusan | Alasan | Ditolak atau sempat dipertimbangkan | Status |
|---|---|---|---|---|
| 1 | Dua role saja: Admin dan User | Cukup untuk tim kecil, pembagian tugas jelas | Satu role saja (siapa yang mengelola akun?) | Berlaku |
| 2 | Hak akses "opsi B": semua bisa lihat, upload, cetak; User edit miliknya; Admin kelola semuanya | Seimbang antara sederhana dan terkendali | Opsi A (hanya Admin yang upload), opsi C (hak akses per folder, terlalu besar) | Berlaku |
| 3 | Hapus arsip hanya oleh Admin, berupa soft delete | Mencegah kehilangan data tidak sengaja | Member boleh hapus ke Trash | Berlaku |
| 4 | Format file: **PDF dan gambar** (JPG, JPEG, PNG, GIF, WEBP), maksimal 10 MB | Cukup untuk arsip, dan keduanya bisa dipratinjau di browser | PDF saja (awal), PDF/Word/Excel/gambar/ZIP (sempat dipakai, lalu dibatalkan) | Berlaku |
| 5 | Tanpa export PDF/Excel. Laporan = filter + tombol Cetak | Aplikasi hanya menyimpan file arsip, tidak mengubah apa pun jadi PDF | Export lewat dompdf dan maatwebsite/excel (sempat dipasang, lalu dicopot) | Berlaku (mengganti spesifikasi awal) |
| 6 | Upload drag and drop **satu file per arsip** | Nomor arsip wajib dan unik, banyak file sekaligus butuh nomor otomatis | Upload banyak file sekaligus | Berlaku |
| 7 | Sampah 30 hari, khusus Admin, auto-hapus saat halaman Sampah dibuka atau lewat command | Mencegah hapus tidak sengaja, tanpa perlu cron di lokal | Hapus langsung permanen | Berlaku |
| 8 | `nomor_arsip` unik termasuk arsip di Sampah | Konsisten dengan fitur pemulihan | | Berlaku (sudah dikonfirmasi) |
| 9 | Kategori tetap ada. Folder hierarkis kelak ditambahkan, bukan pengganti kategori | Kategori berfungsi sebagai filter divisi | Mengganti kategori dengan folder | Berlaku |
| 10 | File arsip dilayani lewat controller dengan nama acak (UUID) | Wajib login dan cek policy, nama file tidak bisa ditebak | Link langsung `asset('storage/...')` | Berlaku (snapshot 5 Nov 2026: semua 5 record menunjuk `arsip_private`; tiga salinan fisik lama masih ada dan URL langsung diuji 403 pada host Laragon. Detail dan batas uji ada di `docs/guidelines/security-and-storage.md`) |
| 11 | Log aktivitas: halaman lengkap khusus Admin, dashboard anggota hanya aktivitas arsip | Anggota tahu perkembangan tanpa melihat data akun | Semua log terbuka untuk semua | Berlaku |
| 12 | Favorit (pin) pribadi per akun, tidak masuk log aktivitas | Preferensi pribadi, bukan perubahan data | Favorit bersama satu tim | Berlaku |
| 13 | Tanpa registrasi publik dan tanpa reset password lewat email | Akun dikelola Admin | | Berlaku |
| 14 | Bootstrap 5 lewat CDN, tanpa build step | Instalasi sederhana | Vite atau Tailwind | Berlaku |
| 15 | Logika di controller, tanpa service layer | Skala proyek kecil | | Berlaku (tinjau ulang jika controller membesar) |
| 16 | Partial form bernama `form.blade.php` (tanpa underscore) | Nama file disamakan dengan `@include` setelah sempat salah ketik | `_form.blade.php` | Berlaku |
| 17 | Zona waktu `Asia/Jakarta` | Jam di log dan sapaan dashboard sesuai | UTC | Berlaku |
| 18 | Dokumentasi: README, AGENTS, dan `docs/` (architecture, database, features, backup-and-recovery, testing, decisions, troubleshooting, history) | Cukup untuk dirawat sendiri | `api.md`, `deployment.md` (ditunda) | Berlaku |
| 19 | Guidelines hanya **3 file** di `docs/guidelines/`: `backend.md`, `frontend.md`, `security-and-storage.md`. Bagian konvensi dipindah dari `AGENTS.md` ke sini | Enam file (frontend, backend, database, security, storage, coding-standards) akan tumpang tindih dan sulit dirawat | Enam file terpisah. Topik `database` masuk `backend.md` dan `docs/database.md`, `coding-standards` masuk `backend.md`, `storage` digabung ke security | Berlaku |

## Template Baris Baru

```markdown
| 20 | <keputusan> | <alasan> | <yang ditolak> | Berlaku |
```
