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
}