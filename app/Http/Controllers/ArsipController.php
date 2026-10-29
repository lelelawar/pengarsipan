<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Arsip;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArsipController extends Controller
{
    private const MIME_PRATINJAU = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ];

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
            ->when($request->jenis, fn ($query, $jenis) => $query->jenis($jenis))
            ->when($request->periode === 'bulan_ini', fn ($query) => $query
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month))
            ->when($request->periode === 'tahun_ini', fn ($query) => $query
                ->whereYear('created_at', now()->year))
            ->latest() // terbaru diunggah di atas
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
            'file'          => $this->aturanFile(true),
        ], $this->pesanFile());

        $data['file_path']  = $this->simpanFile($request->file('file'));
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
            'file'          => $this->aturanFile(false),
        ], $this->pesanFile());

        if ($request->hasFile('file')) {
            if ($arsip->file_path) {
                Storage::disk('public')->delete($arsip->file_path);
            }
            $data['file_path'] = $this->simpanFile($request->file('file'));
        }
        unset($data['file']);

        $arsip->update($data);

        ActivityLog::catat('edit', "Mengubah arsip {$arsip->nomor_arsip} - {$arsip->judul}");

        return redirect()->route('arsip.show', $arsip)->with('success', 'Arsip berhasil diperbarui.');
    }

    public function destroy(Arsip $arsip)
    {
        $this->authorize('delete', $arsip);

        $arsip->delete(); // soft delete, file tetap disimpan di storage

        ActivityLog::catat('hapus', "Menghapus arsip {$arsip->nomor_arsip} - {$arsip->judul}");

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil dihapus.');
    }

    /**
     * Buka file di browser (PDF dan gambar).
     */
    public function preview(Arsip $arsip)
    {
        $this->authorize('view', $arsip);
        $this->pastikanFileAda($arsip);

        abort_unless($arsip->bisa_pratinjau, 404, 'Format file ini tidak bisa dipratinjau. Silakan unduh.');

        return Storage::disk('public')->response(
            $arsip->file_path,
            $this->namaFile($arsip),
            [
                'Content-Type'           => self::MIME_PRATINJAU[$arsip->ekstensi] ?? 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline'
        );
    }

    /**
     * Unduh file dengan nama yang rapi.
     */
    public function download(Arsip $arsip)
    {
        $this->authorize('view', $arsip);
        $this->pastikanFileAda($arsip);

        return Storage::disk('public')->download(
            $arsip->file_path,
            $this->namaFile($arsip),
            ['X-Content-Type-Options' => 'nosniff']
        );
    }

    // ---------- Helper ----------

    private function aturanFile(bool $wajib): array
    {
        return [
            $wajib ? 'required' : 'nullable',
            'file',
            'max:10240', // 10 MB
            function ($atribut, $nilai, $gagal) {
                $ekstensi = strtolower($nilai->getClientOriginalExtension());

                if (!in_array($ekstensi, Arsip::semuaEkstensi(), true)) {
                    $gagal('Format file tidak didukung. Gunakan PDF atau gambar (JPG, PNG, GIF, WEBP).');
                }
            },
        ];
    }

    private function pesanFile(): array
    {
        return [
            'file.required' => 'File arsip wajib diupload.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
            'file.uploaded' => 'Upload gagal. Cek ukuran file (maks 10 MB) dan coba lagi.',
            'nomor_arsip.unique' => 'Nomor arsip sudah dipakai (bisa jadi oleh arsip yang ada di Sampah).',
        ];
    }

    // Nama di server diacak (UUID), ekstensi diambil dari daftar yang sudah divalidasi
    private function simpanFile(UploadedFile $file): string
    {
        $ekstensi = strtolower($file->getClientOriginalExtension());

        return $file->storeAs('arsip', Str::uuid() . '.' . $ekstensi, 'public');
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
        return Str::slug($arsip->nomor_arsip . ' ' . $arsip->judul) . '.' . $arsip->ekstensi;
    }
}