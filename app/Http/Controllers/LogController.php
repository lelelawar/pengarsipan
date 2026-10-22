<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->aksi, fn ($query, $aksi) => $query->where('aksi', $aksi))
            ->when($request->q, fn ($query, $cari) => $query->where('deskripsi', 'like', "%{$cari}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('log.index', compact('logs'));
    }
}