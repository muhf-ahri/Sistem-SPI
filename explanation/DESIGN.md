# DESIGN SYSTEM
# Sistem Audit Internal (SPI)
# PT Pindad Enjiniring Indonesia

## 0. Implementasi Aktual (lgx / sdx — 2026)

Sistem diimplementasikan dengan bahasa desain **"lgx"** (halaman auth) dan **"sdx"** (aplikasi), bertema *blueprint/engineering* yang diturunkan dari identitas PT Pindad Enjiniring Indonesia.

### Token Utama (CSS Variables di `:root`)

```css
--tinta: #10263f;          /* navy dalam — teks utama, sidebar, aksen */
--tinta-2: #16304f;        /* navy hover */
--baja: #51677e;           /* teks sekunder */
--kertas: #e8edf2; --lembar: #ffffff;
--garis: #c9d4de; --garis-halus: #dde5ec;
--kuning: #ffc72c;         /* aksen emas Pindad: nav aktif, highlight */
--merah: #c6362b; --hijau: #1e8e52;  /* aksen status brand */
```

**Color System SPI** (monokrom blue / blue-gray — grafik, badge risiko, badge status):

```css
--spi-navy: #18324d;    /* paling penting / strongest */
--spi-blue: #2d6ac7;    /* interaksi & aksi primer */
--spi-slate: #405a73;   /* info sekunder */
--spi-muted: #52677d;
--spi-soft: #7f91a3;    /* penekanan rendah */
--spi-light: #9aa8b5;   /* paling soft */
--spi-border: #d9e2ea;
--spi-background: #f7f9fb;
```

> Aksen brand lama tetap dijaga untuk elemen brand/accent (nav aktif, tombol primer, branding):
> `--smart-blue: #2D6AC7; --glaucous: #4C79BC; --glaucous-2: #6D8AB4; --racing-red: #E63232; --carrot-orange: #F2913B; --gold: #FFD631;`

### Tipografi

- **Display / Header:** Chakra Petch (engineering feel).
- **UI / Body:** Plus Jakarta Sans.
- **Label / Eyebrow / Angka:** IBM Plex Mono (uppercase, letter-spacing). Numerik memakai `tabular-nums`.

### Radius (Implementasi Aktual)

| Elemen | Radius |
|---|---|
| Card | `0.6rem` (dengan `overflow:hidden` — isi ikut terpotong rounded) |
| Tombol, field input/select, input-group | `0.45rem` |
| Dropdown menu | `0.5rem` |
| Modal | `0.6rem` |
| Panel kalender / area grafik | `0.5rem` |
| Pagination `.page-link` | `0.45rem` (item terpisah, gaya pill) |
| Logo sidebar (`sdx-brand-mark`) | `0.45rem` |

### Shadow

`--bayang-lembar: 0 14px 34px -22px rgba(16,38,63,.38), 0 1px 3px rgba(16,38,63,.06)` — tipis, bernuansa navy. Hindari shadow tebal/efek 3D.

### Komponen Blade Tersedia (`resources/views/components`)

| Komponen | Fungsi |
|---|---|
| `x-page-header` | Header halaman: breadcrumb, judul, deskripsi, slot actions |
| `x-stepper` | Stepper siklus temuan |
| `x-status-badge` / `x-risk-badge` | Badge soft monokrom dengan dot |
| `x-stat-card` | Kartu KPI (ikon tinted, angka besar) |
| `x-progress` | Bar progres |
| `x-pagination` | Pagination windowed (awal/akhir + ellipsis, rounded) |
| `x-sort-th` | Kolom tabel dengan pengurutan |
| `x-detail-list` + `x-detail-item` | Pasangan label/value |
| `x-confirm-modal` | Modal konfirmasi aksi berbahaya |
| `x-evidence-card` | Berkas bukti (preview, lihat/unduh) |
| `x-avatar` / `x-chip` / `x-application-logo` | Elemen kecil pendukung |
| `x-sidebar` / `x-navbar` | Navigasi aplikasi |

### Layout

- Desktop: sidebar navy `#10263f` (tekstur blueprint grid + aksen kuning) + topbar kaca sticky + konten.
- Mobile ≤992px: sidebar menjadi offcanvas.
- Halaman auth: kartu putih terpusat dengan motif arc emas & grid blueprint.
- Tombol aksi baris & header memakai **ikon saja** dengan `aria-label` + `title` (tooltip).

---

## 1. Design Principles

Karakter: Professional / Corporate / Reliable / Clean / Structured / Modern / Efficient.

> Prioritas: Usability → Clarity → Consistency → Accessibility → Professional appearance → Responsive.

Hindari tampilan landing page, social media, atau gaming dashboard.

## 2. Color Guidelines

- **Navy `#18324D`** → informasi paling penting, judul grafik paling kuat.
- **Blue `#2D6AC7`** → interaksi / aksi primer.
- **Slate/Blue-gray `#405A73` – `#9AA8B5`** → informasi sekunder & penekanan rendah.
- **Kuning Pindad `#FFC72C`** → aksen (nav aktif, highlight) — digunakan hemat.
- Background `#F7F9FB` / `#F6F9FB`, border `#D9E2EA`.
- Dominasi putih/netral ~70%, biru ~20%, aksen ~10%. Jangan memakai merah/hijau/oranye secara berbarengan untuk seluruh kategori status/risiko.

## 3. Badge Status & Risiko (Monokrom Soft)

Semua badge memakai palet blue-gray yang seragam — background sangat terang, border tipis, teks kontras, tanpa gradient & tanpa warna solid mencolok.

| Peran | Keterangan |
|---|---|
| Badge Risiko | low → medium → high → critical: intensitas naik (bg `#F5F7F9` → `#E8EEF4`, teks `#7F91A3` → `#18324D`, border `#DCE3E9` → `#B7C5D2`). |
| Badge Status | Terbuka, Sedang Berjalan, Menunggu Verifikasi, Ditutup, Ditolak — satu visual language blue-gray. |

> Label teks selalu tampil — warna bukan satu-satunya indikator.

## 4. Chart & Grafik

Dashboard & laporan memakai **Chart.js** (doughnut, bar, line) dengan properti:

- Irisan/tomat bar `borderRadius: 6` (rounded).
- Separator donut `#F7F9FB`, border 2px.
- Dataset pembanding: tahun aktif berwarna, tahun pembanding abu-abu `rgba(154,168,181,.55)`.
- Canvas dibungkus div `position:relative; height:300px` agar tidak memanjang ke bawah (fix bug grow).
- Font label IBM Plex Mono.

Palet grafik dashboard menyesuaikan **warna kartu KPI / progress**:
- Distribusi status: Terbuka `#3B82F6`, Sedang Berjalan `#F59E0B`, Menunggu Verifikasi `#EF4444`, Ditutup `#10B981`, Ditolak `#6B7280`.
- Klasifikasi risiko: Critical `#C6362B`, High `#EF4444`, Medium `#F59E0B`, Low `#059669`.
- Hasil pemeriksaan: Satisfactory `#059669`, Needs Improvement `#F59E0B`, Non Conformity `#EF4444`.

## 5. Status Warna Semantik

Untuk aksen (bukan kategori risiko/status utama):

| Konteks | Warna |
|---|---|
| Sukses / selesai | `--hijau #1e8e52` / `--ch-hijau #27a35f` |
| Peringatan / overdue | `--ch-oranye #f2913b` |
| Bahaya / error | `--merah #c6362b` / `--ch-merah #e63232` |
| Info / berjalan | `--ch-biru #3f7fd4` |
| Highlight / pending | `--kuning #ffc72c` |

## 6. Dashboard Structure

```
Page Header → Filter (divisi/tahun) → Kartu KPI (7, interaktif)
→ Tabel Detail KPI → Kalender mini + Grafik Hasil Pemeriksaan
→ Grafik Distribusi Status + Klasifikasi Risiko
→ Penyelesaian Temuan + Temuan Terbaru → Aktivitas Sistem
```

Kartu KPI: putih, border tipis, radius `0.6rem`, aksen warna pada border/ikon sesuai status.

## 7. Navigation & Buttons

- **Sidebar**: navy, pill menu (`border-radius .45rem`), aktif kuning Pindad.
- **Navbar**: putih kaca, sticky, pita kuning-navy; tombol bell/user icon rounded.
- **Button**: radius `0.45rem`, font Chakra Petch uppercase, state hover/focus/disabled jelas.
- Aksi destruktif wajib `x-confirm-modal`.

## 8. Tables

`.table-responsive` + `table-hover`, header jelas, pagination windowed rounded:

```
«  1 … 5 6 7 … 11  »
```

- Nomor pertama/terakhir selalu tampil, ellipsis di celah, halaman aktif navy + kuning.
- Kolom aksi memakai tombol ikon (`aria-label` + `title`).

## 9. Forms

- Label + kontrol, indikator wajib, pesan validasi inline.
- `form-control`/`form-select` radius `0.45rem`, fokus glow kuning tipis.

## 10. Empty & Error State

- Kosong: ikon rounded + judul + deskripsi (+ CTA bila relevan).
- Error: pesan jelas, tanpa stack trace/informasi sensitif.

## 11. Accessibility

- Input berlabel; button ikon punya `aria-label`.
- Contrast mencukupi; `:focus-visible` diperkuat (outline navy/kuning).
- Satu sumber kebenaran: teks label menyertai warna status.
- Responsive: sidebar offcanvas, form single-column, KPI 1–2 kolom, tabel scroll, modal tak melebihi viewport.

## 12. Final Target

> **Professional Internal Audit Dashboard** — serius, terpercaya, terstruktur, modern, efisien, dan mudah dipakai.