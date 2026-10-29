<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'aksi', 'deskripsi'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Pemakaian: ActivityLog::catat('tambah', 'Menambah arsip X');
    public static function catat(string $aksi, string $deskripsi): void
    {
        static::create([
            'user_id'   => auth()->id(),
            'aksi'      => $aksi,
            'deskripsi' => $deskripsi,
        ]);
    }

    // Hanya aktivitas arsip (buat anggota biasa). Tambahin pola baru di sini kalau ada jenis log arsip lain.
    public function scopeArsipSaja($query)
    {
        return $query->where(function ($w) {
            foreach (['Menambah arsip %', 'Mengubah arsip %', 'Menghapus arsip %', 'Memulihkan arsip %'] as $pola) {
                $w->orWhere('deskripsi', 'like', $pola);
            }
        });
    }
}