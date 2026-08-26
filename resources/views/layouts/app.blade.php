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

    <!-- Fonts: Chakra Petch (display) + Plus Jakarta Sans (UI) + IBM Plex Mono (label teknis) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
           LEMBAR KENDALI TEKNIK — tema identik dengan halaman masuk.
           Setiap kartu adalah "lembar" dokumen berkontrol di atas meja
           gambar teknik: kertas grid, sudut tajam, tinta navy, stempel
           kuning Pindad.
           ============================================================ */
        :root {
            /* Palet lembar kerja */
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

            /* Warna status siklus pengawasan */
            --ch-biru: #3f7fd4;
            --ch-hijau: #27a35f;
            --ch-kuning: #ffc72c;
            --ch-oranye: #f2913b;
            --ch-merah: #e63232;

            --font-display: 'Chakra Petch', sans-serif;
            --font-body: 'Plus Jakarta Sans', system-ui, sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;

            --bayang-lembar: 0 14px 34px -22px rgba(16, 38, 63, .38), 0 1px 3px rgba(16, 38, 63, .06);
            --glow-kuning: 0 0 0 3px rgba(255, 199, 44, .4);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-body);
            font-size: .925rem;
            color: var(--tinta);
            background:
                linear-gradient(rgba(16, 38, 63, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16, 38, 63, .045) 1px, transparent 1px),
                var(--kertas);
            background-size: 44px 44px, 44px 44px, auto;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 { letter-spacing: -.005em; }

        /* Judul halaman memakai huruf cetak teknik */
        .content-area h1 {
            font-family: var(--font-display);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .01em;
            line-height: 1.15;
        }
        .content-area h1.h3 { font-size: 1.4rem; }

        ::selection { background: rgba(255, 199, 44, .55); }

        /* ---------- Aksesibilitas ---------- */
        :focus-visible {
            outline: 2px solid var(--tinta);
            outline-offset: 2px;
            border-radius: 2px;
        }
        .sdx-skip {
            position: absolute; left: -9999px; top: 12px; z-index: 3000;
            background: var(--tinta); color: var(--lembar);
            padding: .6rem 1rem; border-radius: 2px; font-weight: 600;
        }
        .sdx-skip:focus { left: 12px; }

        /* ---------- Scrollbar ---------- */
        * { scrollbar-width: thin; scrollbar-color: rgba(81, 103, 126, .4) transparent; }
        *::-webkit-scrollbar { width: 8px; height: 8px; }
        *::-webkit-scrollbar-thumb { background: rgba(81, 103, 126, .35); border-radius: 99px; }
        *::-webkit-scrollbar-thumb:hover { background: rgba(81, 103, 126, .55); }
        *::-webkit-scrollbar-track { background: transparent; }

        /* ============ SIDEBAR (MEJA GAMBAR / BLUEPRINT) ============ */
        .sidebar {
            position: fixed; inset: 0 auto 0 0;
            width: 264px; z-index: 1000;
            display: flex; flex-direction: column;
            background: var(--tinta);
            color: var(--kertas);
            border-right: 1.5px solid var(--tinta-2);
            box-shadow: 4px 0 28px rgba(16, 38, 63, .28);
        }
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
            border-bottom: 1.5px solid var(--tinta-2);
            background: rgba(16, 38, 63, .6);
        }
        .sdx-brand-mark {
            flex: 0 0 auto;
            width: 40px; height: 40px; border-radius: 2px;
            display: grid; place-items: center;
            background: var(--lembar);
            border: 1.5px solid var(--garis);
            padding: 4px;
        }
        .sdx-brand-mark img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .sdx-brand-info { display: flex; flex-direction: column; }
        .sdx-brand-tag {
            font-family: var(--font-mono);
            font-size: .56rem;
            letter-spacing: .2em;
            color: var(--kuning);
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: .2rem;
        }
        .sdx-brand-name {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.1;
            letter-spacing: .02em;
            color: var(--lembar);
            text-transform: uppercase;
        }
        .sdx-brand-sub {
            font-family: var(--font-mono);
            font-size: .62rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--baja);
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
            font-family: var(--font-mono);
            font-size: .62rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--baja);
            padding: 1.1rem .65rem .45rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .sdx-sec-code { color: var(--kuning); font-weight: 600; }
        .sdx-item { margin: 2px 0; }
        .sdx-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .62rem .8rem;
            border-radius: 2px;
            color: var(--garis);
            font-weight: 500; font-size: .86rem;
            text-decoration: none; position: relative;
            border: 1px solid transparent;
            transition: all .15s ease;
        }
        .sdx-link i.bi:first-child {
            font-size: 1rem;
            width: 1.25em;
            text-align: center;
            color: var(--baja);
            transition: color .15s ease;
        }
        .sdx-link-text { flex: 1; }
        .sdx-link:hover {
            color: var(--lembar);
            background: var(--tinta-2);
            border-color: rgba(201, 212, 222, .2);
        }
        .sdx-link:hover i.bi:first-child { color: var(--kuning); }
        .sdx-link[aria-expanded="true"] {
            color: var(--lembar);
            background: var(--tinta-2);
            border-color: rgba(201, 212, 222, .25);
        }
        .sdx-link[aria-expanded="true"] i.bi:first-child { color: var(--kuning); }
        .sdx-link .bi-chevron-down {
            margin-left: auto;
            font-size: .68rem;
            color: var(--baja);
            transition: transform .2s ease, color .15s ease;
        }
        .sdx-link[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); color: var(--lembar); }
        .sdx-link.active {
            color: var(--tinta);
            background: var(--kuning);
            font-weight: 700;
            border-color: var(--kuning);
        }
        .sdx-link.active i.bi:first-child { color: var(--tinta); }
        .sdx-link.active .bi-chevron-down { color: var(--tinta); }

        .sdx-sub { list-style: none; margin: .2rem 0 .4rem; padding: 0 0 0 .9rem; position: relative; }
        .sdx-sub::before {
            content: ""; position: absolute; left: 1.1rem; top: .2rem; bottom: .5rem;
            width: 1.5px; background: var(--tinta-2);
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
            background: var(--baja);
            border-radius: 50%;
            display: inline-block;
            transition: background .15s ease, transform .15s ease;
        }
        .sdx-sub .sdx-link:hover .sub-dot { background: var(--kuning); transform: scale(1.4); }
        .sdx-sub .sdx-link.active {
            background: var(--tinta-2);
            color: var(--kuning);
            font-weight: 700;
            border-color: rgba(255, 199, 44, .3);
        }
        .sdx-sub .sdx-link.active .sub-dot { background: var(--kuning); }

        .sdx-foot {
            border-top: 1.5px solid var(--tinta-2);
            padding: .8rem .65rem 1rem;
            background: rgba(16, 38, 63, .8);
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }
        .sdx-sys-status {
            font-family: var(--font-mono);
            font-size: .62rem;
            letter-spacing: .12em;
            color: var(--hijau);
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: 0 .75rem;
        }
        .sys-dot {
            width: 6px; height: 6px; border-radius: 50%; background: var(--hijau);
            box-shadow: 0 0 8px var(--hijau);
            animation: denyut 2.2s ease-in-out infinite;
        }
        @keyframes denyut {
            0%, 100% { opacity: 1; }
            50% { opacity: .35; }
        }

        /* Offcanvas mobile memakai kulit yang sama */
        .offcanvas.sdx-offcanvas {
            background: var(--tinta);
            color: var(--kertas); width: 276px !important;
            border-right: 1.5px solid var(--tinta-2);
        }

        /* ============ TOPBAR ============ */
        .sdx-topbar {
            position: sticky; top: 0; z-index: 900;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--garis);
        }
        /* Pita rambu: jejak pita kuning-tinta dari halaman masuk */
        .sdx-topbar::after {
            content: "";
            display: block;
            height: 4px;
            background: repeating-linear-gradient(
                -45deg,
                var(--kuning) 0 12px,
                var(--tinta) 12px 24px
            );
        }
        .sdx-topbar-inner {
            display: flex; align-items: center; gap: 1rem;
            padding: .8rem 2rem;
            max-width: 1440px; margin-inline: auto;
        }
        .sdx-burger {
            border: none; background: transparent; color: var(--tinta);
            font-size: 1.3rem; line-height: 1; padding: .35rem .5rem; border-radius: 2px;
            display: none;
        }
        .sdx-burger:hover { background: var(--kertas); }
        .sdx-page-title {
            font-family: var(--font-display);
            font-weight: 600; font-size: .84rem;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--tinta);
        }
        .sdx-topbar-right { margin-left: auto; display: flex; align-items: center; gap: .9rem; }
        .sdx-bell {
            width: 36px; height: 36px; border-radius: 2px;
            display: grid; place-items: center;
            color: var(--baja); font-size: 1.1rem;
            cursor: default; opacity: .75;
            border: 1px solid var(--garis-halus);
        }
        .sdx-divider-v { width: 1px; height: 26px; background: var(--garis); }
        .sdx-user-btn {
            display: flex; align-items: center; gap: .6rem;
            border: 1px solid transparent; background: transparent;
            border-radius: 2px; padding: .28rem .6rem .28rem .3rem;
            text-decoration: none; color: inherit;
            transition: background .18s ease, border-color .18s ease;
        }
        .sdx-user-btn:hover { background: var(--kertas); border-color: var(--garis); }
        .sdx-avatar {
            width: 34px; height: 34px; border-radius: 3px;
            display: grid; place-items: center;
            background: var(--tinta);
            color: var(--kuning);
            font-family: var(--font-mono);
            font-weight: 600; font-size: .85rem;
        }
        .sdx-user-name { font-weight: 600; font-size: .84rem; line-height: 1.1; color: var(--tinta); }
        .sdx-user-role {
            font-family: var(--font-mono);
            font-size: .63rem; letter-spacing: .1em; text-transform: uppercase;
            color: var(--baja);
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
            font-family: var(--font-display);
            font-size: 1.5rem; font-weight: 700; color: var(--tinta);
            margin: 0 0 .2rem; letter-spacing: .01em;
            text-transform: uppercase;
        }
        .sdx-eyebrow {
            font-family: var(--font-mono);
            font-size: .67rem; letter-spacing: .22em; text-transform: uppercase;
            color: var(--baja); margin-bottom: .3rem;
            display: flex; align-items: center; gap: .55rem;
        }
        .sdx-eyebrow::before {
            content: "";
            display: inline-block;
            width: 20px; height: 3px;
            background: var(--kuning);
        }
        .sdx-page-desc { color: var(--baja); font-size: .88rem; margin: .15rem 0 0; max-width: 62ch; line-height: 1.65; }
        .sdx-page-actions { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; }

        /* Breadcrumb */
        .breadcrumb {
            font-family: var(--font-mono);
            font-size: .72rem; letter-spacing: .04em;
            --bs-breadcrumb-divider-color: #93a5b6;
        }
        .breadcrumb-item a { color: var(--baja); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { color: var(--tinta); text-decoration: underline; text-decoration-color: var(--kuning); text-decoration-thickness: 2px; text-underline-offset: 3px; }
        .breadcrumb-item.active { color: var(--tinta); font-weight: 600; }

        /* ============ LEMBAR (KARTU) ============ */
        .card {
            border: 1.5px solid var(--garis);
            border-radius: 2px;
            background: var(--lembar);
            box-shadow: var(--bayang-lembar);
        }
        .card-header {
            background: #f6f9fb; border-bottom: 1px solid var(--garis-halus);
            padding: .75rem 1.1rem;
            font-family: var(--font-mono);
            font-size: .72rem; font-weight: 600;
            letter-spacing: .14em; text-transform: uppercase;
            color: var(--tinta);
        }
        /* Judul apa pun di dalam kepala lembar mengikuti gaya label formulir */
        .card-header :is(h1, h2, h3, h4, h5, h6) {
            font-family: var(--font-mono);
            font-size: .74rem; font-weight: 600;
            letter-spacing: .14em; text-transform: uppercase;
            margin-bottom: 0;
            color: var(--tinta);
        }
        .card-header.bg-warning,
        .card-header.bg-warning :is(h1, h2, h3, h4, h5, h6) { color: var(--tinta) !important; }
        .card-body { padding: 1.25rem; }
        .card-footer { background: transparent; border-top: 1px solid var(--garis-halus); }

        /* Kertas catatan di dalam lembar */
        .card.bg-light { background: #f6f9fb !important; }

        /* Stat card */
        .stat-card { transition: box-shadow .2s ease, transform .2s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 18px 40px -22px rgba(16, 38, 63, .45), 0 1px 3px rgba(16, 38, 63, .06); }
        .sdx-stat-label {
            font-family: var(--font-mono);
            font-size: .66rem; letter-spacing: .16em; text-transform: uppercase;
            color: var(--baja); margin-bottom: .3rem;
        }
        .sdx-stat-value {
            font-family: var(--font-display);
            font-size: 1.85rem; font-weight: 700; line-height: 1.1;
            color: var(--tinta);
            font-variant-numeric: tabular-nums; letter-spacing: .01em;
        }
        .sdx-stat-icon {
            width: 46px; height: 46px; border-radius: 3px; flex: 0 0 auto;
            display: grid; place-items: center; font-size: 1.3rem;
            border: 1px solid var(--garis-halus);
        }

        /* ============ TABEL ============ */
        .table { margin: 0; --bs-table-hover-bg: #f2f6fa; font-size: .88rem; color: var(--tinta); }
        .table-light { --bs-table-bg: #f6f9fb; }
        .table thead th {
            font-family: var(--font-mono);
            font-size: .66rem; font-weight: 600;
            letter-spacing: .13em; text-transform: uppercase;
            color: var(--baja);
            background: transparent;
            border-bottom: 1.5px solid var(--tinta);
            padding: .8rem 1rem; white-space: nowrap;
            vertical-align: bottom;
        }
        .table tbody td {
            padding: .85rem 1rem; vertical-align: middle;
            color: #24384e;
            border-color: var(--garis-halus);
            font-variant-numeric: tabular-nums;
        }
        .table > :not(caption) > * > * { box-shadow: none; }
        .table-striped > tbody > tr:nth-of-type(odd) > * { --bs-table-accent-bg: transparent; }
        .table td a:not(.btn) { color: #2c62b8; font-weight: 600; text-decoration: none; }
        .table td a:not(.btn):hover { color: var(--tinta); text-decoration: underline; text-decoration-color: var(--kuning); text-decoration-thickness: 2px; text-underline-offset: 3px; }

        /* ============ TOMBOL (STEMPEL PERINTAH) ============ */
        .btn {
            border-radius: 2px;
            font-family: var(--font-display);
            font-weight: 600; font-size: .82rem;
            letter-spacing: .09em; text-transform: uppercase;
            padding: .55rem 1.05rem;
            transition: background .18s ease, box-shadow .18s ease, transform .12s ease, border-color .18s ease, color .18s ease;
        }
        .btn-sm { padding: .38rem .8rem; font-size: .73rem; border-radius: 2px; }
        .btn:active { transform: translateY(1px); }

        .btn-primary {
            background: var(--tinta); border: 1.5px solid var(--tinta); color: var(--lembar);
        }
        .btn-primary:hover, .btn-primary:focus-visible {
            background: var(--tinta-2); border-color: var(--tinta-2); color: var(--lembar);
            box-shadow: 0 10px 22px -10px rgba(16, 38, 63, .55);
        }
        .btn-outline-primary {
            border: 1.5px solid var(--tinta); color: var(--tinta); background: transparent;
        }
        .btn-outline-primary:hover {
            background: var(--tinta); border-color: var(--tinta); color: var(--lembar);
            box-shadow: 0 10px 22px -10px rgba(16, 38, 63, .45);
        }
        .btn-secondary { background: var(--baja); border: 1.5px solid var(--baja); color: var(--lembar); }
        .btn-secondary:hover { background: #3e5266; border-color: #3e5266; color: var(--lembar); }
        .btn-outline-secondary {
            border: 1.5px solid var(--garis); color: var(--baja); background: var(--lembar);
        }
        .btn-outline-secondary:hover {
            border-color: var(--tinta); color: var(--tinta); background: var(--kertas);
        }
        .btn-danger { background: var(--merah); border: 1.5px solid var(--merah); color: #fff; }
        .btn-danger:hover { background: #a82a21; border-color: #a82a21; color: #fff; box-shadow: 0 8px 18px -10px rgba(198, 54, 43, .6); }
        .btn-outline-danger {
            border: 1.5px solid var(--merah); color: var(--merah); background: transparent;
        }
        .btn-outline-danger:hover { background: var(--merah); border-color: var(--merah); color: #fff; }
        .btn-warning { background: var(--kuning); border: 1.5px solid var(--kuning); color: var(--tinta); }
        .btn-warning:hover { background: #f0b70f; border-color: #f0b70f; color: var(--tinta); }
        .btn-success { background: var(--hijau); border: 1.5px solid var(--hijau); color: #fff; }
        .btn-success:hover { background: #187343; border-color: #187343; color: #fff; }
        .btn-link { color: var(--tinta); text-decoration: underline; text-decoration-color: var(--kuning); text-decoration-thickness: 2px; text-underline-offset: 3px; }
        .btn-link:hover { color: var(--tinta-2); }

        /* ============ STEMPEL STATUS (BADGE) ============ */
        .badge, .sdx-badge {
            font-family: var(--font-mono);
            font-weight: 600; font-size: .64rem;
            letter-spacing: .11em; text-transform: uppercase;
            padding: .42em .65em; border-radius: 2px;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .sdx-badge {
            display: inline-flex; align-items: center; gap: .45em;
        }
        .sdx-badge::before {
            content: "";
            width: 5px; height: 5px; border-radius: 1px;
            background: currentColor; opacity: .9;
        }
        .badge.bg-primary, .sdx-badge--blue { background: #e9f1fb !important; color: #2c62b8 !important; border-color: #c4d8f0 !important; }
        .badge.bg-secondary, .sdx-badge--neutral { background: #eef1f5 !important; color: var(--baja) !important; border-color: #cfd7e0 !important; }
        .badge.bg-success, .sdx-badge--green { background: #e5f4ec !important; color: #177244 !important; border-color: #bfe3cf !important; }
        .badge.bg-info { background: #e9f1fb !important; color: #2c62b8 !important; border-color: #c4d8f0 !important; }
        .badge.bg-warning { background: #fcf4d8 !important; color: #8a6d00 !important; border-color: #ecd98a !important; }
        .badge.bg-danger { background: #fceeee !important; color: #b02a25 !important; border-color: #efc4c1 !important; }
        .sdx-badge--gold { background: #fcf4d8; color: #8a6d00; border-color: #ecd98a; }
        .sdx-badge--orange { background: #fdefdd; color: #a85710; border-color: #f2cfa4; }
        .sdx-badge--red { background: #fceeee; color: #b02a25; border-color: #efc4c1; }

        /* Border utilitas mengikuti palet */
        .border-warning { border-color: var(--kuning) !important; }
        .border-primary { border-color: var(--tinta) !important; }
        .border-danger { border-color: var(--merah) !important; }

        /* Warna teks utilitas mengikuti palet lembar */
        .text-primary { color: var(--tinta) !important; }
        .text-secondary { color: var(--baja) !important; }
        .text-success { color: #187343 !important; }
        .text-danger { color: #b02a25 !important; }
        .text-warning { color: #a85710 !important; }
        .text-info { color: #2c62b8 !important; }
        .text-muted { color: #64788d !important; }

        /* ============ FORMULIR ============ */
        .form-label, .col-form-label {
            font-family: var(--font-mono);
            font-size: .67rem; font-weight: 500;
            letter-spacing: .16em; text-transform: uppercase;
            color: var(--baja);
            margin-bottom: .45rem;
        }
        .form-text { font-size: .76rem; color: var(--baja); }
        .form-control, .form-select {
            font-size: .9rem;
            color: var(--tinta);
            background: var(--lembar);
            border: 1.5px solid var(--garis);
            border-radius: 2px;
            padding: .55rem .85rem;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .form-control::placeholder { color: #9fb0bf; }
        .form-control:hover, .form-select:hover { border-color: var(--baja); }
        .form-control:focus, .form-select:focus {
            outline: none; border-color: var(--tinta); background: var(--lembar);
            box-shadow: var(--glow-kuning);
        }
        .form-control:disabled, .form-select:disabled { background: var(--kertas); opacity: 1; }
        .form-control[readonly] { background: var(--kertas); border-color: var(--garis-halus); color: var(--baja); }
        .form-control.is-invalid, .form-select.is-invalid { border-color: var(--merah); }
        .form-control.is-invalid:focus, .form-select.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(198, 54, 43, .18);
        }
        .invalid-feedback { color: var(--merah); font-size: .78rem; }
        .valid-feedback { color: var(--hijau); font-size: .78rem; }
        .form-check-input {
            border: 1.5px solid var(--garis); border-radius: 2px;
        }
        .form-check-input:checked {
            background-color: var(--tinta); border-color: var(--tinta);
        }
        .form-check-input:focus { border-color: var(--tinta); box-shadow: var(--glow-kuning); }
        .form-check-label { font-size: .86rem; color: #24384e; }
        .input-group-text {
            border: 1.5px solid var(--garis); background: var(--kertas);
            border-radius: 2px; color: var(--baja);
            font-family: var(--font-mono); font-size: .8rem;
        }

        /* ============ PERINGATAN (ALERT) ============ */
        .alert {
            border: none; border-left: 3px solid transparent;
            border-radius: 2px; font-size: .87rem; padding: .8rem 1.1rem;
            line-height: 1.55;
        }
        .alert-success {
            background: #e5f4ec; color: #135c37;
            border-left-color: var(--hijau);
        }
        .alert-danger {
            background: #fbeeed; color: #7c2320;
            border-left-color: var(--merah);
        }
        .alert-warning {
            background: #fdf3dc; color: #6e5500;
            border-left-color: var(--kuning);
        }
        .alert-info {
            background: #e9f1fb; color: #1c4a86;
            border-left-color: var(--ch-biru);
        }

        /* ============ PAGINATION ============ */
        .pagination { gap: .3rem; margin-bottom: 0; }
        .page-link {
            border: 1px solid transparent; border-radius: 2px !important;
            color: var(--tinta); font-weight: 600; font-size: .83rem;
            font-variant-numeric: tabular-nums;
            min-width: 34px; text-align: center;
            transition: background .18s ease, color .18s ease;
        }
        .page-link:hover { background: var(--kertas); color: var(--tinta); border-color: var(--garis); }
        .page-link:focus-visible { box-shadow: none; }
        .page-item.active .page-link {
            background: var(--tinta); color: var(--lembar);
            border-color: var(--tinta);
        }
        .page-item.disabled .page-link { color: #a9b6c3; background: transparent; }

        /* ============ DROPDOWN ============ */
        .dropdown-menu {
            border: 1.5px solid var(--garis); border-radius: 2px;
            box-shadow: 0 18px 44px -18px rgba(16, 38, 63, .4);
            padding: .45rem; font-size: .87rem;
            background: var(--lembar);
        }
        .dropdown-item { border-radius: 2px; padding: .5rem .75rem; font-weight: 500; color: #24384e; }
        .dropdown-item:hover, .dropdown-item:focus { background: var(--kertas); color: var(--tinta); }
        .dropdown-item.text-danger:hover { background: #fbeeed; color: #b02a25 !important; }
        .dropdown-divider { border-color: var(--garis-halus); }

        /* ============ MODAL ============ */
        .modal-content {
            border: 1.5px solid var(--tinta); border-radius: 2px;
            box-shadow: 0 30px 70px -30px rgba(16, 38, 63, .5);
        }
        .modal-header { border-bottom: 1px solid var(--garis-halus); padding: 1.1rem 1.4rem; }
        .modal-title {
            font-family: var(--font-mono);
            font-weight: 600; font-size: .78rem;
            letter-spacing: .14em; text-transform: uppercase;
            color: var(--tinta);
        }
        .modal-footer { border-top: 1px solid var(--garis-halus); padding: 1rem 1.4rem; }
        .modal-backdrop.show { opacity: .45; }

        /* ============ KOMPONEN: DETAIL LIST ============ */
        .sdx-detail-wrap { container-type: inline-size; }
        .sdx-detail {
            display: grid;
            grid-template-columns: minmax(130px, 220px) minmax(0, 1fr);
            row-gap: .95rem; column-gap: 1.5rem;
            margin: 0;
        }
        .sdx-detail dt {
            font-family: var(--font-mono);
            font-size: .66rem; font-weight: 500;
            letter-spacing: .13em; text-transform: uppercase;
            color: var(--baja); padding-top: .2rem;
        }
        .sdx-detail dd { margin: 0; color: var(--tinta); font-weight: 500; font-size: .9rem; overflow-wrap: anywhere; }

        /* Card/kolom sempit: tumpuk label di atas nilai agar tidak terhimpit */
        @container (max-width: 380px) {
            .sdx-detail { grid-template-columns: 1fr; row-gap: .25rem; }
            .sdx-detail dt { padding-top: .6rem; }
            .sdx-detail dd { padding-bottom: .5rem; border-bottom: 1px dashed var(--garis-halus); }
        }

        /* ============ KOMPONEN: STEPPER ALUR ============ */
        .sdx-steps {
            display: flex; align-items: flex-start;
            overflow-x: auto; padding: .25rem 0 .5rem;
        }
        .sdx-step { display: flex; align-items: center; min-width: 0; }
        .sdx-step-node {
            flex: 0 0 auto;
            width: 34px; height: 34px; border-radius: 4px;
            display: grid; place-items: center;
            background: #eef2f7; color: #9fb0bf;
            border: 1.5px solid var(--garis-halus);
            font-size: .92rem;
            transition: background .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .sdx-step-text { margin: 0 .9rem 0 .65rem; white-space: nowrap; }
        .sdx-step-label { font-weight: 600; font-size: .82rem; color: var(--baja); line-height: 1.2; }
        .sdx-step-sub {
            font-family: var(--font-mono);
            font-size: .62rem; letter-spacing: .08em; color: #93a5b6; margin-top: .1rem;
            text-transform: uppercase;
        }
        .sdx-step-line {
            flex: 1 1 24px; min-width: 24px; height: 0;
            border-top: 2px dashed var(--garis);
            margin: 0 .35rem;
            align-self: center;
        }
        .sdx-step.done .sdx-step-node { background: var(--step-tone, var(--ch-biru)); border-color: var(--step-tone, var(--ch-biru)); color: #fff; }
        .sdx-step.done .sdx-step-label { color: var(--tinta); }
        .sdx-step.done .sdx-step-line { border-top-style: solid; border-top-color: var(--step-tone, var(--ch-biru)); opacity: .4; }
        .sdx-step.current .sdx-step-node {
            background: var(--step-tone, var(--ch-biru)); border-color: var(--step-tone, var(--ch-biru)); color: #fff;
            box-shadow: var(--glow-kuning);
        }
        .sdx-step.current .sdx-step-label { color: var(--tinta); font-weight: 700; }

        /* ============ KOMPONEN: LINIMASA ============ */
        .sdx-timeline { list-style: none; margin: 0; padding: 0; }
        .sdx-timeline li { position: relative; padding: 0 0 1.3rem 1.7rem; }
        /* Penyambung putus-putus seperti alur siklus di halaman masuk */
        .sdx-timeline li::before {
            content: "";
            position: absolute; left: 5px; top: 16px; bottom: 2px;
            border-left: 2px dashed var(--garis);
        }
        .sdx-timeline li:last-child { padding-bottom: 0; }
        .sdx-timeline li:last-child::before { display: none; }
        .sdx-timeline li::after {
            content: "";
            position: absolute; left: 0; top: 5px;
            width: 11px; height: 11px; border-radius: 2px;
            background: var(--tl-tone, var(--baja));
        }
        .sdx-tl-body { font-size: .87rem; color: #24384e; line-height: 1.5; }
        .sdx-tl-body strong { color: var(--tinta); }
        .sdx-tl-time {
            font-family: var(--font-mono);
            font-size: .66rem; letter-spacing: .06em; color: #8496a8; margin-top: .15rem;
        }

        /* ============ KOMPONEN: PROGRESS ============ */
        .sdx-progress { height: 8px; border-radius: 2px; background: var(--garis-halus); overflow: hidden; }
        .sdx-progress-bar { height: 100%; border-radius: 0; background: var(--p-tone, var(--ch-biru)); transition: width .5s cubic-bezier(.22,.61,.36,1); }
        .sdx-progress-meta { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .4rem; }
        .sdx-progress-label { font-size: .82rem; font-weight: 600; color: #24384e; }
        .sdx-progress-value {
            font-family: var(--font-mono);
            font-size: .72rem; color: var(--baja); font-variant-numeric: tabular-nums;
        }

        /* ============ KOMPONEN: CHIP ============ */
        .sdx-chip {
            display: inline-flex; align-items: center; gap: .35em;
            padding: .26em .6em; border-radius: 2px;
            background: #eef1f5; color: var(--baja);
            border: 1px solid #cfd7e0;
            font-family: var(--font-mono);
            font-size: .68rem; font-weight: 500; letter-spacing: .06em;
            text-transform: uppercase; white-space: nowrap;
        }
        .sdx-chip i { font-size: .8em; }
        .sdx-chip--blue { background: #e9f1fb; color: #2c62b8; border-color: #c4d8f0; }
        .sdx-chip--gold { background: #fcf4d8; color: #8a6d00; border-color: #ecd98a; }
        .sdx-chip--red { background: #fceeee; color: #b02a25; border-color: #efc4c1; }

        /* ============ KOMPONEN: AVATAR UKURAN ============ */
        .sdx-avatar--sm { width: 28px; height: 28px; border-radius: 2px; font-size: .68rem; }
        .sdx-avatar--lg { width: 44px; height: 44px; border-radius: 3px; font-size: 1rem; }

        /* ============ KOMPONEN: BUKTI (EVIDENCE) ============ */
        .sdx-evidence {
            display: flex; align-items: center; gap: 1rem;
            border: 1.5px solid var(--garis); border-radius: 2px;
            padding: .85rem 1rem; background: var(--lembar);
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .sdx-evidence:hover { border-color: var(--baja); box-shadow: var(--bayang-lembar); }
        .sdx-evidence-icon {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 3px;
            display: grid; place-items: center;
            background: #e9eef5; color: var(--tinta); font-size: 1.25rem;
            border: 1px solid var(--garis-halus);
            overflow: hidden;
        }
        .sdx-evidence-icon img { width: 100%; height: 100%; object-fit: cover; }
        .sdx-evidence-name { font-weight: 600; font-size: .86rem; color: var(--tinta); overflow-wrap: anywhere; }

        /* Keadaan kosong: undangan bertindak */
        .sdx-empty-icon {
            width: 72px; height: 72px; border-radius: 4px;
            display: grid; place-items: center;
            background: #e9eef5; color: var(--baja);
            border: 1.5px dashed var(--garis);
            font-size: 1.9rem; margin-inline: auto;
        }

        /* ============ NAV-TABS (bila dipakai) ============ */
        .nav-tabs { border-bottom: 1.5px solid var(--tinta); }
        .nav-tabs .nav-link {
            border: 1.5px solid transparent; border-radius: 2px 2px 0 0;
            font-family: var(--font-mono); font-size: .7rem;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--baja); margin-bottom: -1.5px;
        }
        .nav-tabs .nav-link:hover { border-color: var(--garis); color: var(--tinta); }
        .nav-tabs .nav-link.active {
            background: var(--lembar); color: var(--tinta);
            border-color: var(--tinta) var(--tinta) var(--lembar);
            font-weight: 600;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 991.98px) {
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .content-area { padding: 1.25rem 1rem 2rem; }
            .sdx-topbar-inner { padding: .7rem 1rem; }
            .sdx-burger { display: inline-flex; }
            .sdx-user-name, .sdx-user-role { display: none; }
            .sdx-detail { grid-template-columns: 1fr; row-gap: .25rem; }
            .sdx-detail dt { padding-top: .6rem; }
            .sdx-detail dd { padding-bottom: .5rem; border-bottom: 1px dashed var(--garis-halus); }
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
            .card { box-shadow: none; break-inside: avoid; }
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
