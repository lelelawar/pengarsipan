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
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 1rem;
        }
        .login-card { width: 100%; max-width: 410px; border: 0; border-radius: 1.2rem; box-shadow: 0 20px 50px rgba(0,0,0,.2); }
        .logo {
            width: 64px; height: 64px; border-radius: 1rem;
            background: #eef2ff; color: #4f46e5; font-size: 1.9rem;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;
        }
        .form-control { padding: .7rem .9rem; }
        .btn-primary { background: #4f46e5; border-color: #4f46e5; padding: .7rem; font-weight: 600; }
        .btn-primary:hover { background: #4338ca; border-color: #4338ca; }
    </style>
</head>
<body>
    <div class="card login-card p-4 p-md-5">
        <div class="logo"><i class="bi bi-archive-fill"></i></div>
        <h4 class="text-center fw-bold mb-1">Pengarsipan</h4>
        <p class="text-center text-muted mb-4">Silakan login untuk melanjutkan</p>

        @error('username')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-medium">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
    </div>
</body>
</html>