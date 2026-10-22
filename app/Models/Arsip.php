<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arsip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_arsip', 'judul', 'kategori_id',
        'tanggal_arsip', 'deskripsi', 'file_path', 'created_by',
    ];

    protected $casts = ['tanggal_arsip' => 'date'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}