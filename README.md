# Sistem Audit Internal (SPI)

**Sistem Pengawasan/Audit Internal** untuk **PT Pindad Enjiniring Indonesia** — aplikasi web end-to-end untuk mendukung proses audit divisi: perencanaan, pemeriksaan lapangan, pencatatan temuan, tindak lanjut, verifikasi, hingga pelaporan (LHA).

> **SPI memeriksa secara langsung, sistem mendokumentasikan prosesnya.**

---

## Fitur Utama

| Modul | Deskripsi |
|---|---|
| **Dashboard** | Lembar kontrol: KPI audit & temuan, kalender mini, grafik distribusi status, klasifikasi tingkat risiko, dan hasil pemeriksaan. |
| **Audit** | Rencana audit (draft → terjadwal → pemeriksaan → selesai), penugasan auditor, mulai/selesaikan, dan reaktivasi audit. |
| **Pemeriksaan** | Catatan kunjungan lapangan dengan hasil: `satisfactory`, `needs_improvement`, `non_conformity` + bukti lampiran. |
| **Temuan** | Pencatatan temuan berbasis pemeriksaan, klasifikasi risiko, alur status (open → in_progress → waiting_verification → closed / rejected), dan buka kembali temuan. |
| **Tindak Lanjut** | Rencana tindak lanjut (action plan), unggah bukti, pengajuan verifikasi, dan verifikasi SPI. |
| **Laporan** | Laporan Hasil Audit (LHA), ringkasan audit, analisis temuan & risiko, status tindak lanjut, dan **Analisis Perbandingan Temuan** (pembanding 2 tahun + divisi). |
| **Analisis Perbandingan** | Grafik pertumbuhan temuan per tahun + pembanding temuan antar dua tahun terpilih (total, status, risiko), bisa difilter divisi. |
| **Master Data** | Divisi, jenis audit, kategori temuan, kategori risiko, hari libur, dan manajemen pengguna. |
| **Notifikasi & Audit Log** | Notifikasi sistem per role/divisi, serta jejak aktivitas audit internal. |

## Role & Hak Akses

| Role | Cakupan |
|---|---|
| **Super Admin** | Akses penuh, kelola master data & user. |
| **SPI / Auditor** | Membuat & mengelola audit, pemeriksaan, temuan; verifikasi tindak lanjut; menerbitkan LHA. |
| **Kepala Divisi** | Mengelola tindak lanjut divisinya; laporan & analisis terbatas pada divisi miliknya. |
| **Staff** | Akses terbatas sesuai kebijakan aplikasi. |

## Alur Kerja Temuan (Ringkas)

```
Pemeriksaan ──► Temuan (open) ──► Rencana Tindak Lanjut ──► Pengajuan Verifikasi
        └──► Verifikasi SPI ──► approved  → closed
                            └─► rejected → dikembalikan untuk diperbaiki
```

Detail alur lengkap per role ada di [ALUR.md](ALUR.md), matriks fungsi di [FUNGSI.md](FUNGSI.md), dan arsitektur sistem di [SISTEM.md](SISTEM.md).

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.3+)
- **Frontend:** Blade + Bootstrap 5 (ikon Bootstrap Icons), Tailwind via Vite
- **Grafik:** Chart.js (doughnut, bar, line)
- **PDF:** barryvdh/laravel-dompdf
- **Database:** MySQL

## Instalasi

```bash
# 1. Pasang dependensi
composer install
npm install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate
# atur koneksi database di .env (MySQL)

# 3. Migrasi & seed (master data + user awal)
php artisan migrate --seed

# 4. Seed data demo (opsional — rantai audit lengkap 2021–2026)
php artisan db:seed --class=FullGrowthDataSeeder

# 5. Jalankan aplikasi
php artisan serve
npm run dev   # atau: npm run build
```

### Data Demo

`FullGrowthDataSeeder` membuat **20 rantai audit lengkap** (audit, pemeriksaan, temuan, tindak lanjut, verifikasi, dan LHA) yang tersebar di berbagai divisi & tahun (2021–2026) agar grafik dashboard, pertumbuhan, dan perbandingan temuan langsung terisi. Seeder ini **tidak menyentuh master data** dan aman dijalankan ulang.

## System Design (UI)

Aplikasi memakai bahasa desain bertema *blueprint/engineering*:

- **"lgx"** — halaman autentikasi, **"sdx"** — aplikasi utama.
- Palet monokrom **blue / blue-gray** (navy `#18324D`, biru `#2D6AC7`, slate `#405A73`, light `#9AA8B5`) dengan aksen kuning Pindad `#FFC72C`.
- Kartu, badge, tombol, dan grafik membulat; badge risiko/status soft dengan kontras halus.

Skema lengkap ada di [DESIGN.md](DESIGN.md).

## Struktur Referensi

- [MASTERP.md](MASTERP.md) — arahan master project & role.
- [ALUR.md](ALUR.md) — alur penggunaan per role.
- [FUNGSI.md](FUNGSI.md) — fungsi tiap modul.
- [SISTEM.md](SISTEM.md) — gambaran arsitektur sistem.
- [DESIGN.md](DESIGN.md) — design system & palet.

## Lisensi

Dikembangkan untuk kebutuhan internal PT Pindad Enjiniring Indonesia.