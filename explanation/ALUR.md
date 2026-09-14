# ALUR APLIKASI SPI
## Sistem Audit Internal — PT Pindad Enjiniring Indonesia

Dokumen ini menjelaskan **cara kerja aplikasi secara bertahap** dari sudut pandang masing-masing role, sesuai implementasi aplikasi saat ini.

---

## 1. Konsep Dasar

> **SPI memeriksa secara langsung, sistem mendokumentasikan prosesnya.**

Aplikasi TIDAK menyediakan checklist atau daftar pertanyaan pemeriksaan.
SPI datang langsung ke divisi, memeriksa, lalu mencatat hasilnya di aplikasi:
pemeriksaan, bukti, temuan, tindak lanjut, verifikasi, hingga laporan.

**Rantai alur besar:**

```
PERENCANAAN → PEMERIKSAAN → TEMUAN → TINDAK LANJUT → VERIFIKASI → CLOSED → LAPORAN (LHA)
```

---

## 2. Role Pengguna (Implementasi Aktual)

Sistem memakai **3 peran**, berlaku untuk semua akun:

| Role | Tugas Utama |
|---|---|
| **Super Admin** | Mengelola sistem & master data. Bukan pemeriksa, tidak membuat temuan/verifikasi. |
| **SPI / Auditor** | Merencanakan audit, memeriksa langsung, membuat temuan, verifikasi, dan menerbitkan Laporan Hasil Audit (LHA). |
| **Kepala Divisi** | Menindaklanjuti temuan divisinya; semua akses & laporan terbatas ke divisi sendiri. |

> Model akses diterapkan dengan Laravel **Policy**, **middleware role**, dan **data scoping divisi** (dicek di server, bukan sekadar menyembunyikan menu).

---

## 3. Alur Super Admin

> Prinsip: **mengelola sistem**, tidak boleh memeriksa, membuat temuan, atau verifikasi.

```
Login
 ↓
Isi Master Data terlebih dahulu:
   ├── Divisi              (kode & nama divisi, contoh: PRO, AKMR, SDM, RKP)
   ├── Jenis Audit         (jenis audit yang dipakai saat membuat rencana)
   ├── Kategori Temuan     (klasifikasi ketidaksesuaian)
   ├── Kategori Risiko     (level: Low, Medium, High, Critical)
   ├── Hari Libur          (definisi libur untuk kalender)
   └── Users               (buat akun Super Admin, SPI, Kepala Divisi; tetapkan role & divisi)
 ↓
Monitoring data Audit penyelenggaraan (lihat saja)
 ↓
Cek Audit Log (jejak aktivitas semua pengguna)
```

- Semua akun dibuat oleh Super Admin (registrasi mandiri tidak tersedia).
- Super Admin juga berwenang **membuka kembali temuan yang sudah closed** (kontrol administratif), namun tidak dalam rantai pemeriksaan/verifikasi.

---

## 4. Alur SPI / Auditor (Role Utama)

### Tahap 1 — Membuat Rencana Audit
1. Login → Dashboard.
2. Menu **Audit → Tambah**.
3. Isi form: divisi yang diperiksa, jenis audit, periode (mulai–selesai), dan auditor yang ditugaskan.
4. Simpan → status awal **Draft/Scheduled**.

### Tahap 2 — Memulai & Melakukan Pemeriksaan
5. Buka detail Audit → klik **Mulai Pemeriksaan** (status *In Progress*).
6. SPI datang ke divisi dan melakukan pemeriksaan langsung.
7. Menu **Pemeriksaan → Tambah Pemeriksaan** (pilih audit terkait):
   - Tanggal & auditor penanggung jawab, ringkasan pemeriksaan.
   - Hasil: **Satisfactory** / **Needs Improvement** / **Non Conformity**.
8. Upload **Bukti Pemeriksaan** di detail pemeriksaan (maks. 10 MB).

### Tahap 3A — Tidak Ada Temuan
9. Jika bersih → detail Audit → **Selesaikan Audit** (status *Completed*).

### Tahap 3B — Ada Temuan
9. Dari detail Audit klik **Buat Temuan** (atau dari detail pemeriksaan):
   - Kategori temuan, tingkat risiko, judul, deskripsi, rekomendasi, batas waktu.
10. Nomor temuan otomatis: `FND_{kode divisi}_{no urut}_{tahun}` (contoh `FND_PRO_001_2026`), status **Open**.
11. Satu pemeriksaan hanya menjadi dasar **satu temuan**.

### Tahap 4 — Menunggu & Memverifikasi
12. Pantau temuan (filter Open, In Progress, Waiting Verification, Overdue).
13. Saat divisi mengirim tindak lanjut → temuan **Waiting Verification**.
14. Buka detail tindak lanjut → **Verifikasi Tindak Lanjut**:
    - **Setujui & Tutup Temuan** → action plan *verified*, temuan *closed* ✅.
    - **Tolak & Kembalikan** → wajib catatan; action plan *rejected*, temuan *rejected* untuk diperbaiki divisi ❌.
15. Ulangi sampai perbaikan disetujui.

### Tahap 5 — Buka Kembali Temuan (Reopen)
- Temuan yang sudah *closed* dapat **dibuka kembali menjadi *open*** jika masalah terulang / perbaikan tidak tuntas.
- Tombol `↺ Buka Kembali` tersedia di detail temuan dan daftar temuan (ikon).
- Hanya **SPI** (pemeriksa) dan **Super Admin** yang berwenang; tercatat di Audit Log sebagai `status_change`.

### Tahap 6 — Laporan Hasil Audit (LHA)
- Setelah audit *completed*, SPI **Buat Laporan** (Laporan Hasil Akhir): upload file LHA (PDF/doc/excel), nomor otomatis `LHA_{kode divisi}_{no urut}_{tahun}`.
- Daftar & unduh LHA di menu **Laporan → Laporan Hasil Audit**.

---

## 5. Alur Kepala Divisi

> Prinsip: **menyelesaikan temuan divisinya sendiri**. Hanya melihat data divisi sendiri.

1. Login → Dashboard (semua angka dari divisinya).
2. Menu **Temuan** → baca temuan beserta rekomendasi SPI.
3. Klik detail temuan → **Buat Tindak Lanjut** (PIC dari user divisinya, rencana aksi, target tanggal).
4. PIC/Kepala Divisi mengerjakan perbaikan lalu upload **Bukti Perbaikan** → action plan *in progress*.
5. Setelah bukti terupload → **Kirim Sekarang** → action plan *submitted*, temuan *waiting verification*.
6. Menunggu verifikasi SPI:
   - **Disetujui** → temuan closed.
   - **Ditolak** → baca catatan SPI (riwayat verifikasi), perbaiki, upload bukti baru, submit ulang.

---

## 6. Analisis & Perbandingan Temuan

Halaman menu **Laporan → Analisis Perbandingan Temuan** (`/reports/comparison`), dapat diakses **seluruh role** (Super Admin, SPI, Kepala Divisi):

- Pilih **Divisi** (opsional; **terkunci otomatis ke divisi sendiri untuk Kepala Divisi**).
- Pilih **dua tahun pembanding** (Tahun Awal & Tahun Akhir) — contoh 2021 dan 2025.
- Menampilkan:
  - KPI: jumlah temuan per tahun, perubahan (%), dan kesimpulan (bertambah/berkurang/stabil).
  - Grafik garis **pertumbuhan temuan per tahun** (dengan penjelasan).
  - Grafik batang **perbandingan status** dan **perbandingan tingkat risiko** antara dua tahun.
  - Tabel rincian per kategori risiko & status + selisih.
- Data bersumber dari `findings.created_at` per tahun dan scope divisi.

---

## 7. Siklus Status

### Temuan

```
OPEN                  ← temuan dibuat SPI
  ↓ (divisi membuat action plan)
IN_PROGRESS           ← divisi sedang memperbaiki
  ↓ (divisi submit bukti)
WAITING_VERIFICATION  ← menunggu SPI memverifikasi
  ↓
  ├── Disetujui → CLOSED ✅
  └── Ditolak   → REJECTED → perbaikan divisi → IN_PROGRESS → ... ↺
```

**Buka kembali (reopen):** `CLOSED → OPEN` (SPI / Super Admin), diterapkan pada temuan; action plan lama tetap tersimpan sebagai riwayat.

### Audit

```
draft → scheduled → in_progress → completed
                              └→ cancelled
```

### Action Plan

```
pending → in_progress → submitted → verified / rejected
```
Upload bukti otomatis mengubah `pending`/`rejected` menjadi `in_progress`.

### Hasil Pemeriksaan

```
satisfactory | needs_improvement | non_conformity
```

---

## 8. Aturan Hak Akses Penting

| Aksi | SA | SPI | Kepala Divisi |
|---|:-:|:-:|:-:|
| Kelola master data & users | ✓ | lihat | – |
| Buat/kelola rencana Audit & penugasan | ✓ | ✓ | lihat* |
| Mulai/selesaikan pemeriksaan & Audit | – | ✓ | – |
| Catat pemeriksaan + bukti | – | ✓ | lihat* |
| Buat temuan | – | ✓ | – |
| Buat action plan & upload bukti perbaikan | – | – | ✓ |
| Submit tindak lanjut | – | – | ✓ |
| Verifikasi tindak lanjut | – | ✓ | lihat |
| Buka kembali temuan (reopen) | ✓ | ✓ | – |
| Buat Laporan Hasil Audit (LHA) | – | ✓ | – |
| Analisis Perbandingan Temuan | ✓ | ✓ | ✓ (divisi terkunci) |
| Audit log | ✓ | lihat | – |

\* lihat = hanya melihat data yang menjadi kewenangannya.

---

## 9. Contoh Skenario Lengkap (End-to-End)

1. **Super Admin** menyiapkan master data & akun (SPI, Kepala Divisi).
2. **SPI** membuat rencana audit, menugaskan auditor, lalu **Mulai Pemeriksaan**.
3. SPI datang ke divisi, mencatat **Pemeriksaan** (hasil *needs_improvement* / *non_conformity*), upload bukti.
4. SPI **Buat Temuan** (risiko High, deadline 14 hari).
5. **Kepala Divisi** buat **Tindak Lanjut** → perbaikan → upload bukti → **Kirim Sekarang**.
6. **SPI** verifikasi → setujui → temuan **Closed**.
7. Audit **Selesai** → SPI **Buat Laporan (LHA)**.
8. **Monitoring** hasil audit & perbandingan temuan via dashboard dan menu **Analisis Perbandingan Temuan**.