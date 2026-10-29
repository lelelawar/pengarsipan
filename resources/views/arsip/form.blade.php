<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-medium">Nomor Arsip</label>
        <input type="text" name="nomor_arsip" value="{{ old('nomor_arsip', $arsip?->nomor_arsip) }}"
               class="form-control @error('nomor_arsip') is-invalid @enderror" placeholder="Contoh: ARS-2026-001" required>
        @error('nomor_arsip') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium">Tanggal Arsip</label>
        <input type="date" name="tanggal_arsip" value="{{ old('tanggal_arsip', $arsip?->tanggal_arsip?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="form-control @error('tanggal_arsip') is-invalid @enderror" required>
        @error('tanggal_arsip') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label fw-medium">Judul</label>
        <input type="text" name="judul" value="{{ old('judul', $arsip?->judul) }}"
               class="form-control @error('judul') is-invalid @enderror" required>
        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-medium">Kategori</label>
        <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
            <option value="">-- Pilih --</option>
            @foreach($kategoris as $kategori)
                <option value="{{ $kategori->id }}" @selected(old('kategori_id', $arsip?->kategori_id) == $kategori->id)>
                    {{ $kategori->nama }}
                </option>
            @endforeach
        </select>
        @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-medium">Deskripsi</label>
        <textarea name="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $arsip?->deskripsi) }}</textarea>
        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-medium">File Arsip (PDF)</label>
        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
               accept="application/pdf,.pdf" {{ $arsip ? '' : 'required' }}>
        <div class="form-text">Hanya file PDF. Maksimal 5 MB.</div>
        @error('file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        @if($arsip?->file_path)
            <div class="mt-2 small">
                File saat ini:
                        <a href="{{ route('arsip.preview', $arsip) }}" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i> {{ basename($arsip->file_path) }}
                </a>
                <span class="text-muted">(kosongkan kalau tidak mau mengganti)</span>
            </div>
        @endif
    </div>
</div>