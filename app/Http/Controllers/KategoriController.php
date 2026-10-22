<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::withCount('arsips')->orderBy('nama')->get();

        return view('kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:kategoris,nama'],
        ]);

        $kategori = Kategori::create($data);

        ActivityLog::catat('tambah', "Menambah kategori {$kategori->nama}");

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('kategoris', 'nama')->ignore($kategori->id)],
        ]);

        $lama = $kategori->nama;
        $kategori->update($data);

        ActivityLog::catat('edit', "Mengubah kategori {$lama} menjadi {$kategori->nama}");

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        // withTrashed: arsip yang di-soft delete tetap menahan kategori ini
        if ($kategori->arsips()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori masih dipakai oleh arsip, jadi tidak bisa dihapus.');
        }

        $nama = $kategori->nama;
        $kategori->delete();

        ActivityLog::catat('hapus', "Menghapus kategori {$nama}");

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}