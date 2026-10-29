<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Arsip;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Arsip::class);

        $arsips = Arsip::with(['kategori', 'pembuat'])
            ->when($request->q, function ($query, $cari) {
                $query->where(function ($w) use ($cari) {
                    $w->where('judul', 'like', "%{$cari}%")
                      ->orWhere('nomor_arsip', 'like', "%{$cari}%");
                });
            })
            ->when($request->kategori_id, fn ($query, $id) => $query->where('kategori_id', $id))
            ->latest('tanggal_arsip')
            ->paginate(10)
            ->withQueryString();

        $kategoris = Kategori::orderBy('nama')->get();

        return view('arsip.index', compact('arsips', 'kategoris'));
    }

    public function create()
    {
        $this->authorize('create', Arsip::class);

        $kategoris = Kategori::orderBy('nama')->get();

        return view('arsip.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Arsip::class);

        $data = $request->validate([
            'nomor_arsip'   => ['required', 'string', 'max:100', 'unique:arsips,nomor_arsip'],
            'judul'         => ['required', 'string', 'max:255'],
            'kategori_id'   => ['required', 'exists:kategoris,id'],
            'tanggal_arsip' => ['required', 'date'],
            'deskripsi'     => ['nullable', 'string'],
            'file'          => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'file.required' => 'File arsip (PDF) wajib diupload.',
            'file.mimes'    => 'File harus berformat PDF.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
            'file.uploaded' => 'Upload gagal. Cek ukuran file (maks 5 MB) dan coba lagi.',
        ]);

        $data['file_path']  = $request->file('file')->store('arsip', 'public');
        $data['created_by'] = auth()->id();
        unset($data['file']);

        $arsip = Arsip::create($data);

        ActivityLog::catat('tambah', "Menambah arsip {$arsip->nomor_arsip} - {$arsip->judul}");

        return redirect()->route('arsip.show', $arsip)->with('success', 'Arsip berhasil ditambahkan.');
    }

    public function show(Arsip $arsip)
    {
        $this->authorize('view', $arsip);

        $arsip->load(['kategori', 'pembuat']);

        return view('arsip.show', compact('arsip'));
    }

    public function edit(Arsip $arsip)
    {
        $this->authorize('update', $arsip);

        $kategoris = Kategori::orderBy('nama')->get();

        return view('arsip.edit', compact('arsip', 'kategoris'));
    }

    public function update(Request $request, Arsip $arsip)
    {
        $this->authorize('update', $arsip);

        $data = $request->validate([
            'nomor_arsip'   => ['required', 'string', 'max:100', Rule::unique('arsips', 'nomor_arsip')->ignore($arsip->id)],
            'judul'         => ['required', 'string', 'max:255'],
            'kategori_id'   => ['required', 'exists:kategoris,id'],
            'tanggal_arsip' => ['required', 'date'],
            'deskripsi'     => ['nullable', 'string'],
            'file'          => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'file.mimes'    => 'File harus berformat PDF.',
            'file.max'      => 'Ukuran file maksimal 5 MB.',
            'file.uploaded' => 'Upload gagal. Cek ukuran file (maks 5 MB) dan coba lagi.',
        ]);

        if ($request->hasFile('file')) {
            if ($arsip->file_path) {
                Storage::disk('public')->delete($arsip->file_path);
            }
            $data['file_path'] = $request->file('file')->store('arsip', 'public');
        }
        unset($data['file']);

        $arsip->update($data);

        ActivityLog::catat('edit', "Mengubah arsip {$arsip->nomor_arsip} - {$arsip->judul}");

        return redirect()->route('arsip.show', $arsip)->with('success', 'Arsip berhasil diperbarui.');
    }

    public function destroy(Arsip $arsip)
    {
        $this->authorize('delete', $arsip);

        $arsip->delete(); // soft delete, file PDF tetap disimpan di storage

        ActivityLog::catat('hapus', "Menghapus arsip {$arsip->nomor_arsip} - {$arsip->judul}");

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil dihapus.');
    }

    /**
     * Buka PDF langsung di browser (tab baru).
     */
    public function preview(Arsip $arsip)
    {
        $this->authorize('view', $arsip);
        $this->pastikanFileAda($arsip);

        return Storage::disk('public')->response(
            $arsip->file_path,
            $this->namaFile($arsip),
            ['Content-Type' => 'application/pdf'],
            'inline'
        );
    }

    /**
     * Unduh PDF dengan nama file yang rapi.
     */
    public function download(Arsip $arsip)
    {
        $this->authorize('view', $arsip);
        $this->pastikanFileAda($arsip);

        return Storage::disk('public')->download(
            $arsip->file_path,
            $this->namaFile($arsip),
            ['Content-Type' => 'application/pdf']
        );
    }

    private function pastikanFileAda(Arsip $arsip): void
    {
        abort_unless(
            $arsip->file_path && Storage::disk('public')->exists($arsip->file_path),
            404,
            'File arsip tidak ditemukan.'
        );
    }

    // Contoh hasil: ars-2026-001-surat-undangan-rapat.pdf
    private function namaFile(Arsip $arsip): string
    {
        return Str::slug($arsip->nomor_arsip . ' ' . $arsip->judul) . '.pdf';
    }
}