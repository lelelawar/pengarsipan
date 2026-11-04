<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = auth()->user()->isAdmin();

        $totalArsip    = Arsip::count();
        $arsipBulanIni = Arsip::whereYear('created_at', now()->year)
                              ->whereMonth('created_at', now()->month)
                              ->count();
        $totalKategori = Kategori::count();
        $totalUser     = User::count();

        $arsipTerbaru = Arsip::with(['pembuat', 'kategori'])->latest()->take(5)->get();

        $perKategori = Kategori::withCount('arsips')
            ->orderByDesc('arsips_count')
            ->get();

        // Admin lihat semua aktivitas, anggota biasa cuma aktivitas arsip
        $logQuery = ActivityLog::with('user')->latest();
        if (!$admin) {
            $logQuery->arsipSaja();
        }
        $logTerbaru = $logQuery->take(6)->get();

        // Hitung file yang direferensikan record, termasuk Sampah. Salinan migrasi
        // yang belum direferensikan tidak menggandakan angka arsip pada widget.
        $berkas = Arsip::withTrashed()->whereNotNull('file_path')->get(['file_path', 'file_disk']);
        $terhitung = [];
        foreach ($berkas as $arsip) {
            $diskNama = $arsip->diskArsip();
            $kunci = $diskNama . ':' . $arsip->file_path;
            if (isset($terhitung[$kunci])) {
                continue;
            }

            $disk = Storage::disk($diskNama);
            if ($disk->exists($arsip->file_path)) {
                $terhitung[$kunci] = $disk->size($arsip->file_path);
            }
        }
        $ukuranTerpakai = array_sum($terhitung);
        $jumlahBerkas   = count($terhitung);
        $kuota          = 1024 * 1024 * 1024; // 1 GB, ubah sesuai kebutuhan

        return view('dashboard', compact(
            'totalArsip', 'arsipBulanIni', 'totalKategori', 'totalUser',
            'arsipTerbaru', 'perKategori', 'logTerbaru',
            'ukuranTerpakai', 'jumlahBerkas', 'kuota'
        ));
    }
}
