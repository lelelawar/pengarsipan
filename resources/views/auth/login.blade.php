<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#07111f">
    <title>Login - SIKAD</title>

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
            --pointer-x: 50%;
            --pointer-y: 50%;
            --ripple-strength: 0;
        }

        .auth-liquid {
            position: absolute;
            z-index: -1;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            background:
                radial-gradient(ellipse 28rem 22rem at var(--pointer-x) var(--pointer-y), rgba(50, 171, 232, var(--ripple-strength)), transparent 72%),
                radial-gradient(ellipse 19rem 14rem at calc(var(--pointer-x) + 8%) calc(var(--pointer-y) - 7%), rgba(130, 104, 255, var(--ripple-strength)), transparent 74%);
            transition: background 180ms ease-out;
        }

        .auth-liquid::before,
        .auth-liquid::after {
            position: absolute;
            content: "";
            inset: -18%;
            pointer-events: none;
            will-change: transform, opacity;
        }

        .auth-liquid::before {
            opacity: .55;
            background: repeating-radial-gradient(ellipse at var(--pointer-x) var(--pointer-y), transparent 0 44px, rgba(103, 217, 244, .055) 46px 48px, transparent 51px 78px);
            transform: scale(1);
            transition: opacity .65s ease, transform .8s ease-out;
            animation: liquid-current 32s ease-in-out infinite alternate;
        }

        .auth-liquid::after {
            opacity: .22;
            background: radial-gradient(ellipse 45% 32% at 28% 72%, rgba(18, 111, 190, .18), transparent 72%), radial-gradient(ellipse 34% 28% at 77% 25%, rgba(92, 79, 196, .16), transparent 74%);
            transform: translate3d(0, 0, 0);
            transition: transform 1.2s ease-out;
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
            background: linear-gradient(145deg, rgba(20, 43, 68, .78), rgba(12, 27, 49, .72));
            -webkit-backdrop-filter: blur(20px) saturate(135%);
            backdrop-filter: blur(20px) saturate(135%);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .38), inset 0 1px 0 rgba(255, 255, 255, .12), inset 0 0 28px rgba(103, 217, 244, .035), 0 0 34px rgba(82, 122, 220, .07);
            animation: card-enter .7s cubic-bezier(.2, .75, .25, 1) both;
        }

        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            .auth-card { background: linear-gradient(145deg, rgba(20, 39, 63, .96), rgba(12, 25, 44, .95)); }
        }

        .auth-brand { text-align: center; }

        .brand-mark {
            display: grid;
            width: 3.25rem;
            height: 3.25rem;
            place-items: center;
            margin: 0 auto 1.5rem;
            padding: .25rem;
            border: 1px solid rgba(103, 217, 244, .25);
            border-radius: 1rem;
            color: #9ceafa;
            background: linear-gradient(145deg, rgba(44, 154, 203, .22), rgba(69, 100, 212, .16));
            box-shadow: 0 8px 24px rgba(25, 150, 202, .12);
            font-size: 1.5rem;
        }
        .brand-mark img { width: 100%; height: 100%; object-fit: contain; }

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
            background-color: rgba(5, 15, 29, .46);
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
            background-color: rgba(6, 19, 35, .84);
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
        @keyframes liquid-current { from { transform: translate3d(-1.2%, -.7%, 0) scale(1); } to { transform: translate3d(1.2%, .7%, 0) scale(1.035); } }
        @keyframes orb-float { from { transform: translate3d(-10px, -12px, 0) scale(.96); } to { transform: translate3d(18px, 16px, 0) scale(1.04); } }
        @keyframes card-enter { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 575.98px) {
            .auth-scene { padding: 1.25rem .85rem; }
            .auth-card { border-radius: 1.2rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            .auth-liquid { display: none; }
        }
    </style>
</head>
<body>
<main class="auth-scene">
    <div class="auth-liquid" aria-hidden="true"></div>
    <span class="auth-orb" aria-hidden="true"></span>

    <section class="auth-card" aria-labelledby="auth-title">
        <div class="auth-brand">
            <div class="brand-mark">
                <img src="{{ asset('images/logo-kapal.png') }}" alt="Logo SIKAD">
            </div>
            <p class="auth-eyebrow">Sistem Informasi Kearsipan Digital</p>
            <h1 class="auth-title fw-bold mb-2" id="auth-title">SIKAD</h1>
            <p class="auth-description mb-4">Masuk untuk mengelola arsip Anda.</p>
        </div>

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

        const scene = document.querySelector('.auth-scene');
        const card = document.querySelector('.auth-card');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

        if (!reducedMotion.matches && finePointer.matches) {
            let targetX = 50;
            let targetY = 50;
            let targetStrength = 0;
            let currentX = 50;
            let currentY = 50;
            let currentStrength = 0;
            let frame = 0;

            function animateLiquid() {
                frame = 0;
                currentX += (targetX - currentX) * .12;
                currentY += (targetY - currentY) * .12;
                currentStrength += (targetStrength - currentStrength) * .1;

                scene.style.setProperty('--pointer-x', currentX.toFixed(2) + '%');
                scene.style.setProperty('--pointer-y', currentY.toFixed(2) + '%');
                scene.style.setProperty('--ripple-strength', currentStrength.toFixed(3));

                if (Math.abs(targetX - currentX) > .08 || Math.abs(targetY - currentY) > .08 || Math.abs(targetStrength - currentStrength) > .005) {
                    frame = requestAnimationFrame(animateLiquid);
                }
            }

            scene.addEventListener('pointermove', function (event) {
                const bounds = scene.getBoundingClientRect();
                targetX = Math.max(0, Math.min(100, ((event.clientX - bounds.left) / bounds.width) * 100));
                targetY = Math.max(0, Math.min(100, ((event.clientY - bounds.top) / bounds.height) * 100));

                const cardBounds = card.getBoundingClientRect();
                const centerX = cardBounds.left + cardBounds.width / 2;
                const centerY = cardBounds.top + cardBounds.height / 2;
                const distance = Math.hypot(event.clientX - centerX, event.clientY - centerY);
                const influence = Math.max(cardBounds.width, cardBounds.height) * 1.25;
                targetStrength = Math.max(0, 1 - distance / influence);

                if (!frame) frame = requestAnimationFrame(animateLiquid);
            }, { passive: true });

            scene.addEventListener('pointerleave', function () {
                targetStrength = 0;
                if (!frame) frame = requestAnimationFrame(animateLiquid);
            }, { passive: true });
        }
    })();
</script>
</body>
</html>
