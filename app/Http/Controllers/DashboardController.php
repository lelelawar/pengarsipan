<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
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

        // Aktivitas terbaru hanya untuk admin
        $logTerbaru = auth()->user()->isAdmin()
            ? ActivityLog::with('user')->latest()->take(6)->get()
            : collect();

        return view('dashboard', compact(
            'totalArsip', 'arsipBulanIni', 'totalKategori', 'totalUser',
            'arsipTerbaru', 'perKategori', 'logTerbaru'
        ));
    }
}