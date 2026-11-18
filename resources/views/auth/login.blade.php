<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#07111f">
    <title>Masuk - Pengarsipan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            color-scheme: dark;
            --auth-ink: #f3f7ff;
            --auth-muted: #9eacc3;
            --auth-cyan: #67d9f4;
            --auth-blue: #4e8dff;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            color: var(--auth-ink);
            font-family: 'Inter', sans-serif;
            background: #07111f;
        }

        .auth-scene {
            position: relative;
            isolation: isolate;
            display: grid;
            min-height: 100vh;
            min-height: 100svh;
            place-items: center;
            overflow: hidden;
            padding: 2rem 1rem;
            background: linear-gradient(125deg, #07111f, #0a1c35, #101b3d, #071827, #07111f);
            background-size: 280% 280%;
            animation: gradient-drift 24s ease-in-out infinite;
        }

        .auth-scene::before,
        .auth-scene::after {
            position: absolute;
            z-index: -1;
            content: "";
            pointer-events: none;
            border-radius: 50%;
            will-change: transform;
        }

        .auth-scene::before {
            top: 7%;
            left: 9%;
            width: clamp(12rem, 30vw, 25rem);
            aspect-ratio: 1;
            background: radial-gradient(circle, rgba(43, 145, 255, .25), rgba(43, 145, 255, 0) 70%);
            animation: orb-float 19s ease-in-out infinite alternate;
        }

        .auth-scene::after {
            right: 4%;
            bottom: 2%;
            width: clamp(13rem, 34vw, 29rem);
            aspect-ratio: 1;
            background: radial-gradient(circle, rgba(69, 221, 239, .17), rgba(92, 89, 224, .09) 42%, transparent 72%);
            animation: orb-float 23s ease-in-out -7s infinite alternate-reverse;
        }

        .auth-orb {
            position: absolute;
            z-index: -1;
            top: 52%;
            left: 70%;
            width: clamp(8rem, 18vw, 15rem);
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(135, 103, 255, .2), transparent 70%);
            pointer-events: none;
            animation: orb-float 21s ease-in-out -4s infinite alternate;
        }

        .auth-card {
            width: min(100%, 440px);
            padding: clamp(1.5rem, 5vw, 2.75rem);
            border: 1px solid rgba(185, 211, 255, .16);
            border-radius: 1.5rem;
            background: linear-gradient(145deg, rgba(20, 36, 59, .97), rgba(12, 25, 44, .97));
            box-shadow: 0 28px 80px rgba(0, 0, 0, .4), inset 0 1px 0 rgba(255, 255, 255, .045);
            animation: card-enter .7s cubic-bezier(.2, .75, .25, 1) both;
        }

        .brand-mark {
            display: grid;
            width: 3.25rem;
            height: 3.25rem;
            place-items: center;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(103, 217, 244, .25);
            border-radius: 1rem;
            color: #9ceafa;
            background: linear-gradient(145deg, rgba(44, 154, 203, .22), rgba(69, 100, 212, .16));
            box-shadow: 0 8px 24px rgba(25, 150, 202, .12);
            font-size: 1.5rem;
        }

        .auth-eyebrow {
            margin-bottom: .45rem;
            color: var(--auth-cyan);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .auth-title { color: var(--auth-ink); font-size: clamp(1.65rem, 5vw, 1.9rem); letter-spacing: -.04em; }
        .auth-description { color: var(--auth-muted); font-size: .94rem; }

        .form-label { margin-bottom: .5rem; color: #dce6f5; font-size: .86rem; font-weight: 600; }

        .auth-input-group .input-group-text,
        .auth-input-group .form-control {
            min-height: 3.15rem;
            border-color: rgba(173, 196, 229, .18);
            color: var(--auth-ink);
            background-color: rgba(5, 15, 29, .55);
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .auth-input-group .input-group-text { color: #91a8c8; }
        .auth-input-group > .input-group-text:first-child { padding-inline: 1rem; border-radius: .8rem 0 0 .8rem; }
        .auth-input-group > .form-control { min-width: 0; padding: .75rem .85rem; border-left: 0; }
        .auth-input-group > .form-control::placeholder { color: #7486a0; }
        .auth-input-group > .form-control:last-of-type { border-radius: 0 .8rem .8rem 0; }

        .auth-input-group:focus-within .input-group-text,
        .auth-input-group:focus-within .form-control {
            border-color: rgba(103, 217, 244, .78);
            background-color: rgba(6, 19, 35, .92);
        }

        .auth-input-group:focus-within .form-control { box-shadow: none; }
        .auth-input-group:focus-within { border-radius: .8rem; box-shadow: 0 0 0 .22rem rgba(78, 168, 255, .18); }

        .password-toggle {
            cursor: pointer;
            border-left: 0 !important;
            border-radius: 0 .8rem .8rem 0 !important;
        }

        .password-toggle:hover { color: var(--auth-cyan); }
        .password-toggle:focus-visible { position: relative; z-index: 2; outline: 2px solid var(--auth-cyan); outline-offset: 2px; }

        .form-check-input { border-color: rgba(173, 196, 229, .42); background-color: rgba(5, 15, 29, .6); }
        .form-check-input:checked { border-color: var(--auth-blue); background-color: var(--auth-blue); }
        .form-check-input:focus { border-color: var(--auth-cyan); box-shadow: 0 0 0 .2rem rgba(103, 217, 244, .2); }
        .form-check-label { color: #b8c6da; font-size: .88rem; }

        .auth-alert { color: #ffd6dc; border: 1px solid rgba(255, 122, 145, .28); border-radius: .8rem; background: rgba(132, 35, 57, .22); }

        .btn-auth {
            min-height: 3.2rem;
            border: 1px solid #56bfe9;
            border-radius: .8rem;
            color: #061423;
            background: linear-gradient(105deg, #70dff0, #6daeff);
            box-shadow: 0 10px 26px rgba(58, 160, 226, .2);
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }

        .btn-auth:hover { color: #061423; filter: brightness(1.06); transform: translateY(-2px); box-shadow: 0 14px 30px rgba(58, 160, 226, .3); }
        .btn-auth:active { color: #061423 !important; transform: translateY(0) scale(.99); box-shadow: 0 5px 14px rgba(58, 160, 226, .22); }
        .btn-auth:focus-visible { outline: 3px solid rgba(148, 234, 255, .85); outline-offset: 3px; }

        @keyframes gradient-drift { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes orb-float { from { transform: translate3d(-10px, -12px, 0) scale(.96); } to { transform: translate3d(18px, 16px, 0) scale(1.04); } }
        @keyframes card-enter { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 575.98px) {
            .auth-scene { padding: 1.25rem .85rem; }
            .auth-card { border-radius: 1.2rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
<main class="auth-scene">
    <span class="auth-orb" aria-hidden="true"></span>

    <section class="auth-card" aria-labelledby="auth-title">
        <div class="brand-mark" aria-hidden="true"><i class="bi bi-archive-fill"></i></div>
        <p class="auth-eyebrow">Pengarsipan</p>
        <h1 class="auth-title fw-bold mb-2" id="auth-title">Selamat datang</h1>
        <p class="auth-description mb-4">Masuk untuk mengelola arsip Anda dengan aman.</p>

        @error('username')
            <div class="alert auth-alert py-2 px-3 mb-4" role="alert">
                <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i> {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text" aria-hidden="true"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" id="username" value="{{ old('username') }}"
                           class="form-control" placeholder="Masukkan username" required autofocus autocomplete="username">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control"
                           placeholder="Masukkan password" required autocomplete="current-password">
                    <button type="button" class="input-group-text password-toggle" id="togglePassword"
                            aria-label="Lihat password" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-auth w-100">Masuk <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></button>
        </form>
    </section>
</main>

<script>
    (function () {
        const password = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        const icon = toggle.querySelector('i');

        toggle.addEventListener('click', function () {
            const tampil = password.type === 'password';
            password.type = tampil ? 'text' : 'password';
            toggle.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Lihat password');
            toggle.setAttribute('aria-pressed', String(tampil));
            icon.classList.toggle('bi-eye', !tampil);
            icon.classList.toggle('bi-eye-slash', tampil);
        });
    })();
</script>
</body>
</html>
