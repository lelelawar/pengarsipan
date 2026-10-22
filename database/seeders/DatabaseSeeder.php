<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        User::create([
            'nama' => 'User Biasa',
            'username' => 'user',
            'password' => 'user123',
            'role' => 'user',
        ]);

        foreach (['Surat Masuk', 'Surat Keluar', 'Laporan', 'Lainnya'] as $nama) {
            Kategori::create(['nama' => $nama]);
        }
    }
}