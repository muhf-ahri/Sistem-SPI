<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Autentikasi') &middot; SPI &mdash; PT Pindad Enjiniring</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PEI.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --spi-navy-deep: #122e56;
            --spi-navy: #1d4f9c;
            --spi-blue: #2d6ac7;
            --spi-glaucous2: #6d8ab4;
            --spi-gold: #ffd631;
            --spi-line: #e9ecef;
            --spi-muted: #6c757d;
            --star-red: #e63232;
        }

        * { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            background:
                radial-gradient(900px 320px at 50% -10%, rgba(45, 106, 199, .07), transparent 60%),
                #f5f7fa;
            color: #212a3a;
        }
        body::before {
            content: "";
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(18, 46, 86, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(18, 46, 86, .035) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }

        .agx-card {
            position: relative;
            width: 100%; max-width: 440px;
            background: #fff;
            border: 1px solid var(--spi-line);
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(18, 46, 86, .05), 0 16px 40px rgba(18, 46, 86, .07);
            padding: 2.4rem 2.2rem;
        }
        .agx-arc {
            position: absolute;
            width: 340px; height: 340px;
            border-radius: 50%;
            border: 3px solid rgba(255, 214, 49, .0);
            border-top-color: rgba(255, 214, 49, .55);
            top: -190px; right: -170px;
            transform: rotate(-30deg);
            pointer-events: none;
        }

        .agx-brand { display: flex; justify-content: center; margin-bottom: 1.9rem; }
        .agx-brand img { width: 168px; height: auto; }

        .agx-heading { font-weight: 800; font-size: 1.3rem; letter-spacing: -.01em; color: var(--spi-navy-deep); margin-bottom: .3rem; }
        .agx-sub { color: var(--spi-muted); font-size: .88rem; margin-bottom: 1.6rem; }

        .agx-label { font-weight: 600; font-size: .84rem; color: #33415c; margin-bottom: .4rem; display: block; }
        .agx-input {
            width: 100%;
            border: 1.5px solid var(--spi-line);
            border-radius: 8px;
            padding: .68rem .95rem;
            font-size: .9rem;
            color: #212a3a;
            background: #fbfcfe;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .agx-input::placeholder { color: #a5afc2; }
        .agx-input:focus {
            outline: none;
            border-color: var(--spi-blue);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(45, 106, 199, .15);
        }
        .agx-input.is-invalid { border-color: var(--star-red); }

        .agx-error { font-size: .78rem; color: #c22b2b; margin-top: .35rem; }

        .agx-alert {
            border: none; border-radius: 10px;
            font-size: .85rem; padding: .75rem 1rem;
        }
        .agx-alert-success { background: #e4f5ec; color: #17603a; box-shadow: inset 3px 0 0 #27a35f; }
        .agx-alert-danger { background: #fcebeb; color: #9c2323; box-shadow: inset 3px 0 0 #e63232; }
        .agx-alert-info { background: #e7f1fd; color: #1d5397; box-shadow: inset 3px 0 0 var(--spi-blue); }

        .agx-submit {
            width: 100%;
            border: none; border-radius: 8px;
            background: var(--spi-blue); color: #fff;
            font-weight: 700; font-size: .93rem;
            padding: .78rem 1rem;
            transition: background .2s ease, transform .15s ease, box-shadow .2s ease;
        }
        .agx-submit:hover { background: var(--spi-navy); box-shadow: 0 4px 14px rgba(29, 79, 156, .28); }
        .agx-submit:active { transform: translateY(1px); }
        .agx-submit:focus-visible { outline: 3px solid rgba(45, 106, 199, .4); outline-offset: 2px; }

        .agx-link { font-size: .85rem; font-weight: 600; color: var(--spi-blue); text-decoration: none; }
        .agx-link:hover { color: var(--spi-navy); text-decoration: underline; }

        .agx-foot { margin-top: 1.8rem; text-align: center; font-size: .75rem; color: #9aa4b8; }
        .agx-foot-mono {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .68rem; letter-spacing: .14em; text-transform: uppercase;
        }

        @media (max-width: 575.98px) {
            .agx-card { padding: 1.9rem 1.4rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; animation: none !important; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <span class="agx-arc" aria-hidden="true"></span>

    <main class="agx-card">
        <div class="agx-brand">
            <a href="{{ route('login') }}" aria-label="Ke halaman login">
                <img src="{{ asset('images/PEILongLogo.png') }}" alt="PT Pindad Enjiniring Indonesia">
            </a>
        </div>

        {{ $slot }}
    </main>

    <footer class="agx-foot">
        <span class="agx-foot-mono d-block mb-1">Sistem Pengawasan Internal</span>
        &copy; {{ date('Y') }} PT Pindad Enjiniring Indonesia
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
