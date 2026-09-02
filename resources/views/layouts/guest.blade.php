<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Autentikasi') &middot; SPI &mdash; PT Pindad Enjiniring</title>    <link rel="icon" type="image/png" href="{{ asset('images/PEI.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* Lembar kendali teknik — selaras dengan halaman masuk */
        :root {
            --tinta: #10263f;
            --tinta-2: #16304f;
            --baja: #51677e;
            --kertas: #e8edf2;
            --lembar: #ffffff;
            --garis: #c9d4de;
            --garis-halus: #dde5ec;
            --kuning: #ffc72c;
            --merah: #c6362b;
            --hijau: #1e8e52;
            --ch-biru: #3f7fd4;

            --font-display: 'Chakra Petch', sans-serif;
            --font-body: 'Plus Jakarta Sans', system-ui, sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        * { font-family: var(--font-body); }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            background:
                linear-gradient(rgba(16, 38, 63, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16, 38, 63, .045) 1px, transparent 1px),
                var(--kertas);
            background-size: 44px 44px, 44px 44px, auto;
            color: var(--tinta);
        }

        /* Lembar dokumen berbingkai dalam */
        .agx-card {
            position: relative;
            width: 100%; max-width: 440px;
            background: var(--lembar);
            border: 1px solid #aebccb;
            box-shadow: 0 24px 60px -28px rgba(16, 38, 63, .35), 0 2px 6px rgba(16, 38, 63, .08);
            padding: clamp(1.8rem, 4vw, 2.6rem) clamp(1.6rem, 4vw, 2.4rem);
        }
        .agx-card::after {
            content: "";
            position: absolute;
            inset: 5px;
            border: 1.5px solid var(--tinta);
            pointer-events: none;
        }
        /* Pita rambu kuning-tinta */
        .agx-pita {
            position: absolute;
            top: -14px; left: 50%;
            transform: translateX(-50%) rotate(-2deg);
            width: 132px; height: 18px;
            background: repeating-linear-gradient(
                -45deg,
                var(--kuning) 0 10px,
                var(--tinta) 10px 20px
            );
            opacity: .95;
            pointer-events: none;
        }

        .agx-brand { display: flex; justify-content: center; margin-bottom: 1.9rem; position: relative; z-index: 1; }
        .agx-brand img { width: 168px; height: auto; display: block; }

        .agx-heading {
            font-family: var(--font-display);
            font-weight: 700; font-size: 1.35rem;
            letter-spacing: .02em; text-transform: uppercase;
            color: var(--tinta); margin-bottom: .3rem;
            position: relative; z-index: 1;
        }
        .agx-sub { color: var(--baja); font-size: .88rem; margin-bottom: 1.6rem; line-height: 1.65; position: relative; z-index: 1; }

        .agx-label {
            font-family: var(--font-mono);
            font-weight: 500; font-size: .68rem;
            letter-spacing: .18em; text-transform: uppercase;
            color: var(--baja); margin-bottom: .45rem; display: block;
            position: relative; z-index: 1;
        }
        .agx-input {
            width: 100%;
            font-size: .92rem;
            color: var(--tinta);
            background: var(--lembar);
            border: 1.5px solid var(--garis);
            border-radius: 2px;
            padding: .68rem .95rem;
            transition: border-color .18s ease, box-shadow .18s ease;
            position: relative; z-index: 1;
        }
        .agx-input::placeholder { color: #9fb0bf; }
        .agx-input:hover { border-color: var(--baja); }
        .agx-input:focus {
            outline: none;
            border-color: var(--tinta);
            background: var(--lembar);
            box-shadow: 0 0 0 3px rgba(255, 199, 44, .4);
        }
        .agx-input.is-invalid { border-color: var(--merah); }

        .agx-error { font-size: .78rem; color: var(--merah); margin-top: .35rem; position: relative; z-index: 1; }

        .agx-alert {
            border: none; border-left: 3px solid transparent;
            border-radius: 2px;
            font-size: .85rem; padding: .75rem 1rem; line-height: 1.55;
            position: relative; z-index: 1;
        }
        .agx-alert-success { background: #e5f4ec; color: #135c37; border-left-color: var(--hijau); }
        .agx-alert-danger { background: #fbeeed; color: #7c2320; border-left-color: var(--merah); }
        .agx-alert-info { background: #e9f1fb; color: #1c4a86; border-left-color: var(--ch-biru); }

        .agx-submit {
            width: 100%;
            display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
            border: 0; border-radius: 2px;
            background: var(--tinta); color: var(--lembar);
            font-family: var(--font-display);
            font-weight: 600; font-size: .93rem;
            letter-spacing: .12em; text-transform: uppercase;
            padding: .82rem 1rem;
            cursor: pointer;
            transition: background .18s ease, box-shadow .18s ease, transform .12s ease;
            position: relative; z-index: 1;
        }
        .agx-submit:hover { background: var(--tinta-2); box-shadow: 0 10px 22px -10px rgba(16, 38, 63, .55); }
        .agx-submit:active { transform: translateY(1px); }
        .agx-submit:focus-visible { outline: 3px solid rgba(255, 199, 44, .8); outline-offset: 2px; }

        .agx-link {
            font-size: .85rem; font-weight: 600; color: var(--tinta); text-decoration: none;
            text-decoration: underline; text-decoration-color: var(--kuning);
            text-decoration-thickness: 2px; text-underline-offset: 3px;
            position: relative; z-index: 1;
        }
        .agx-link:hover { color: var(--tinta-2); text-decoration-thickness: 3px; }

        .agx-foot { margin-top: 1.8rem; text-align: center; font-size: .75rem; color: var(--baja); }
        .agx-foot-mono {
            font-family: var(--font-mono);
            font-size: .66rem; letter-spacing: .18em; text-transform: uppercase;
        }

        :focus-visible { outline: 2px solid var(--tinta); outline-offset: 2px; }

        @media (max-width: 575.98px) {
            body { padding: 2rem .9rem; }
            .agx-card { padding: 1.8rem 1.3rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <main class="agx-card">
        <span class="agx-pita" aria-hidden="true"></span>

        <div class="agx-brand">
            <a href="{{ route('login') }}" aria-label="Ke halaman login">
                <img src="{{ asset('images/PEILongLogo.png') }}" alt="PT Pindad Enjiniring Indonesia">
            </a>
        </div>

        @yield('content')
    </main>

    <footer class="agx-foot">
        <span class="agx-foot-mono d-block mb-1">Sistem Audit Internal</span>
        &copy; {{ date('Y') }} PT Pindad Enjiniring Indonesia
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
