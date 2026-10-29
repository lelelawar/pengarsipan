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

        // Pemakaian storage (semua file di folder arsip, termasuk arsip yang sudah dihapus)
        $disk           = Storage::disk('public');
        $berkas         = $disk->allFiles('arsip');
        $ukuranTerpakai = collect($berkas)->sum(fn ($f) => $disk->size($f));
        $jumlahBerkas   = count($berkas);
        $kuota          = 1024 * 1024 * 1024; // 1 GB, ubah sesuai kebutuhan

        return view('dashboard', compact(
            'totalArsip', 'arsipBulanIni', 'totalKategori', 'totalUser',
            'arsipTerbaru', 'perKategori', 'logTerbaru',
            'ukuranTerpakai', 'jumlahBerkas', 'kuota'
        ));
    }
}