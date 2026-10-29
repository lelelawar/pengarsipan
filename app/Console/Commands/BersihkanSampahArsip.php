<?php

namespace App\Console\Commands;

use App\Models\Arsip;
use Illuminate\Console\Command;

class BersihkanSampahArsip extends Command
{
    protected $signature = 'arsip:bersihkan-sampah';

    protected $description = 'Hapus permanen arsip di Sampah yang sudah lewat masa simpan (30 hari)';

    public function handle(): int
    {
        $jumlah = Arsip::bersihkanSampahKedaluwarsa();

        $this->info("{$jumlah} arsip dihapus permanen.");

        return self::SUCCESS;
    }
}