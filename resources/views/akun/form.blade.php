<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-medium">Nama Lengkap</label>
        <input type="text" name="nama" value="{{ old('nama', $user?->nama) }}"
               class="form-control @error('nama') is-invalid @enderror" required>
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-medium">Username</label>
        <input type="text" name="username" value="{{ old('username', $user?->username) }}"
               class="form-control @error('username') is-invalid @enderror" autocomplete="off" required>
        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-medium">Role</label>
        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
            <option value="user" @selected(old('role', $user?->role) === 'user')>User</option>
            <option value="admin" @selected(old('role', $user?->role) === 'admin')>Admin</option>
        </select>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-medium">Password</label>
        <input type="password" name="password" autocomplete="new-password"
               class="form-control @error('password') is-invalid @enderror" {{ $user ? '' : 'required' }}>
        @if($user) <div class="form-text">Kosongkan kalau tidak mau mengganti.</div> @endif
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-medium">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" autocomplete="new-password"
               class="form-control" {{ $user ? '' : 'required' }}>
    </div>
</div>