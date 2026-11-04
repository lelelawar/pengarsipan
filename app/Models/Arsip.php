<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LogicException;

class Arsip extends Model
{
    use SoftDeletes;

    // Lama arsip disimpan di Sampah sebelum dihapus permanen
    public const MASA_SAMPAH_HARI = 30;

    public const DISK_FILE_DIIZINKAN = ['public', 'arsip_private'];

    protected static function booted(): void
    {
        static::saving(function (Arsip $arsip) {
            if ($arsip->file_disk !== null && !in_array($arsip->file_disk, self::DISK_FILE_DIIZINKAN, true)) {
                throw new LogicException('Disk file arsip tidak diizinkan.');
            }
        });

        // Hapus permanen = file fisiknya ikut dihapus dari storage
        static::forceDeleted(function (Arsip $arsip) {
            if ($arsip->file_path) {
                try {
                    if (!Storage::disk($arsip->diskArsip())->delete($arsip->file_path)) {
                        Log::warning('File arsip tidak terhapus setelah force delete.', [
                            'arsip_id' => $arsip->id,
                            'file_disk' => $arsip->file_disk,
                            'file_path' => $arsip->file_path,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Gagal menghapus file arsip permanen.', [
                        'arsip_id' => $arsip->id,
                        'file_disk' => $arsip->file_disk,
                        'file_path' => $arsip->file_path,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    // Hapus permanen arsip yang sudah lewat masa simpan. Mengembalikan jumlah yang dihapus.
    public static function bersihkanSampahKedaluwarsa(): int
    {
        $batas  = now()->subDays(self::MASA_SAMPAH_HARI);
        $daftar = static::onlyTrashed()->where('deleted_at', '<', $batas)->get();

        foreach ($daftar as $arsip) {
            $arsip->forceDelete();
        }

        return $daftar->count();
    }

    // Jenis file yang boleh diupload
    public const JENIS = [
        'pdf'    => ['label' => 'PDF',    'ekstensi' => ['pdf'],                                'ikon' => 'bi-file-earmark-pdf',   'warna' => '#dc2626'],
        'gambar' => ['label' => 'Gambar', 'ekstensi' => ['jpg', 'jpeg', 'png', 'gif', 'webp'], 'ikon' => 'bi-file-earmark-image', 'warna' => '#d97706'],
    ];

    protected $fillable = [
        'nomor_arsip', 'judul', 'kategori_id',
        'tanggal_arsip', 'deskripsi', 'file_path', 'file_disk', 'created_by',
    ];

    protected $casts = ['tanggal_arsip' => 'date'];

    /** Disk file arsip; null pada record lama diperlakukan sebagai public. */
    public function diskArsip(): string
    {
        $disk = $this->file_disk ?: 'public';

        if (!in_array($disk, self::DISK_FILE_DIIZINKAN, true)) {
            throw new LogicException('Disk file arsip tidak diizinkan.');
        }

        return $disk;
    }

    public static function semuaEkstensi(): array
    {
        return collect(self::JENIS)->pluck('ekstensi')->flatten()->all();
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Ekstensi file, misal "pdf"
    public function getEkstensiAttribute(): ?string
    {
        return $this->file_path
            ? strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION))
            : null;
    }

    // Kode jenis: pdf atau gambar (null kalau format lain, misal sisa file tes lama)
    public function getJenisFileAttribute(): ?string
    {
        $ekstensi = $this->ekstensi;

        foreach (self::JENIS as $kode => $info) {
            if (in_array($ekstensi, $info['ekstensi'], true)) {
                return $kode;
            }
        }

        return null;
    }

    public function getIkonAttribute(): string
    {
        $jenis = $this->jenis_file;

        return $jenis ? self::JENIS[$jenis]['ikon'] : 'bi-file-earmark';
    }

    public function getWarnaIkonAttribute(): string
    {
        $jenis = $this->jenis_file;

        return $jenis ? self::JENIS[$jenis]['warna'] : '#6b7280';
    }

    // PDF dan gambar sama-sama bisa dibuka langsung di browser
    public function getBisaPratinjauAttribute(): bool
    {
        return in_array($this->jenis_file, ['pdf', 'gambar'], true);
    }

    // Filter berdasarkan jenis file: Arsip::jenis('gambar')
    public function scopeJenis($query, ?string $jenis)
    {
        $ekstensi = self::JENIS[$jenis]['ekstensi'] ?? null;

        if (!$ekstensi) {
            return $query;
        }

        return $query->where(function ($w) use ($ekstensi) {
            foreach ($ekstensi as $e) {
                $w->orWhere('file_path', 'like', '%.' . $e);
            }
        });
    }
}
