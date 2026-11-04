<?php

namespace App\Console\Commands;

use App\Models\Arsip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrasikanArsipKePrivate extends Command
{
    protected $signature = 'arsip:migrasikan-ke-private
        {--dry-run : Periksa rencana migrasi tanpa menyalin atau mengubah database}
        {--apply : Salin, verifikasi, lalu alihkan referensi arsip}
        {--rollback : Arahkan arsip kembali ke public tanpa menghapus salinan private}';

    protected $description = 'Salin file arsip dari public ke penyimpanan privat dengan verifikasi checksum';

    public function handle(): int
    {
        if ($this->option('dry-run') === $this->option('apply') || ($this->option('rollback') && !$this->option('apply') && !$this->option('dry-run'))) {
            $this->error('Pilih --dry-run atau --apply; --rollback dapat digabung dengan salah satunya.');

            return self::INVALID;
        }

        $dryRun = (bool) $this->option('dry-run');
        $rollback = (bool) $this->option('rollback');
        $jumlah = ['dialihkan' => 0, 'dilewati' => 0, 'gagal' => 0];

        Arsip::withTrashed()->whereNotNull('file_path')->orderBy('id')->chunkById(100, function ($arsips) use ($dryRun, $rollback, &$jumlah) {
            foreach ($arsips as $arsip) {
                try {
                    $hasil = $rollback
                        ? $this->rollback($arsip, $dryRun)
                        : $this->proses($arsip, $dryRun);
                    $jumlah[$hasil]++;
                } catch (Throwable $e) {
                    $jumlah['gagal']++;
                    $this->error("Arsip #{$arsip->id}: {$e->getMessage()}");
                }
            }
        });

        $this->table(['Hasil', 'Jumlah'], [
            ['Dialihkan', $jumlah['dialihkan']],
            ['Tidak berubah / dry-run', $jumlah['dilewati']],
            ['Gagal', $jumlah['gagal']],
        ]);

        return $jumlah['gagal'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function rollback(Arsip $arsip, bool $dryRun): string
    {
        $pathLama = $arsip->file_path;
        $this->validasiPath($pathLama);
        if ($arsip->diskArsip() === 'public') {
            $this->line("Arsip #{$arsip->id}: sudah di public; tidak diubah.");
            return 'dilewati';
        }

        $private = Storage::disk('arsip_private');
        $public = Storage::disk('public');
        if (!$private->exists($pathLama)) {
            throw new \RuntimeException("sumber private tidak ditemukan ({$pathLama}); referensi tidak diubah.");
        }

        $ukuran = $private->size($pathLama);
        $checksum = $this->checksum($private, $pathLama);
        $pathTujuan = $pathLama;

        // Jangan menimpa sumber public lama bila isinya berbeda. Simpan versi terkini
        // di path baru agar rollback tidak menghilangkan file lama.
        if ($public->exists($pathTujuan)) {
            try {
                $this->verifikasi($public, $pathTujuan, $ukuran, $checksum);
            } catch (Throwable $e) {
                $nama = basename($pathLama);
                $pathTujuan = 'arsip/rollback/' . $arsip->id . '-' . $nama;
                if ($public->exists($pathTujuan)) {
                    $this->verifikasi($public, $pathTujuan, $ukuran, $checksum);
                }
            }
        }

        if (!$public->exists($pathTujuan) && !$dryRun) {
            $stream = $private->readStream($pathLama);
            if (!is_resource($stream)) {
                throw new \RuntimeException('gagal membuka stream file private untuk rollback.');
            }
            try {
                if (!$public->put($pathTujuan, $stream)) {
                    throw new \RuntimeException('gagal menyalin file ke public saat rollback.');
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            $this->verifikasi($public, $pathTujuan, $ukuran, $checksum);
        }

        if ($dryRun) {
            $this->line("Arsip #{$arsip->id}: rollback akan memakai {$pathTujuan}; checksum SHA-256 {$checksum}.");
            return 'dilewati';
        }

        DB::transaction(function () use ($arsip, $pathLama, $pathTujuan) {
            $terkini = Arsip::withTrashed()->lockForUpdate()->find($arsip->id);
            if (!$terkini || $terkini->file_path !== $pathLama || $terkini->diskArsip() !== 'arsip_private') {
                throw new \RuntimeException('record berubah selama rollback; referensi tidak dialihkan.');
            }

            $terkini->file_path = $pathTujuan;
            $terkini->file_disk = 'public';
            $terkini->save();
        });

        $this->info("Arsip #{$arsip->id}: referensi kembali ke public; salinan private dan sumber lama dipertahankan.");
        return 'dialihkan';
    }

    private function proses(Arsip $arsip, bool $dryRun): string
    {
        $path = $arsip->file_path;
        $diskAsal = $arsip->diskArsip();

        $this->validasiPath($path);

        if ($diskAsal === 'arsip_private') {
            if (!Storage::disk('arsip_private')->exists($path)) {
                throw new \RuntimeException("record sudah menunjuk ke private tetapi file tidak ditemukan ({$path}).");
            }
            $this->info("Arsip #{$arsip->id}: sudah privat; tidak diubah.");
            return 'dilewati';
        }

        $sumber = Storage::disk('public');
        $tujuan = Storage::disk('arsip_private');

        if (!$sumber->exists($path)) {
            throw new \RuntimeException("file sumber tidak ditemukan ({$path}).");
        }

        $ukuranSumber = $sumber->size($path);
        $checksumSumber = $this->checksum($sumber, $path);

        if ($tujuan->exists($path)) {
            $this->verifikasi($tujuan, $path, $ukuranSumber, $checksumSumber);
        } elseif (!$dryRun) {
            $stream = $sumber->readStream($path);
            if (!is_resource($stream)) {
                throw new \RuntimeException('gagal membuka stream file sumber.');
            }

            try {
                if (!$tujuan->put($path, $stream)) {
                    throw new \RuntimeException('gagal menyalin file ke disk privat.');
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            $this->verifikasi($tujuan, $path, $ukuranSumber, $checksumSumber);
        } else {
            $this->line("Arsip #{$arsip->id}: siap disalin; sumber {$ukuranSumber} byte, SHA-256 {$checksumSumber}.");
            return 'dilewati';
        }

        if ($dryRun) {
            $this->line("Arsip #{$arsip->id}: salinan privat yang sudah ada cocok; referensi akan dialihkan.");
            return 'dilewati';
        }

        DB::transaction(function () use ($arsip, $path, $diskAsal) {
            $terkini = Arsip::withTrashed()->lockForUpdate()->find($arsip->id);

            if (!$terkini || $terkini->file_path !== $path || $terkini->diskArsip() !== $diskAsal) {
                throw new \RuntimeException('record berubah selama migrasi; referensi tidak dialihkan.');
            }

            $terkini->file_disk = 'arsip_private';
            $terkini->save();
        });

        $this->info("Arsip #{$arsip->id}: tersalin dan terverifikasi; file sumber dipertahankan.");

        return 'dialihkan';
    }

    private function verifikasi($disk, string $path, int $ukuranSumber, string $checksumSumber): void
    {
        if (!$disk->exists($path)) {
            throw new \RuntimeException('file salinan tidak ditemukan setelah copy.');
        }

        $ukuranSalinan = $disk->size($path);
        $checksumSalinan = $this->checksum($disk, $path);

        if ($ukuranSalinan !== $ukuranSumber || !hash_equals($checksumSumber, $checksumSalinan)) {
            throw new \RuntimeException('ukuran atau checksum salinan tidak cocok; referensi tidak dialihkan.');
        }
    }

    private function checksum($disk, string $path): string
    {
        $stream = $disk->readStream($path);
        if (!is_resource($stream)) {
            throw new \RuntimeException("gagal membaca file untuk checksum ({$path}).");
        }

        $context = hash_init('sha256');

        try {
            hash_update_stream($context, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return hash_final($context);
    }

    private function validasiPath(?string $path): void
    {
        if (!$path || str_contains($path, '..') || str_starts_with($path, '/') || !str_starts_with($path, 'arsip/')) {
            throw new \RuntimeException('path file tidak valid; hanya path relatif di folder arsip yang diterima.');
        }
    }
}
