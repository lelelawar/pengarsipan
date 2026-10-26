<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $this->validasi($request);
        $base   = $this->base($filter);

        $total = (clone $base)->count();

        $perKategori = (clone $base)
            ->select('kategori_id', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kategori_id')
            ->with('kategori')
            ->get();

        $arsips = (clone $base)
            ->with(['kategori', 'pembuat'])
            ->orderByDesc('tanggal_arsip')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $kategoris  = Kategori::orderBy('nama')->get();
        $users      = User::orderBy('nama')->get();
        $infoFilter = $this->infoFilter($filter);

        return view('laporan.index', compact(
            'arsips', 'total', 'perKategori', 'kategoris', 'users', 'infoFilter'
        ));
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'dari'        => ['nullable', 'date'],
            'sampai'      => ['nullable', 'date', 'after_or_equal:dari'],
            'kategori_id' => ['nullable', 'exists:kategoris,id'],
            'user_id'     => ['nullable', 'exists:users,id'],
            'q'           => ['nullable', 'string', 'max:100'],
        ]);
    }

    // Query dasar dengan filter. Arsip yang sudah di-soft delete otomatis tidak ikut.
    private function base(array $f)
    {
        return Arsip::query()
            ->when($f['dari'] ?? null, fn ($q, $v) => $q->whereDate('tanggal_arsip', '>=', $v))
            ->when($f['sampai'] ?? null, fn ($q, $v) => $q->whereDate('tanggal_arsip', '<=', $v))
            ->when($f['kategori_id'] ?? null, fn ($q, $v) => $q->where('kategori_id', $v))
            ->when($f['user_id'] ?? null, fn ($q, $v) => $q->where('created_by', $v))
            ->when($f['q'] ?? null, function ($q, $v) {
                $q->where(function ($w) use ($v) {
                    $w->where('judul', 'like', "%{$v}%")
                      ->orWhere('nomor_arsip', 'like', "%{$v}%");
                });
            });
    }

    // Teks ringkasan filter, dipakai di kop saat dicetak
    private function infoFilter(array $f): array
    {
        $info = [];

        if (!empty($f['dari']) || !empty($f['sampai'])) {
            $dari   = !empty($f['dari']) ? Carbon::parse($f['dari'])->format('d M Y') : 'awal';
            $sampai = !empty($f['sampai']) ? Carbon::parse($f['sampai'])->format('d M Y') : 'sekarang';
            $info[] = "Periode: {$dari} s/d {$sampai}";
        } else {
            $info[] = 'Periode: semua tanggal';
        }

        if (!empty($f['kategori_id'])) {
            $info[] = 'Kategori: ' . Kategori::find($f['kategori_id'])?->nama;
        }

        if (!empty($f['user_id'])) {
            $info[] = 'Dibuat oleh: ' . User::find($f['user_id'])?->nama;
        }

        if (!empty($f['q'])) {
            $info[] = 'Kata kunci: ' . $f['q'];
        }

        return $info;
    }
}