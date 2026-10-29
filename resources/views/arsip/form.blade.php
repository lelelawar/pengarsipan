@push('styles')
<style>
    .dropzone {
        position: relative; border: 2px dashed #c7d2fe; border-radius: 1rem;
        background: #f8f9ff; padding: 1.7rem 1rem; text-align: center; transition: .15s;
    }
    .dropzone:hover, .dropzone.dragover { border-color: #6366f1; background: #eef2ff; }
    .dropzone.zona-error { border-color: #dc3545; background: #fff5f5; }
    .dropzone input[type=file] {
        position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
    }
    .dz-icon { font-size: 2.3rem; color: #6366f1; display: block; margin-bottom: .35rem; }
    .dz-file { display: flex; align-items: center; justify-content: center; gap: .6rem; flex-wrap: wrap; }
    .dz-file i { font-size: 1.6rem; color: #10b981; }
</style>
@endpush

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
        <label class="form-label fw-medium">File Arsip</label>

        <div class="dropzone @error('file') zona-error @enderror" id="dropzone">
            <input type="file" name="file" id="fileInput"
                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                   {{ $arsip ? '' : 'required' }}>

            <div id="dzKosong">
                <i class="bi bi-cloud-arrow-up dz-icon"></i>
                <div class="fw-medium">Tarik file ke sini atau <span class="text-primary">klik untuk memilih</span></div>
                <div class="small text-muted mt-1">PDF atau gambar (JPG, PNG, GIF, WEBP). Maksimal 10 MB.</div>
            </div>

            <div id="dzTerpilih" class="dz-file d-none">
                <i class="bi bi-file-earmark-check"></i>
                <span class="fw-medium" id="dzNama"></span>
                <small class="text-muted" id="dzUkuran"></small>
                <small class="text-primary">(klik untuk mengganti)</small>
            </div>
        </div>

        <div class="text-danger small mt-1 d-none" id="dzError"></div>
        @error('file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

        @if($arsip?->file_path)
            <div class="mt-2 small">
                File saat ini:
                <a href="{{ $arsip->bisa_pratinjau ? route('arsip.preview', $arsip) : route('arsip.download', $arsip) }}" target="_blank">
                    <i class="bi {{ $arsip->ikon }}" style="color: {{ $arsip->warna_ikon }}"></i>
                    {{ strtoupper($arsip->ekstensi) }}
                </a>
                <span class="text-muted">(kosongkan kalau tidak mau mengganti)</span>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const zona     = document.getElementById('dropzone');
        const input    = document.getElementById('fileInput');
        const kosong   = document.getElementById('dzKosong');
        const terpilih = document.getElementById('dzTerpilih');
        const nama     = document.getElementById('dzNama');
        const ukuran   = document.getElementById('dzUkuran');
        const galat    = document.getElementById('dzError');

        const diizinkan = @json(\App\Models\Arsip::semuaEkstensi());
        const maksimal  = 10 * 1024 * 1024; // 10 MB

        function formatUkuran(b) {
            if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
            return Math.max(1, Math.round(b / 1024)) + ' KB';
        }

        function tampilkan() {
            const f = input.files[0];
            galat.classList.add('d-none');
            zona.classList.remove('zona-error');

            if (!f) {
                kosong.classList.remove('d-none');
                terpilih.classList.add('d-none');
                return;
            }

            const ekstensi = f.name.split('.').pop().toLowerCase();
            let pesan = '';

            if (!diizinkan.includes(ekstensi)) {
                pesan = 'Format file tidak didukung. Gunakan PDF atau gambar (JPG, PNG, GIF, WEBP).';
            } else if (f.size > maksimal) {
                pesan = 'Ukuran file melebihi 10 MB.';
            }

            if (pesan) {
                input.value = '';
                galat.textContent = pesan;
                galat.classList.remove('d-none');
                zona.classList.add('zona-error');
                kosong.classList.remove('d-none');
                terpilih.classList.add('d-none');
                return;
            }

            nama.textContent = f.name;
            ukuran.textContent = formatUkuran(f.size);
            kosong.classList.add('d-none');
            terpilih.classList.remove('d-none');
        }

        input.addEventListener('change', tampilkan);
        ['dragenter', 'dragover'].forEach(e => input.addEventListener(e, () => zona.classList.add('dragover')));
        ['dragleave', 'drop'].forEach(e => input.addEventListener(e, () => zona.classList.remove('dragover')));
    })();
</script>
@endpush