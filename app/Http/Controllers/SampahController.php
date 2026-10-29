<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Arsip;
use Illuminate\Http\Request;

class SampahController extends Controller
{
    public function index(Request $request)
    {
        // Bersihkan dulu yang sudah lewat masa simpan
        Arsip::bersihkanSampahKedaluwarsa();

        $arsips = Arsip::onlyTrashed()
            ->with(['kategori', 'pembuat'])
            ->when($request->q, function ($query, $cari) {
                $query->where(function ($w) use ($cari) {
                    $w->where('judul', 'like', "%{$cari}%")
                      ->orWhere('nomor_arsip', 'like', "%{$cari}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        $totalSampah = Arsip::onlyTrashed()->count();

        return view('sampah.index', compact('arsips', 'totalSampah'));
    }

    public function pulihkan(int $id)
    {
        $arsip = Arsip::onlyTrashed()->findOrFail($id);
        $arsip->restore();

        ActivityLog::catat('pulihkan', "Memulihkan arsip {$arsip->nomor_arsip} - {$arsip->judul}");

        return back()->with('success', 'Arsip berhasil dipulihkan.');
    }

    public function hapusPermanen(int $id)
    {
        $arsip = Arsip::onlyTrashed()->findOrFail($id);
        $label = "{$arsip->nomor_arsip} - {$arsip->judul}";

        $arsip->forceDelete(); // file fisiknya ikut terhapus (lihat Arsip::booted)

        ActivityLog::catat('permanen', "Menghapus permanen arsip {$label}");

        return back()->with('success', 'Arsip dihapus permanen.');
    }

    public function kosongkan()
    {
        $daftar = Arsip::onlyTrashed()->get();

        foreach ($daftar as $arsip) {
            $arsip->forceDelete();
        }

        if ($daftar->isNotEmpty()) {
            ActivityLog::catat('permanen', "Mengosongkan Sampah ({$daftar->count()} arsip dihapus permanen)");
        }

        return back()->with('success', 'Sampah dikosongkan.');
    }
}