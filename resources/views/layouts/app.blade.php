<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') &middot; SPI &mdash; PT Pindad Enjiniring</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PEI.png') }}">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Fonts: Plus Jakarta Sans (UI) + IBM Plex Mono (angka & label) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --spi-navy-deep: #122e56;
            --spi-navy: #1d4f9c;
            --spi-blue: #2d6ac7;
            --spi-glaucous: #4c79bc;
            --spi-glaucous2: #6d8ab4;
            --spi-gold: #ffd631;

            --sdx-bg: #f5f7fa;
            --sdx-line: #e6eaf1;
            --sdx-muted: #66738a;
            --sdx-ink: #212a3a;

            --sdx-green: #27a35f;
            --sdx-orange: #f2913b;
            --sdx-red: #e63232;

            --sdx-radius: 14px;
            --sdx-shadow-sm: 0 1px 2px rgba(18, 46, 86, .05), 0 2px 8px rgba(18, 46, 86, .04);
            --sdx-shadow-md: 0 4px 10px rgba(18, 46, 86, .06), 0 12px 28px rgba(18, 46, 86, .07);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: .925rem;
            color: var(--sdx-ink);
            background:
                radial-gradient(1100px 380px at 15% -8%, rgba(45, 106, 199, .055), transparent 60%),
                var(--sdx-bg);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 { letter-spacing: -.01em; text-wrap: balance; }

        ::selection { background: rgba(255, 214, 49, .45); }

        /* ---------- Aksesibilitas ---------- */
        :focus-visible {
            outline: 2px solid var(--spi-blue);
            outline-offset: 2px;
            border-radius: 4px;
        }
        .sdx-skip {
            position: absolute; left: -9999px; top: 12px; z-index: 3000;
            background: var(--spi-navy-deep); color: #fff;
            padding: .6rem 1rem; border-radius: 8px; font-weight: 600;
        }
        .sdx-skip:focus { left: 12px; }

        /* ---------- Scrollbar ---------- */
        * { scrollbar-width: thin; scrollbar-color: rgba(29, 79, 156, .35) transparent; }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-thumb { background: rgba(29, 79, 156, .30); border-radius: 99px; }
        *::-webkit-scrollbar-thumb:hover { background: rgba(29, 79, 156, .50); }
        *::-webkit-scrollbar-track { background: transparent; }

        /* ============ SIDEBAR (TEKNIKAL / BLUEPRINT ENGINE) ============ */
        .sidebar {
            position: fixed; inset: 0 auto 0 0;
            width: 264px; z-index: 1000;
            display: flex; flex-direction: column;
            background: #10263f;
            color: #e8edf2;
            border-right: 1.5px solid #16304f;
            box-shadow: 4px 0 28px rgba(16, 38, 63, .28);
        }
        /* Grid blueprint halus persis halaman login */
        .sidebar::before {
            content: "";
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .03) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }
        .sidebar > * { position: relative; }

        .sdx-brand {
            display: flex; align-items: center; gap: .8rem;
            padding: 1.25rem 1.15rem 1.15rem;
            border-bottom: 1.5px solid #16304f;
            background: rgba(16, 38, 63, .6);
        }
        .sdx-brand-mark {
            flex: 0 0 auto;
            width: 40px; height: 40px; border-radius: 2px;
            display: grid; place-items: center;
            background: #ffffff;
            border: 1.5px solid #c9d4de;
            padding: 4px;
        }
        .sdx-brand-mark img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sdx-brand-info { display: flex; flex-direction: column; }
        .sdx-brand-tag {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .56rem;
            letter-spacing: .2em;
            color: #ffc72c;
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: .2rem;
        }
        .sdx-brand-name {
            font-family: 'Chakra Petch', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.1;
            letter-spacing: .02em;
            color: #ffffff;
            text-transform: uppercase;
        }
        .sdx-brand-sub {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .62rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #51677e;
            margin-top: .15rem;
        }

        .sdx-nav {
            list-style: none;
            margin: 0;
            padding: .8rem .65rem 1rem;
            overflow-y: auto;
            flex-grow: 1;
        }
        .sdx-section {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .62rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: #51677e;
            padding: 1.1rem .65rem .45rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .sdx-sec-code {
            color: #ffc72c;
            font-weight: 600;
        }
        .sdx-item { margin: 2px 0; }
        .sdx-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .62rem .8rem;
            border-radius: 2px;
            color: #c9d4de;
            font-weight: 500; font-size: .86rem;
            text-decoration: none; position: relative;
            border: 1px solid transparent;
            transition: all .15s ease;
        }
        .sdx-link i.bi:first-child {
            font-size: 1rem;
            width: 1.25em;
            text-align: center;
            color: #51677e;
            transition: color .15s ease;
        }
        .sdx-link-text { flex: 1; }
        .sdx-link:hover {
            color: #ffffff;
            background: #16304f;
            border-color: rgba(201, 212, 222, .2);
        }
        .sdx-link:hover i.bi:first-child { color: #ffc72c; }

        .sdx-link[aria-expanded="true"] {
            color: #ffffff;
            background: #16304f;
            border-color: rgba(201, 212, 222, .25);
        }
        .sdx-link[aria-expanded="true"] i.bi:first-child { color: #ffc72c; }

        .sdx-link .bi-chevron-down {
            margin-left: auto;
            font-size: .68rem;
            color: #51677e;
            transition: transform .2s ease, color .15s ease;
        }
        .sdx-link[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); color: #ffffff; }

        .sdx-link.active {
            color: #10263f;
            background: #ffc72c;
            font-weight: 700;
            border-color: #ffc72c;
        }
        .sdx-link.active i.bi:first-child { color: #10263f; }
        .sdx-link.active .bi-chevron-down { color: #10263f; }

        .sdx-sub { list-style: none; margin: .2rem 0 .4rem; padding: 0 0 0 .9rem; position: relative; }
        .sdx-sub::before {
            content: ""; position: absolute; left: 1.1rem; top: .2rem; bottom: .5rem;
            width: 1.5px; background: #16304f;
        }
        .sdx-sub .sdx-link {
            padding: .42rem .75rem;
            font-size: .81rem;
            font-weight: 500;
            color: #8a9eb2;
            border-radius: 2px;
        }
        .sub-dot {
            width: 4px; height: 4px;
            background: #51677e;
            border-radius: 50%;
            display: inline-block;
            transition: background .15s ease, transform .15s ease;
        }
        .sdx-sub .sdx-link:hover .sub-dot { background: #ffc72c; transform: scale(1.4); }
        .sdx-sub .sdx-link.active {
            background: #16304f;
            color: #ffc72c;
            font-weight: 700;
            border-color: rgba(255, 199, 44, .3);
        }
        .sdx-sub .sdx-link.active .sub-dot { background: #ffc72c; }

        .sdx-foot {
            border-top: 1.5px solid #16304f;
            padding: .8rem .65rem 1rem;
            background: rgba(16, 38, 63, .8);
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }
        .sdx-sys-status {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .62rem;
            letter-spacing: .12em;
            color: #1e8e52;
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: 0 .75rem;
        }
        .sys-dot {
            width: 6px; height: 6px; border-radius: 50%; background: #1e8e52;
            box-shadow: 0 0 8px #1e8e52;
            animation: denyut 2.2s ease-in-out infinite;
        }

        /* Offcanvas mobile memakai kulit yang sama */
        .offcanvas.sdx-offcanvas {
            background: #10263f;
            color: #e8edf2; width: 276px !important;
            border-right: 1.5px solid #16304f;
        }

        /* ============ TOPBAR ============ */
        .sdx-topbar {
            position: sticky; top: 0; z-index: 900;
            background: rgba(255,255,255,.88);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--sdx-line);
        }
        .sdx-topbar-inner {
            display: flex; align-items: center; gap: 1rem;
            padding: .8rem 2rem;
            max-width: 1440px; margin-inline: auto;
        }
        .sdx-burger {
            border: none; background: transparent; color: var(--spi-navy-deep);
            font-size: 1.3rem; line-height: 1; padding: .35rem .5rem; border-radius: 8px;
            display: none;
        }
        .sdx-burger:hover { background: #eef3fa; }
        .sdx-page-title { font-weight: 700; font-size: .95rem; color: var(--spi-navy-deep); }
        .sdx-topbar-right { margin-left: auto; display: flex; align-items: center; gap: .9rem; }
        .sdx-bell {
            width: 38px; height: 38px; border-radius: 10px;
            display: grid; place-items: center;
            color: var(--spi-glaucous2); font-size: 1.1rem;
            cursor: default; opacity: .75;
        }
        .sdx-divider-v { width: 1px; height: 26px; background: var(--sdx-line); }
        .sdx-user-btn {
            display: flex; align-items: center; gap: .6rem;
            border: 1px solid transparent; background: transparent;
            border-radius: 11px; padding: .28rem .6rem .28rem .3rem;
            text-decoration: none; color: inherit;
            transition: background .18s ease, border-color .18s ease;
        }
        .sdx-user-btn:hover { background: #f3f6fb; border-color: var(--sdx-line); }
        .sdx-avatar {
            width: 34px; height: 34px; border-radius: 10px;
            display: grid; place-items: center;
            background: linear-gradient(150deg, var(--spi-navy), var(--spi-blue));
            color: #fff; font-weight: 700; font-size: .8rem; letter-spacing: .02em;
        }
        .sdx-user-name { font-weight: 600; font-size: .84rem; line-height: 1.1; color: var(--sdx-ink); }
        .sdx-user-role {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .63rem; letter-spacing: .08em; text-transform: uppercase;
            color: var(--sdx-muted);
        }

        /* ============ KONTEN ============ */
        .main-wrapper {
            margin-left: 264px;
            min-height: 100dvh;
            display: flex; flex-direction: column;
        }
        .content-area { flex-grow: 1; padding: 1.9rem 2rem 2.6rem; }
        .sdx-container { max-width: 1400px; margin-inline: auto; }

        .sdx-page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .sdx-page-head h1 {
            font-size: 1.45rem; font-weight: 800; color: var(--spi-navy-deep);
            margin: 0 0 .2rem; letter-spacing: -.02em;
        }
        .sdx-eyebrow {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .67rem; letter-spacing: .18em; text-transform: uppercase;
            color: var(--spi-glaucous2); margin-bottom: .3rem;
        }
        .sdx-page-desc { color: var(--sdx-muted); font-size: .88rem; margin: .15rem 0 0; max-width: 62ch; }
        .sdx-page-actions { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; }

        /* Breadcrumb */
        .breadcrumb { font-size: .78rem; --bs-breadcrumb-divider-color: #a5afc2; }
        .breadcrumb-item a { color: var(--spi-glaucous2); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { color: var(--spi-navy); text-decoration: underline; }
        .breadcrumb-item.active { color: var(--sdx-muted); }

        /* ============ KARTU ============ */
        .card {
            border: 1px solid rgba(18, 46, 86, .07);
            border-radius: var(--sdx-radius);
            background: #fff;
            box-shadow: var(--sdx-shadow-sm);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .card-header {
            background: transparent; border-bottom: 1px solid var(--sdx-line);
            padding: .95rem 1.25rem;
            font-weight: 700; font-size: .92rem; color: var(--spi-navy-deep);
        }
        .card-body { padding: 1.25rem; }
        .card-footer { background: transparent; border-top: 1px solid var(--sdx-line); }

        /* Stat card */
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--sdx-shadow-md); }
        .sdx-stat-label {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .67rem; letter-spacing: .13em; text-transform: uppercase;
            color: var(--sdx-muted); margin-bottom: .3rem;
        }
        .sdx-stat-value {
            font-size: 1.72rem; font-weight: 800; line-height: 1.1;
            color: var(--spi-navy-deep);
            font-variant-numeric: tabular-nums; letter-spacing: -.02em;
        }
        .sdx-stat-icon {
            width: 48px; height: 48px; border-radius: 13px; flex: 0 0 auto;
            display: grid; place-items: center; font-size: 1.35rem;
        }

        /* ============ TABEL ============ */
        .table { margin: 0; --bs-table-hover-bg: #f3f7fd; font-size: .88rem; }
        .table thead th {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .67rem; font-weight: 500;
            letter-spacing: .11em; text-transform: uppercase;
            color: #7d89a0;
            background: #fafbfd;
            border-bottom: 1px solid var(--sdx-line);
            padding: .8rem 1rem; white-space: nowrap;
        }
        .table tbody td {
            padding: .85rem 1rem; vertical-align: middle;
            color: #33415c;
            font-variant-numeric: tabular-nums;
        }
        .table > :not(caption) > * > * { box-shadow: none; }
        .table-striped > tbody > tr:nth-of-type(odd) > * { --bs-table-accent-bg: transparent; }
        .table td a { color: var(--spi-blue); font-weight: 600; text-decoration: none; }
        .table td a:hover { color: var(--spi-navy); text-decoration: underline; }

        /* ============ TOMBOL ============ */
        .btn {
            border-radius: 9px; font-weight: 600; font-size: .87rem;
            padding: .52rem 1.05rem;
            transition: background .2s ease, box-shadow .2s ease, transform .12s ease, border-color .2s ease, color .2s ease;
        }
        .btn-sm { padding: .38rem .8rem; font-size: .8rem; border-radius: 8px; }
        .btn:active { transform: scale(.98); }

        .btn-primary {
            background: linear-gradient(180deg, #3773d0, var(--spi-blue));
            border: none; color: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.14), 0 1px 2px rgba(18,46,86,.2);
        }
        .btn-primary:hover, .btn-primary:focus-visible {
            background: var(--spi-navy); color: #fff;
            box-shadow: 0 5px 14px rgba(29, 79, 156, .32);
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            border: 1.5px solid var(--spi-blue); color: var(--spi-blue); background: transparent;
        }
        .btn-outline-primary:hover {
            background: var(--spi-blue); border-color: var(--spi-blue);
            box-shadow: 0 4px 12px rgba(45,106,199,.28); transform: translateY(-1px);
        }
        .btn-secondary { background: #51617a; border: none; }
        .btn-secondary:hover { background: #3c4a5e; transform: translateY(-1px); }
        .btn-outline-secondary {
            border: 1.5px solid #d6dde8; color: #51617a; background: #fff;
        }
        .btn-outline-secondary:hover {
            border-color: var(--spi-glaucous2); color: var(--spi-navy); background: #f6f9fd;
        }
        .btn-danger { background: var(--sdx-red); border: none; }
        .btn-danger:hover { background: #c22b2b; box-shadow: 0 4px 12px rgba(230,50,50,.3); transform: translateY(-1px); }
        .btn-warning { background: var(--sdx-orange); border: none; color: #fff; }
        .btn-warning:hover { background: #d97a24; color: #fff; transform: translateY(-1px); }
        .btn-success { background: #218951; border: none; }
        .btn-success:hover { background: #1b7042; transform: translateY(-1px); }

        /* ============ BADGE SOFT ============ */
        .badge {
            font-weight: 600; font-size: .71rem;
            padding: .4em .75em; border-radius: 7px;
            letter-spacing: .01em;
        }
        /* Soft tones hanya untuk elemen badge; bg-* generik tetap solid */
        .badge.bg-primary { background: #e8f0fc !important; color: #1d4f9c !important; }
        .badge.bg-secondary { background: #edf1f7 !important; color: #51617a !important; }
        .badge.bg-success { background: #e4f5ec !important; color: #1c7a46 !important; }
        .badge.bg-info { background: #e7f1fd !important; color: #2160b4 !important; }
        .badge.bg-warning { background: #fdf1de !important; color: #b3640f !important; }
        .badge.bg-danger { background: #fcebeb !important; color: #bf2b2b !important; }

        /* Warna teks utilitas mengikuti palet */
        .text-primary { color: var(--spi-blue) !important; }
        .text-secondary { color: var(--spi-glaucous2) !important; }
        .text-success { color: #1c7a46 !important; }
        .text-danger { color: #c22b2b !important; }
        .text-warning { color: #d97a24 !important; }
        .text-info { color: #2160b4 !important; }

        /* Badge risiko & status kustom */
        .sdx-badge {
            display: inline-flex; align-items: center; gap: .4em;
            font-weight: 600; font-size: .71rem;
            padding: .4em .75em; border-radius: 7px;
            white-space: nowrap;
        }
        .sdx-badge::before {
            content: "";
            width: 6px; height: 6px; border-radius: 50%;
            background: currentColor; opacity: .85;
        }
        .sdx-badge--neutral { background: #edf1f7; color: #51617a; }
        .sdx-badge--blue { background: #e8f0fc; color: #1d4f9c; }
        .sdx-badge--green { background: #e4f5ec; color: #1c7a46; }
        .sdx-badge--amber { background: #fdf1de; color: #b3640f; }
        .sdx-badge--gold { background: #fbf3cf; color: #8a6d00; }
        .sdx-badge--orange { background: #fdeee0; color: #bc5f10; }
        .sdx-badge--red { background: #fcebeb; color: #bf2b2b; }

        /* ============ FORM ============ */
        .form-label { font-weight: 600; font-size: .8rem; color: #33415c; margin-bottom: .35rem; }
        .form-text { font-size: .76rem; color: var(--sdx-muted); }
        .form-control, .form-select {
            border: 1.5px solid #e2e8f1; border-radius: 9px;
            background: #fbfcfe; color: var(--sdx-ink);
            font-size: .88rem; padding: .52rem .85rem;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .form-control::placeholder { color: #a5afc2; }
        .form-control:focus, .form-select:focus {
            outline: none; border-color: var(--spi-blue); background: #fff;
            box-shadow: 0 0 0 3px rgba(45, 106, 199, .14);
        }
        .form-control.is-invalid:focus, .form-select.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(230, 50, 50, .12);
        }
        .form-check-input { border-color: #cbd4e1; }
        .form-check-input:checked { background-color: var(--spi-blue); border-color: var(--spi-blue); }
        .input-group-text { border: 1.5px solid #e2e8f1; background: #f6f8fc; border-radius: 9px; color: var(--sdx-muted); }

        /* ============ ALERT ============ */
        .alert { border: none; border-radius: 11px; font-size: .87rem; padding: .85rem 1.1rem; }
        .alert-success {
            background: #e4f5ec; color: #17603a;
            box-shadow: inset 3px 0 0 #27a35f;
        }
        .alert-danger {
            background: #fcebeb; color: #9c2323;
            box-shadow: inset 3px 0 0 #e63232;
        }
        .alert-warning {
            background: #fdf1de; color: #8a5200;
            box-shadow: inset 3px 0 0 #f2913b;
        }
        .alert-info {
            background: #e7f1fd; color: #1d5397;
            box-shadow: inset 3px 0 0 var(--spi-blue);
        }

        /* ============ PAGINATION ============ */
        .pagination { gap: .3rem; margin-bottom: 0; }
        .page-link {
            border: none; border-radius: 8px !important;
            color: var(--spi-navy); font-weight: 600; font-size: .83rem;
            min-width: 34px; text-align: center;
            transition: background .18s ease;
        }
        .page-link:hover { background: #e8f0fc; color: var(--spi-navy-deep); }
        .page-item.active .page-link {
            background: var(--spi-navy); color: #fff;
            box-shadow: 0 3px 8px rgba(29, 79, 156, .3);
        }
        .page-item.disabled .page-link { color: #b6bfce; background: transparent; }

        /* ============ DROPDOWN ============ */
        .dropdown-menu {
            border: 1px solid var(--sdx-line); border-radius: 12px;
            box-shadow: 0 10px 30px rgba(18, 46, 86, .12);
            padding: .45rem; font-size: .87rem;
        }
        .dropdown-item { border-radius: 8px; padding: .5rem .75rem; font-weight: 500; color: #33415c; }
        .dropdown-item:hover { background: #f3f6fb; color: var(--spi-navy-deep); }
        .dropdown-item.text-danger:hover { background: #fcebeb; color: #bf2b2b; }

        /* ============ MODAL ============ */
        .modal-content { border: none; border-radius: 16px; box-shadow: 0 24px 60px rgba(18, 46, 86, .22); }
        .modal-header { border-bottom: 1px solid var(--sdx-line); padding: 1.15rem 1.4rem; }
        .modal-title { font-weight: 700; font-size: 1.02rem; color: var(--spi-navy-deep); }
        .modal-footer { border-top: 1px solid var(--sdx-line); padding: 1rem 1.4rem; }
        .modal-backdrop.show { opacity: .45; }

        /* ============ KOMPONEN: DETAIL LIST ============ */
        .sdx-detail {
            display: grid;
            grid-template-columns: minmax(150px, 230px) 1fr;
            row-gap: .95rem; column-gap: 1.5rem;
            margin: 0;
        }
        .sdx-detail dt {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .67rem; font-weight: 500;
            letter-spacing: .11em; text-transform: uppercase;
            color: #7d89a0; padding-top: .2rem;
        }
        .sdx-detail dd { margin: 0; color: var(--sdx-ink); font-weight: 500; font-size: .9rem; overflow-wrap: anywhere; }

        /* ============ KOMPONEN: STEPPER BINTANG ============ */
        .sdx-steps {
            display: flex; align-items: flex-start;
            overflow-x: auto; padding: .25rem 0 .5rem;
        }
        .sdx-step { display: flex; align-items: center; min-width: 0; }
        .sdx-step-node {
            flex: 0 0 auto;
            width: 36px; height: 36px; border-radius: 50%;
            display: grid; place-items: center;
            background: #eef2f7; color: #a5afc2;
            font-size: .95rem;
            transition: background .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .sdx-step-text { margin: 0 .9rem 0 .65rem; white-space: nowrap; }
        .sdx-step-label { font-weight: 600; font-size: .82rem; color: #8a96ab; line-height: 1.2; }
        .sdx-step-sub {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .63rem; letter-spacing: .06em; color: #aab3c4; margin-top: .1rem;
        }
        .sdx-step-line {
            flex: 1 1 24px; min-width: 24px; height: 2px;
            background: var(--sdx-line); margin: 0 .35rem;
            border-radius: 2px; align-self: center;
        }
        .sdx-step.done .sdx-step-node { background: var(--step-tone, var(--spi-blue)); color: #fff; }
        .sdx-step.done .sdx-step-label { color: var(--sdx-ink); }
        .sdx-step.done .sdx-step-line { background: var(--step-tone, var(--spi-blue)); opacity: .35; }
        .sdx-step.current .sdx-step-node {
            background: var(--step-tone, var(--spi-blue)); color: #fff;
            box-shadow: 0 0 0 4px rgba(45, 106, 199, .15);
        }
        .sdx-step.current .sdx-step-label { color: var(--spi-navy-deep); }

        /* ============ KOMPONEN: TIMELINE ============ */
        .sdx-timeline { list-style: none; margin: 0; padding: 0; }
        .sdx-timeline li { position: relative; padding: 0 0 1.3rem 1.7rem; }
        .sdx-timeline li::before {
            content: "";
            position: absolute; left: 5px; top: 16px; bottom: 2px;
            width: 1.5px; background: var(--sdx-line);
        }
        .sdx-timeline li:last-child { padding-bottom: 0; }
        .sdx-timeline li:last-child::before { display: none; }
        .sdx-timeline li::after {
            content: "";
            position: absolute; left: 0; top: 5px;
            width: 11px; height: 11px; border-radius: 50%;
            background: var(--tl-tone, var(--spi-glaucous2));
        }
        .sdx-tl-body { font-size: .87rem; color: #33415c; line-height: 1.45; }
        .sdx-tl-body strong { color: var(--sdx-ink); }
        .sdx-tl-time {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .66rem; letter-spacing: .05em; color: #9aa4b8; margin-top: .15rem;
        }

        /* ============ KOMPONEN: PROGRESS ============ */
        .sdx-progress { height: 8px; border-radius: 99px; background: #e9eef6; overflow: hidden; }
        .sdx-progress-bar { height: 100%; border-radius: 99px; background: var(--p-tone, var(--spi-blue)); transition: width .5s cubic-bezier(.22,.61,.36,1); }
        .sdx-progress-meta { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .4rem; }
        .sdx-progress-label { font-size: .8rem; font-weight: 600; color: #33415c; }
        .sdx-progress-value {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .74rem; color: var(--sdx-muted); font-variant-numeric: tabular-nums;
        }

        /* ============ KOMPONEN: CHIP ============ */
        .sdx-chip {
            display: inline-flex; align-items: center; gap: .35em;
            padding: .28em .7em; border-radius: 7px;
            background: #eef2f7; color: #51617a;
            font-size: .72rem; font-weight: 600; white-space: nowrap;
        }
        .sdx-chip i { font-size: .8em; }
        .sdx-chip--blue { background: #e8f0fc; color: #1d4f9c; }
        .sdx-chip--gold { background: #fbf3cf; color: #8a6d00; }
        .sdx-chip--red { background: #fcebeb; color: #bf2b2b; }

        /* ============ KOMPONEN: AVATAR UKURAN ============ */
        .sdx-avatar--sm { width: 28px; height: 28px; border-radius: 8px; font-size: .68rem; }
        .sdx-avatar--lg { width: 44px; height: 44px; border-radius: 12px; font-size: 1rem; }

        /* ============ KOMPONEN: EVIDENCE ============ */
        .sdx-evidence {
            display: flex; align-items: center; gap: 1rem;
            border: 1px solid var(--sdx-line); border-radius: 12px;
            padding: .85rem 1rem; background: #fff;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .sdx-evidence:hover { border-color: #c9d6ea; box-shadow: var(--sdx-shadow-sm); }
        .sdx-evidence-icon {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 11px;
            display: grid; place-items: center;
            background: #eef3fa; color: var(--spi-glaucous2); font-size: 1.25rem;
            overflow: hidden;
        }
        .sdx-evidence-icon img { width: 100%; height: 100%; object-fit: cover; }
        .sdx-evidence-name { font-weight: 600; font-size: .86rem; color: var(--sdx-ink); overflow-wrap: anywhere; }

        /* Empty state */
        .sdx-empty-icon {
            width: 72px; height: 72px; border-radius: 20px;
            display: grid; place-items: center;
            background: #eef3fa; color: var(--spi-glaucous2);
            font-size: 1.9rem; margin-inline: auto;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .content-area { padding: 1.25rem 1rem 2rem; }
            .sdx-topbar-inner { padding: .7rem 1rem; }
            .sdx-burger { display: inline-flex; }
            .sdx-user-name, .sdx-user-role { display: none; }
            .sdx-detail { grid-template-columns: 1fr; row-gap: .25rem; }
            .sdx-detail dt { padding-top: .6rem; }
            .sdx-detail dd { padding-bottom: .5rem; border-bottom: 1px dashed var(--sdx-line); }
            .sdx-steps { scrollbar-width: none; }
            .sdx-steps::-webkit-scrollbar { display: none; }
            .sdx-step-node { width: 30px; height: 30px; font-size: .82rem; }
            .sdx-step-text { margin-right: .6rem; }
            .sdx-step-sub { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }

        @media print {
            .sidebar, .sdx-topbar, .offcanvas, .btn, form { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            body { background: #fff; }
            .card { box-shadow: none; border-color: #ddd; break-inside: avoid; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <a class="sdx-skip" href="#main-content">Langsung ke konten</a>

    <!-- Sidebar untuk desktop -->
    <x-sidebar />

    <!-- Offcanvas Sidebar untuk mobile -->
    <div class="offcanvas offcanvas-start sdx-offcanvas" tabindex="-1" id="sidebarOffcanvas">
        <div class="offcanvas-header" style="border-bottom: 1px solid rgba(255,255,255,.09);">
            <div class="d-flex align-items-center gap-2">
                <span class="sdx-brand-mark" style="width:36px;height:36px;"><img src="{{ asset('images/PEI.png') }}" alt="Logo PEI"></span>
                <span class="fw-bold">SPI</span>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="sdx-nav">
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('audit-plans.*') ? 'active' : '' }}" href="{{ route('audit-plans.index') }}"><i class="bi bi-clipboard-check"></i> Pengawasan</a></li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('findings.*') ? 'active' : '' }}" href="{{ route('findings.index') }}"><i class="bi bi-exclamation-triangle"></i> Temuan</a></li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('action-plans.*') ? 'active' : '' }}" href="{{ route('action-plans.index') }}"><i class="bi bi-arrow-repeat"></i> Tindak Lanjut</a></li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}"><i class="bi bi-search"></i> Pemeriksaan</a></li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.audit-summary') }}"><i class="bi bi-file-earmark-text"></i> Laporan</a></li>
                @can('manage-master')
                <li class="sdx-section">Administrasi</li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('master.*') ? 'active' : '' }}" href="{{ auth()->user()->role === 'super_admin' ? route('master.users.index') : route('master.audit-types.index') }}"><i class="bi bi-gear"></i> Master Data</a></li>
                <li class="sdx-item"><a class="sdx-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}"><i class="bi bi-clock-history"></i> Audit Log</a></li>
                @endcan
            </ul>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <x-navbar />

        <!-- Content Area -->
        <main id="main-content" class="content-area">
            <div class="sdx-container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
