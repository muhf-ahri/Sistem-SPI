# FUNGSI SISTEM SPI
## Penjelasan Fungsi Setiap Modul Berdasarkan Role
### PT Pindad Enjiniring Indonesia

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Fungsi Umum Semua Role](#2-fungsi-umum-semua-role)
3. [Role Super Admin](#3-role-super-admin)
4. [Role SPI / Auditor](#4-role-spi--auditor)
5. [Role Kepala Divisi](#5-role-kepala-divisi)
6. [Analisis & Perbandingan Temuan](#6-analisis--perbandingan-temuan)
7. [Otomatisasi Status Sistem](#7-otomatisasi-status-sistem)
8. [Ringkasan Matriks Fungsi](#8-ringkasan-matriks-fungsi)

---

## 1. Gambaran Umum

Dokumen ini menjelaskan **fungsi setiap modul yang telah dibangun** di aplikasi SPI, dikelompokkan berdasarkan role pengguna.

Role yang tersedia (implementasi aktual — hanya 3):

| Role | Peran Utama |
|---|---|
| **Super Admin** | Mengelola sistem dan master data |
| **SPI / Auditor** | Melakukan audit, pemeriksaan, temuan, verifikasi, dan LHA |
| **Kepala Divisi** | Menindaklanjuti dan menyelesaikan temuan divisinya |

---

## 2. Fungsi Umum Semua Role

### 2.1 Autentikasi (Login)

| Fungsi | Penjelasan |
|---|---|
| Login | Email kantor dan password. Registrasi publik **dinonaktifkan** — akun hanya dibuat Super Admin. |
| Lupa Password | Reset via tautan email terdaftar. |
| Logout | Keluar dari sistem. |

### 2.2 Dashboard (per role)

Dashboard menyesuaikan peran. Elemen umum:

- **7 Kartu KPI** interaktif: Audit Selesai, Audit Berlangsung, Belum DiAudit, Total Temuan, Belum Ditindaklanjuti, Ditindaklanjuti Sebagian, Selesai Ditindaklanjuti — klik membuka tabel detail.
- **Kalender mini** (kiri) + grafik **Hasil Pemeriksaan** (kanan).
- Grafik **Distribusi Status Temuan** (donut) & **Klasifikasi Tingkat Risiko** (bar).
- Progress **Penyelesaian Temuan**, daftar temuan terbaru, dan aktivitas sistem.
- Filter divisi & tahun (untuk Super Admin/SPI).

### 2.3 Profil

| Fungsi | Penjelasan |
|---|---|
| Edit Profil | Mengubah nama/data akun. |
| Ganti Password | Mengubah password mandiri. |

---

## 3. Role Super Admin

> **Fungsi role:** mengelola administrasi & konfigurasi sistem.

### 3.1 Manajemen Master Data

| Modul | Fungsi |
|---|---|
| Divisi | CRUD divisi (kode, nama, deskripsi, status aktif) — dasar pembatasan data Kepala Divisi. |
| Jenis Audit | CRUD jenis audit. |
| Kategori Temuan | CRUD klasifikasi temuan. |
| Kategori Risiko | CRUD tingkat risiko (Low/Medium/High/Critical). |
| Users | CRUD akun + role + divisi; aktif/non-aktif; detail. |
| Hari Libur | Kelola hari libur (custom) & lihat libur nasional (sinkronisasi API). |

### 3.2 Audit Log

| Fungsi | Penjelasan |
|---|---|
| Riwayat Aktivitas | Catatan otomatis create/update/delete/status_change/upload — pelaku, waktu, nilai lama/baru. |
| Pencarian & Filter | Cari per user, aktivitas, modul. |

### 3.3 Monitoring & Reopen

- Melihat seluruh data audit, pemeriksaan, temuan, tindak lanjut, laporan (read-only).
- Berwenang **membuka kembali temuan closed** (tombol `↺ Buka Kembali`).

---

## 4. Role SPI / Auditor

> **Fungsi role:** peran utama proses audit.

### 4.1 Rencana Audit (Audit Plans)

| Fungsi | Penjelasan |
|---|---|
| Buat Rencana | Judul, divisi, jenis audit, periode, deskripsi. |
| Penugasan Auditor | Satu/lebih auditor SPI (lead_auditor/auditor). |
| Mulai Pemeriksaan | `scheduled/draft → in_progress`. |
| Selesaikan Audit | `in_progress → completed`. |
| Aktifkan Kembali | `completed → in_progress` (jika perlu). |
| Edit / Hapus | Perbarui/hapus rencana & penugasannya. |
| Filter & Cari | Status, divisi, jenis, tahun, pencarian. |

Status hanya berubah **lewat tombol aksi**, bukan edit manual.

### 4.2 Pemeriksaan Lapangan (Inspections)

| Fungsi | Penjelasan |
|---|---|
| Catat Pemeriksaan | Tanggal, auditor, ringkasan, hasil (`satisfactory` / `needs_improvement` / `non_conformity`), catatan. |
| Upload Bukti | File dokumen/gambar (maks. 10 MB). |
| Detail Pemeriksaan | Ringkasan, bukti, dan temuan yang lahir dari kunjungan. |

### 4.3 Temuan (Findings)

| Fungsi | Penjelasan |
|---|---|
| Buat Temuan | Nomor otomatis `FND_{kode}_{no}_{tahun}`, kategori, risiko, deskripsi, rekomendasi, deadline. Status awal `open`. |
| Edit / Hapus | Temuan yang belum selesai. |
| Detail Temuan | Stepper siklus, risiko & status badge, rencana tindak lanjut, riwayat verifikasi. |
| **Buka Kembali** | Temuan `closed` dibuka lagi ke `open` bila masalah terulang — dengan konfirmasi & dicatat ke Audit Log. |

### 4.4 Verifikasi Tindak Lanjut

| Fungsi | Penjelasan |
|---|---|
| Review Bukti | Memeriksa bukti perbaikan divisi. |
| Setujui | Action plan `verified`, temuan `closed`. |
| Tolak | Wajib catatan; action plan & temuan `rejected`. |
| Riwayat Verifikasi | Verifikator, hasil, catatan, waktu. |

### 4.5 Laporan Hasil Audit (LHA) & Laporan

| Fungsi | Penjelasan |
|---|---|
| Buat Laporan | Upload file LHA pada audit `completed`; nomor otomatis `LHA_{kode}_{no}_{tahun}`. |
| Hapus/Unduh LHA | Kelola & unduh dari menu Laporan. |
| Laporan Lain | Ringkasan Audit, Analisis Temuan, Status Tindak Lanjut, Perbandingan Temuan. |

---

## 5. Role Kepala Divisi

> **Pembatasan data:** hanya data divisi sendiri — di dashboard, daftar, maupun akses langsung URL (Policy + data scoping server-side).

### 5.1 Monitoring Divisi (Read-Only)

| Modul | Fungsi |
|---|---|
| Dashboard | Statistik & progres khusus divisinya. |
| Audit / Pemeriksaan / Temuan | Melihat data yang menyangkut divisinya. |

### 5.2 Rencana Tindak Lanjut (Action Plans)

| Fungsi | Penjelasan |
|---|---|
| Buat Action Plan | Uraian aksi, PIC (user divisinya), target tanggal. |
| Otomatisasi Status | Membuat action plan → temaun `open → in_progress`. |
| Edit / Hapus | Rencana yang belum selesai. |

### 5.3 Bukti Perbaikan & Submit

| Fungsi | Penjelasan |
|---|---|
| Upload Bukti | Maks. 10 MB + keterangan; action plan `pending/rejected → in_progress`, temuan `rejected → in_progress`. |
| Kirim Verifikasi | Action plan `submitted`, temuan `waiting_verification`. |

### 5.4 Laporan & Analisis

- Laporan dibatasi cakupan divisinya.
- **Analisis Perbandingan Temuan**: pilihan divisi **terkunci ke divisi sendiri**, tahun pembanding bebas.

---

## 6. Analisis & Perbandingan Temuan

Modul **Laporan → Analisis Perbandingan Temuan** (`/reports/comparison`) — dapat diakses ketiga role.

| Elemen | Fungsi |
|---|---|
| Filter Divisi | Pilih semua divisi (Super Admin/SPI) atau terkunci ke divisi sendiri (Kepala Divisi). |
| Tahun Awal & Tahun Akhir | Dua tahun bebas dipilih untuk dibandingkan (mis. 2021 vs 2025). |
| KPI | Temuan tahun awal, temuan tahun akhir, perubahan %, kesimpulan bertambah/berkurang/stabil. |
| Grafik Pertumbuhan | Line jumlah temuan per tahun (dengan penjelasan). |
| Grafik Status & Risiko | Bar perbandingan dua tahun dengan legend sesuai tahun terpilih. |
| Tabel Rincian | Status & risiko tahun awal – akhir + selisih (+/−). |

---

## 7. Otomatisasi Status Sistem

| Aksi Pengguna | Efek Otomatis |
|---|---|
| Mulai Pemeriksaan | Audit: `scheduled/draft → in_progress` |
| Selesaikan Audit | Audit: `in_progress → completed` |
| Buat Action Plan | Temuan: `open → in_progress` |
| Upload bukti perbaikan | Action plan: `pending/rejected → in_progress`; Temuan `rejected → in_progress` |
| Kirim verifikasi | Action plan `→ submitted`; Temuan `→ waiting_verification` |
| SPI setujui | Action plan `→ verified`; Temuan `→ closed` |
| SPI tolak | Action plan `→ rejected`; Temuan `→ rejected` |
| SPI/Super Admin reopen | Temuan: `closed → open` |
| Semua aktivitas penting | Tercatat ke Audit Log |

---

## 8. Ringkasan Matriks Fungsi

| Modul | Super Admin | SPI / Auditor | Kepala Divisi |
|---|:---:|:---:|:---:|
| Dashboard (per role) | ✓ | ✓ | ✓ (divisinya) |
| Master Data (Users, Divisi, Jenis, Kategori, Hari Libur) | CRUD | lihat sebagian | — |
| Audit Log | Lihat | lihat | — |
| Rencana Audit + Penugasan | Lihat | CRUD | Lihat (divisinya) |
| Pemeriksaan + Bukti | Lihat | CRUD | Lihat (divisinya) |
| Temuan | Lihat | CRUD | Lihat (divisinya) |
| Buka Kembali Temuan | ✓ | ✓ | — |
| Action Plan + Bukti Perbaikan | Lihat | Lihat | CRUD |
| Verifikasi | — | Kelola | lihat hasil |
| Laporan Hasil Audit (LHA) | Lihat | Buat/Unduh | Lihat |
| Analisis Perbandingan Temuan | ✓ | ✓ | ✓ (divisi terkunci) |
| Profil | Kelola | Kelola | Kelola |

Keterangan: `CRUD = Create, Read, Update, Delete`, `— = tidak memiliki akses`.

---

> **Prinsip utama sistem:**
> SPI memeriksa secara langsung, sistem mendokumentasikan prosesnya —
> setiap aktivitas terekam, setiap perubahan status terotomatisasi, dan setiap role hanya melihat data sesuai kewenangannya.