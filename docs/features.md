# Fitur dan Hak Akses

Dokumen ini mencatat perilaku yang tersedia sekarang. Struktur dan alur kode
diringkas di `docs/architecture.md`; skema ada di `docs/database.md`.

## Fitur

- **Login dan logout:** autentikasi memakai username/password. Tidak ada
  registrasi publik atau alur reset password email.
- **Arsip:** pengguna login dapat melihat daftar/detail, mengunggah, melihat
  pratinjau, dan mengunduh arsip. Format dan validasi file mengikuti
  `Arsip::JENIS` dan `ArsipController`.
- **Edit arsip:** User hanya dapat mengedit arsip yang dibuatnya sendiri;
  Admin dapat mengedit semua arsip.
- **Hapus arsip:** hanya Admin. Hapus biasa memindahkan arsip ke Sampah melalui
  soft delete. Pemulihan dan penghapusan permanen tersedia untuk Admin.
- **Kategori dan akun:** hanya Admin yang dapat mengelolanya.
- **Laporan:** dapat diakses Admin dan User yang login; laporan memakai filter
  dan pagination, dengan tombol cetak.
- **Log aktivitas:** halaman log lengkap hanya untuk Admin. Dashboard User
  menyaring log ke aktivitas arsip.
- **Pin/Favorit:** Admin dan User dapat mem-pin arsip aktif yang dapat mereka
  lihat. Daftar pin privat untuk akun yang login; setiap pasangan akun/arsip
  hanya dapat disimpan sekali. Arsip di Sampah tidak tampil, pin tetap ada saat
  soft delete, dan pin kembali terlihat setelah arsip dipulihkan. Penghapusan
  permanen arsip atau akun menghapus pivot melalui foreign key cascade. Aksi
  pin/unpin tidak mengubah arsip dan tidak menghasilkan Activity Log.
- **Dashboard:** menampilkan ringkasan arsip dan kategori, aktivitas, serta
  informasi penyimpanan. Ringkasan jumlah akun ditampilkan bagi Admin.

## Matriks hak akses aktual

| Fitur/aksi | Admin | User |
|---|---:|---:|
| Login, dashboard, daftar/detail arsip aktif | Ya | Ya |
| Upload arsip | Ya | Ya |
| Pratinjau dan unduh arsip yang dapat dilihat | Ya | Ya |
| Edit arsip | Semua arsip | Arsip miliknya sendiri |
| Hapus arsip ke Sampah | Ya | Tidak |
| Lihat, pulihkan, dan hapus permanen isi Sampah | Ya | Tidak |
| Kelola kategori | Ya | Tidak |
| Kelola akun | Ya | Tidak |
| Laporan | Ya | Ya |
| Halaman Log Aktivitas | Ya | Tidak |
| Pin/unpin dan lihat daftar Favorit | Favorit sendiri | Favorit sendiri |

Aturan arsip mengikuti `ArsipPolicy`: `viewAny`, `view`, dan `create` diizinkan
bagi setiap user terautentikasi; `update` hanya Admin atau pemilik; `delete`
hanya Admin. Fitur Favorit tidak memperluas izin edit atau hapus tersebut.

## Batasan akun

Admin tidak dapat menghapus atau menurunkan role akunnya sendiri. Akun yang
memiliki arsip aktif/di Sampah atau activity log tidak dapat dihapus. Akun lain
yang memenuhi aturan controller dapat dihapus; pin yang dimilikinya ikut
terhapus oleh cascade foreign key.
