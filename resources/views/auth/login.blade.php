<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Pengarsipan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f6fb; }
        .auth-wrap { min-height: 100vh; display: flex; }

        .auth-side {
            position: relative; overflow: hidden; flex: 1.1;
            background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 60%, #7c3aed 100%);
            color: #fff; padding: 3rem;
            flex-direction: column; justify-content: center;
        }
        .auth-side::before, .auth-side::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .07); }
        .auth-side::before { width: 380px; height: 380px; right: -120px; top: -120px; }
        .auth-side::after { width: 260px; height: 260px; left: -90px; bottom: -90px; }
        .auth-side > * { position: relative; z-index: 1; }
        .auth-side .logo {
            width: 56px; height: 56px; border-radius: 1rem; background: rgba(255, 255, 255, .15);
            display: flex; align-items: center; justify-content: center; font-size: 1.7rem; margin-bottom: 1.5rem;
        }
        .auth-side h1 { font-weight: 700; font-size: 2.1rem; line-height: 1.25; margin-bottom: .75rem; }
        .auth-side p.lead { opacity: .85; max-width: 420px; }
        .points { list-style: none; padding: 0; margin: 2rem 0 0; }
        .points li { display: flex; align-items: center; gap: .75rem; margin-bottom: .9rem; opacity: .95; }
        .points i {
            width: 34px; height: 34px; border-radius: .6rem; background: rgba(255, 255, 255, .14);
            display: flex; align-items: center; justify-content: center;
        }

        .auth-form { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1.25rem; }
        .auth-box { width: 100%; max-width: 400px; }
        .auth-box .mobile-logo {
            width: 56px; height: 56px; border-radius: 1rem; background: #eef2ff; color: #4f46e5;
            display: flex; align-items: center; justify-content: center; font-size: 1.7rem; margin-bottom: 1.25rem;
        }

        .form-control { border-radius: .7rem; padding: .7rem .9rem; border-color: #dfe3ee; }
        .form-control:focus { border-color: #818cf8; box-shadow: 0 0 0 .22rem rgba(99, 102, 241, .15); }
        .input-group-text { background: #fff; border-color: #dfe3ee; color: #6b7280; }
        .input-group > .form-control:not(:first-child) { border-left: 0; }
        .btn-primary {
            background: #4f46e5; border-color: #4f46e5; padding: .75rem; font-weight: 600; border-radius: .7rem;
            box-shadow: 0 6px 16px rgba(79, 70, 229, .3);
        }
        .btn-primary:hover { background: #4338ca; border-color: #4338ca; }
    </style>
</head>
<body>
<div class="auth-wrap">

    <div class="auth-side d-none d-lg-flex">
        <div class="logo"><i class="bi bi-archive-fill"></i></div>
        <h1>Arsip rapi,<br>akses jadi cepat.</h1>
        <p class="lead">Simpan, cari, dan kelola dokumen arsip internal dalam satu tempat.</p>

        <ul class="points">
            <li><i class="bi bi-cloud-arrow-up"></i> Upload arsip dalam format PDF</li>
            <li><i class="bi bi-search"></i> Cari dan filter arsip dengan mudah</li>
            <li><i class="bi bi-shield-check"></i> Hak akses terpisah untuk Admin dan User</li>
        </ul>
    </div>

    <div class="auth-form">
        <div class="auth-box">
            <div class="mobile-logo d-lg-none"><i class="bi bi-archive-fill"></i></div>

            <h3 class="fw-bold mb-1">Selamat datang</h3>
            <p class="text-muted mb-4">Silakan login untuk melanjutkan.</p>

            @error('username')
                <div class="alert alert-danger py-2 border-0">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                </div>
            @enderror

            <form method="POST" action="{{ route('login.process') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-medium">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" value="{{ old('username') }}" class="form-control"
                               placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="Masukkan password" required>
                        <button type="button" class="input-group-text" id="togglePassword" aria-label="Lihat password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>

</div>

<script>
    const pw = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');
    toggle.addEventListener('click', () => {
        const show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        toggle.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
</script>
</body>
</html>