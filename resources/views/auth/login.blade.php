<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SPI PT Pindad Enjiniring Indonesia</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PEI.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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
            --ch-hijau: #27a35f;
            --ch-kuning: #ffc72c;
            --ch-oranye: #f2913b;
            --ch-merah: #e63232;

            --font-display: 'Chakra Petch', sans-serif;
            --font-body: 'Plus Jakarta Sans', system-ui, sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: var(--font-body);
            color: var(--tinta);
            background:
                linear-gradient(rgba(16, 38, 63, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16, 38, 63, .045) 1px, transparent 1px),
                var(--kertas);
            background-size: 44px 44px, 44px 44px, auto;
            min-height: 100vh;
        }

        /* ============ MEJA & LEMBAR GAMBAR ============ */
        .meja {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: clamp(14px, 3vw, 36px);
        }

        .lembar {
            position: relative;
            width: min(1240px, 100%);
            background: var(--lembar);
            border: 1px solid #aebccb;
            box-shadow: 0 24px 60px -28px rgba(16, 38, 63, .35), 0 2px 6px rgba(16, 38, 63, .08);
            display: flex;
            flex-direction: column;
        }

        /* bingkai dalam ala kertas gambar teknik */
        .lembar::after {
            content: "";
            position: absolute;
            inset: 5px;
            border: 1.5px solid var(--tinta);
            pointer-events: none;
        }

        .pita {
            height: 6px;
            background: repeating-linear-gradient(
                -45deg,
                var(--kuning) 0 12px,
                var(--tinta) 12px 24px
            );
            margin: 5px 5px 0;
        }

        /* tanda registrasi sudut */
        .reg {
            position: absolute;
            width: 15px;
            height: 15px;
            z-index: 3;
            pointer-events: none;
        }
        .reg::before, .reg::after {
            content: "";
            position: absolute;
            background: var(--baja);
        }
        .reg::before { left: 50%; top: 0; bottom: 0; width: 1.5px; transform: translateX(-50%); }
        .reg::after { top: 50%; left: 0; right: 0; height: 1.5px; transform: translateY(-50%); }
        .reg--tl { top: -9px; left: -9px; }
        .reg--tr { top: -9px; right: -9px; }
        .reg--bl { bottom: -9px; left: -9px; }
        .reg--br { bottom: -9px; right: -9px; }

        /* ============ ISI LEMBAR ============ */
        .lembar-isi {
            display: flex;
            flex: 1;
            margin: 0 5px;
        }

        .panel-ringkas {
            flex: 1.15;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
            padding: clamp(1.8rem, 3.4vw, 3.2rem);
        }

        .panel-form {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.8rem, 3.4vw, 3rem) clamp(1.4rem, 3vw, 3rem);
            border-left: 1px solid var(--garis-halus);
        }

        /* ---- sisi kiri ---- */
        .logo-atas img {
            width: clamp(140px, 13vw, 176px);
            height: auto;
            display: block;
        }

        .alis {
            font-family: var(--font-mono);
            font-size: .68rem;
            font-weight: 500;
            letter-spacing: .24em;
            text-transform: uppercase;
            color: var(--baja);
            margin-bottom: .9rem;
        }
        .alis::before {
            content: "";
            display: inline-block;
            width: 22px;
            height: 2px;
            background: var(--kuning);
            vertical-align: middle;
            margin-right: .6rem;
        }

        .judul {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: clamp(1.65rem, 2.9vw, 2.55rem);
            line-height: 1.08;
            text-transform: uppercase;
            letter-spacing: .01em;
            max-width: 15em;
        }

        .pengantar {
            margin-top: 1rem;
            max-width: 46ch;
            color: var(--baja);
            font-size: .93rem;
            line-height: 1.7;
        }

        .siklus {
            list-style: none;
            margin-top: 2rem;
            max-width: 480px;
        }
        .siklus li {
            position: relative;
            display: flex;
            gap: .9rem;
            align-items: flex-start;
            padding-bottom: 1.05rem;
        }
        .siklus li:last-child { padding-bottom: 0; }
        .siklus li::before {
            content: "";
            position: absolute;
            left: 14px;
            top: 32px;
            bottom: 4px;
            border-left: 2px dashed var(--garis);
        }
        .siklus li:last-child::before { display: none; }

        .chip {
            flex: 0 0 auto;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            display: grid;
            place-items: center;
            background: var(--c);
            color: #fff;
        }
        .chip i { font-size: .85rem; }
        .chip--kuning { color: var(--tinta); }
        .siklus li:nth-child(1) .chip { --c: var(--ch-biru); }
        .siklus li:nth-child(2) .chip { --c: var(--ch-hijau); }
        .siklus li:nth-child(3) .chip { --c: var(--ch-kuning); }
        .siklus li:nth-child(4) .chip { --c: var(--ch-oranye); }
        .siklus li:nth-child(5) .chip { --c: var(--ch-merah); }

        .langkah-no {
            font-family: var(--font-mono);
            font-size: .64rem;
            letter-spacing: .18em;
            color: var(--baja);
        }
        .langkah-judul {
            font-weight: 700;
            font-size: .92rem;
            margin: .05rem 0 .1rem;
        }
        .langkah-desk {
            font-size: .8rem;
            color: var(--baja);
            line-height: 1.5;
        }

        .kaki-kiri {
            font-family: var(--font-mono);
            font-size: .68rem;
            letter-spacing: .06em;
            color: var(--baja);
        }

        /* ---- sisi kanan: formulir ---- */
        .form-wrap {
            width: 100%;
            max-width: 396px;
        }

        .logo-mobile { display: none; }

        .form-kepala h2 {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .form-kepala p {
            margin-top: .3rem;
            color: var(--baja);
            font-size: .88rem;
        }

        .stempel {
            position: absolute;
            top: clamp(1.4rem, 3.4vw, 3rem);
            right: clamp(1.4rem, 3vw, 3rem);
            transform: rotate(-7deg);
            font-family: var(--font-mono);
            font-weight: 600;
            font-size: .74rem;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--merah);
            border: 2.5px solid currentColor;
            outline: 1px solid currentColor;
            outline-offset: 3px;
            padding: .32rem .65rem;
            opacity: .9;
            pointer-events: none;
            z-index: 4;
        }

        .peringatan {
            display: flex;
            gap: .65rem;
            align-items: flex-start;
            margin: 1.25rem 0 .25rem;
            padding: .75rem .9rem;
            background: #fbeeed;
            border-left: 3px solid var(--merah);
            font-size: .84rem;
            color: #7c2320;
            line-height: 1.5;
        }
        .peringatan i { color: var(--merah); font-size: 1rem; margin-top: .1rem; }

        .baris-form { margin-bottom: 1.05rem; }

        .label {
            display: block;
            font-family: var(--font-mono);
            font-size: .68rem;
            font-weight: 500;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--baja);
            margin-bottom: .45rem;
        }
        .label .wajib { color: var(--merah); font-family: var(--font-body); }

        .kolom { position: relative; }
        .kolom > i {
            position: absolute;
            left: .95rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--baja);
            font-size: .95rem;
            pointer-events: none;
        }

        .input {
            width: 100%;
            font: inherit;
            font-size: .92rem;
            color: var(--tinta);
            background: var(--lembar);
            border: 1.5px solid var(--garis);
            border-radius: 2px;
            padding: .72rem .95rem .72rem 2.7rem;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .input::placeholder { color: #9fb0bf; }
        .input:hover { border-color: var(--baja); }
        .input:focus {
            outline: none;
            border-color: var(--tinta);
            box-shadow: 0 0 0 3px rgba(255, 199, 44, .4);
        }
        .input.is-invalid { border-color: var(--merah); }
        .input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(198, 54, 43, .18); }

        .input--password { padding-right: 3rem; }

        .toggle-pass {
            position: absolute;
            right: .35rem;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--baja);
            padding: .42rem .6rem;
            border-radius: 3px;
            cursor: pointer;
            line-height: 1;
        }
        .toggle-pass:hover { color: var(--tinta); background: var(--kertas); }
        .toggle-pass:focus-visible { outline: 2px solid var(--tinta); outline-offset: 1px; }

        .baris-opsi {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: .2rem 0 1.4rem;
        }

        .ingat {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .84rem;
            color: var(--baja);
            cursor: pointer;
            user-select: none;
        }
        .ingat input {
            width: 15px;
            height: 15px;
            accent-color: var(--tinta);
            cursor: pointer;
        }

        .lupa {
            font-size: .84rem;
            font-weight: 600;
            color: var(--tinta);
            text-decoration: underline;
            text-decoration-color: var(--kuning);
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
        }
        .lupa:hover { color: var(--tinta-2); text-decoration-thickness: 3px; }
        .lupa:focus-visible { outline: 2px solid var(--tinta); outline-offset: 3px; border-radius: 2px; }

        .tombol-masuk {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: .95rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--lembar);
            background: var(--tinta);
            border: 0;
            border-radius: 2px;
            padding: .85rem 1rem;
            cursor: pointer;
            transition: background .18s ease, box-shadow .18s ease, transform .12s ease;
        }
        .tombol-masuk i { transition: transform .18s ease; }
        .tombol-masuk:hover {
            background: var(--tinta-2);
            box-shadow: 0 10px 22px -10px rgba(16, 38, 63, .55);
        }
        .tombol-masuk:hover i { transform: translateX(3px); }
        .tombol-masuk:active { transform: translateY(1px); }
        .tombol-masuk:focus-visible { outline: 3px solid rgba(255, 199, 44, .8); outline-offset: 2px; }

        .catatan-audit {
            display: flex;
            align-items: center;
            gap: .45rem;
            justify-content: center;
            margin-top: 1.15rem;
            font-family: var(--font-mono);
            font-size: .66rem;
            letter-spacing: .08em;
            color: var(--baja);
        }
        .catatan-audit i { font-size: .78rem; }

        .info-akun {
            display: flex;
            align-items: center;
            gap: .45rem;
            justify-content: center;
            margin-top: .8rem;
            font-size: .84rem;
            color: var(--baja);
        }
        .info-akun i { color: var(--kuning); font-size: .95rem; }
        .link-wa {
            font-weight: 600;
            color: var(--tinta);
            text-decoration: underline;
            text-decoration-color: var(--hijau);
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
        }
        .link-wa:hover { color: var(--hijau); text-decoration-thickness: 3px; }

        /* ============ BLOK JUDUL ============ */
        .blok-judul {
            display: flex;
            border-top: 1.5px solid var(--tinta);
            margin: 0 5px;
            background: var(--lembar);
        }
        .sel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: .18rem;
            padding: .55rem 1rem;
            border-left: 1px solid var(--garis);
            min-width: 0;
        }
        .sel:first-child { border-left: 0; }
        .sel--logo { justify-content: center; }
        .sel--logo img { height: 24px; width: auto; display: block; }
        .sel--sistem { flex: 1; }
        .sel-label {
            font-family: var(--font-mono);
            font-size: .58rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--baja);
            white-space: nowrap;
        }
        .sel-nilai {
            font-size: .76rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .status-titik {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--hijau);
            margin-right: .4rem;
            animation: denyut 2.2s ease-in-out infinite;
        }
        .sel--status .sel-nilai { color: var(--hijau); display: flex; align-items: center; }

        @keyframes denyut {
            0%, 100% { opacity: 1; }
            50% { opacity: .35; }
        }

        /* ============ GERAK MASUK ============ */
        @keyframes turun {
            from { opacity: 0; transform: translateY(10px) scale(.995); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .muncul { animation: turun .5s cubic-bezier(.22, .61, .36, 1) both; }
        .m1 { animation-delay: .04s; }
        .m2 { animation-delay: .12s; }
        .m3 { animation-delay: .2s; }

        @media (prefers-reduced-motion: reduce) {
            .muncul, .status-titik { animation: none; }
            * { transition: none !important; }
        }

        :focus-visible { outline: 2px solid var(--tinta); outline-offset: 2px; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 991.98px) {
            .panel-ringkas { display: none; }
            .panel-form { border-left: 0; }
            .form-wrap { max-width: 420px; }
            .logo-mobile {
                display: flex;
                flex-direction: column;
                gap: .55rem;
                margin-bottom: 1.6rem;
            }
            .logo-mobile img { width: 168px; height: auto; }
            .logo-mobile p {
                font-family: var(--font-display);
                font-weight: 600;
                font-size: .82rem;
                letter-spacing: .14em;
                text-transform: uppercase;
                color: var(--baja);
            }
        }

        @media (max-width: 719.98px) {
            .sel--dokumen, .sel--tanggal, .sel--revisi { display: none; }
        }

        @media (max-width: 479.98px) {
            .meja { padding: 10px; }
            .blok-judul { flex-wrap: wrap; }
            .sel--sistem { flex: 1 1 100%; border-left: 0; border-bottom: 1px solid var(--garis); }
            .sel--status { border-left: 0; }
            .stempel { display: none; }
        }
    </style>
</head>
<body>
    <div class="meja">
        <div class="lembar muncul">
            <span class="reg reg--tl" aria-hidden="true"></span>
            <span class="reg reg--tr" aria-hidden="true"></span>
            <span class="reg reg--bl" aria-hidden="true"></span>
            <span class="reg reg--br" aria-hidden="true"></span>
            <div class="pita" aria-hidden="true"></div>

            <div class="lembar-isi">

                <!-- ===== KIRI: LEMBAR PENJELASAN ===== -->
                <aside class="panel-ringkas">
                    <div class="logo-atas muncul m1">
                        <img src="{{ asset('images/PEILongLogo.png') }}" alt="PT Pindad Enjiniring Indonesia">
                    </div>

                    <div>
                        <p class="alis">Sistem Audit Internal</p>
                        <h1 class="judul">Dicatat.<br>Ditelusuri.<br>Dituntaskan.</h1>
                        <p class="pengantar">
                            SPI memusatkan perencanaan Audit, pencatatan temuan,
                            tindak lanjut, hingga pelaporan dalam satu lembar kendali &mdash;
                            setiap risiko terpantau dan tersalurkan ke tindakan yang jelas.
                        </p>

                        <ol class="siklus" aria-label="Alur siklus Audit">
                            <li>
                                <span class="chip"><i class="bi bi-clipboard-check"></i></span>
                                <div>
                                    <span class="langkah-no">01 / RENCANA</span>
                                    <div class="langkah-judul">Audit direncanakan</div>
                                    <div class="langkah-desk">Jadwal dan auditor ditetapkan per divisi.</div>
                                </div>
                            </li>
                            <li>
                                <span class="chip"><i class="bi bi-search"></i></span>
                                <div>
                                    <span class="langkah-no">02 / PELAKSANAAN</span>
                                    <div class="langkah-judul">Pemeriksaan dilaksanakan</div>
                                    <div class="langkah-desk">Proses berjalan diperiksa sesuai rencana audit.</div>
                                </div>
                            </li>
                            <li>
                                <span class="chip chip--kuning"><i class="bi bi-exclamation-triangle"></i></span>
                                <div>
                                    <span class="langkah-no">03 / TEMUAN</span>
                                    <div class="langkah-judul">Temuan tercatat</div>
                                    <div class="langkah-desk">Setiap temuan diklasifikasikan berdasarkan risikonya.</div>
                                </div>
                            </li>
                            <li>
                                <span class="chip"><i class="bi bi-arrow-repeat"></i></span>
                                <div>
                                    <span class="langkah-no">04 / TINDAK LANJUT</span>
                                    <div class="langkah-judul">Perbaikan dijalankan</div>
                                    <div class="langkah-desk">Rencana perbaikan disepakati dengan tenggat jelas.</div>
                                </div>
                            </li>
                            <li>
                                <span class="chip"><i class="bi bi-patch-check"></i></span>
                                <div>
                                    <span class="langkah-no">05 / PENUTUPAN</span>
                                    <div class="langkah-judul">Verifikasi &amp; pelaporan</div>
                                    <div class="langkah-desk">Bukti diverifikasi, Audit ditutup dan dilaporkan.</div>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <footer class="kaki-kiri">&copy; {{ date('Y') }} PT PINDAD ENJINIRING INDONESIA</footer>
                </aside>

                <!-- ===== KANAN: FORMULIR MASUK ===== -->
                <main class="panel-form">
                    @if ($errors->any())
                        <span class="stempel" aria-hidden="true">Akses Ditolak</span>
                    @endif

                    <div class="form-wrap">
                        <div class="logo-mobile muncul m1">
                            <img src="{{ asset('images/PEILongLogo.png') }}" alt="PT Pindad Enjiniring Indonesia">
                            <p>Dicatat &middot; Ditelusuri &middot; Dituntaskan</p>
                        </div>

                        <header class="form-kepala muncul m1">
                            <h2>Masuk</h2>
                            <p>Gunakan email kantor yang terdaftar pada sistem.</p>
                        </header>

                        @if ($errors->any())
                            <div class="peringatan" role="alert">
                                <i class="bi bi-shield-exclamation"></i>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" novalidate>
                            @csrf

                            <div class="baris-form muncul m2">
                                <label for="email" class="label">Email <span class="wajib">*</span></label>
                                <div class="kolom">
                                    <i class="bi bi-envelope"></i>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           class="input @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}"
                                           placeholder="nama@pindad.co.id"
                                           autocomplete="email"
                                           required
                                           autofocus>
                                </div>
                            </div>

                            <div class="baris-form muncul m2">
                                <label for="password" class="label">Password <span class="wajib">*</span></label>
                                <div class="kolom">
                                    <i class="bi bi-lock"></i>
                                    <input type="password"
                                           id="password"
                                           name="password"
                                           class="input input--password @error('password') is-invalid @enderror"
                                           placeholder="Masukkan password"
                                           autocomplete="current-password"
                                           required>
                                    <button type="button"
                                            class="toggle-pass"
                                            id="togglePassword"
                                            aria-label="Tampilkan password"
                                            data-visible="false">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="baris-opsi muncul m3">
                                <label class="ingat">
                                    <input type="checkbox" name="ingat_saya" id="ingat_saya">
                                    Ingat saya
                                </label>
                                <a href="{{ route('password.request') }}" class="lupa">Lupa password?</a>
                            </div>

                            <button type="submit" class="tombol-masuk muncul m3">
                                Masuk ke Sistem <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>

                        <p class="info-akun muncul m3">
                            <i class="bi bi-info-circle"></i>
                            <span>Jika tidak memiliki akun silahkan hubungi
                                <a href="https://wa.me/6282130641298?text=Halo%20Tim%20SPI%2C%20saya%20ingin%20meminta%20akun%20untuk%20mengakses%20Sistem%20Audit%20Internal.%20Terima%20kasih."
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="link-wa">Tim SPI</a>
                            </span>
                        </p>

                        <p class="catatan-audit">
                            <i class="bi bi-shield-lock"></i> Seluruh aktivitas masuk tercatat untuk keperluan audit
                        </p>
                    </div>
                </main>
            </div>

            <!-- ===== BLOK JUDUL (KOTAK IDENTITAS DOKUMEN) ===== -->
            <footer class="blok-judul" aria-hidden="true">
                <div class="sel sel--logo">
                    <img src="{{ asset('images/PEI.png') }}" alt="">
                </div>
                <div class="sel sel--sistem">
                    <span class="sel-label">Sistem</span>
                    <span class="sel-nilai">Sistem Audit Internal</span>
                </div>
                <div class="sel sel--dokumen">
                    <span class="sel-label">No. Dokumen</span>
                    <span class="sel-nilai">SPI/FRM-01</span>
                </div>
                <div class="sel sel--revisi">
                    <span class="sel-label">Revisi</span>
                    <span class="sel-nilai">04/{{ date('y') }}</span>
                </div>
                <div class="sel sel--tanggal">
                    <span class="sel-label">Tanggal</span>
                    <span class="sel-nilai">{{ now()->format('d.m.Y') }}</span>
                </div>
                <div class="sel sel--status">
                    <span class="sel-label">Status</span>
                    <span class="sel-nilai"><span class="status-titik"></span>Aktif</span>
                </div>
            </footer>
        </div>
    </div>

    <script>
        (function () {
            var toggle = document.getElementById('togglePassword');
            var input = document.getElementById('password');
            var icon = document.getElementById('toggleIcon');
            if (!toggle || !input || !icon) return;

            toggle.addEventListener('click', function () {
                var visible = toggle.getAttribute('data-visible') === 'true';
                input.type = visible ? 'password' : 'text';
                icon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
                toggle.setAttribute('data-visible', String(!visible));
                toggle.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
            });
        })();
    </script>
</body>
</html>
