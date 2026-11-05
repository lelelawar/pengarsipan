<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoritController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Arsip::class);

        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $arsips = $request->user()->pins()
            ->with(['kategori', 'pembuat'])
            ->when($data['q'] ?? null, function ($query, $cari) {
                $query->where(function ($w) use ($cari) {
                    $w->where('arsips.judul', 'like', "%{$cari}%")
                        ->orWhere('arsips.nomor_arsip', 'like', "%{$cari}%");
                });
            })
            ->latest('arsip_pin.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('favorit.index', compact('arsips'));
    }

    public function pin(Arsip $arsip)
    {
        $this->authorize('view', $arsip);

        DB::table('arsip_pin')->insertOrIgnore([
            'user_id' => auth()->id(),
            'arsip_id' => $arsip->id,
        ]);

        return back()->with('success', 'Arsip ditambahkan ke Favorit.');
    }

    public function unpin(Arsip $arsip)
    {
        $this->authorize('view', $arsip);

        auth()->user()->pins()->detach($arsip->id);

        return back()->with('success', 'Arsip dihapus dari Favorit.');
    }
}
