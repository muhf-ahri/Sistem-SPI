# SISTEM SPI
## Sistem Audit Internal (SPI)
### PT Pindad Enjiniring Indonesia

## 1. Gambaran Umum

Sistem SPI adalah aplikasi internal Laravel untuk mendukung proses Audit divisi di PT Pindad Enjiniring Indonesia: perencanaan audit, penugasan auditor, pemeriksaan lapangan, pencatatan temuan, tindak lanjut, verifikasi, hingga laporan (LHA).

> **SPI memeriksa secara langsung, sistem mendokumentasikan prosesnya.**
> Tidak ada modul checklist/daftar pertanyaan pemeriksaan.

**Stack:** Laravel 13 (PHP 8.3+), Blade + Bootstrap 5 + Bootstrap Icons, Tailwind/Vite, Chart.js, barryvdh/laravel-dompdf, MySQL.

## 2. Tujuan Sistem

1. Mengelola rencana & kegiatan audit.
2. Mendokumentasikan pemeriksaan SPI + bukti.
3. Mengelola temuan beserta tingkat risiko.
4. Mengelola tindak lanjut (action plan) divisi.
5. Melakukan verifikasi perbaikan.
6. Menerbitkan & mengelola Laporan Hasil Audit (LHA).
7. Menyediakan analisis pertumbuhan & perbandingan temuan.
8. Menyediakan audit trail aktivitas penting.

## 3. Role Pengguna (Implementasi Aktual)

Sistem hanya memakai **3 peran**:

| Role | Fungsi |
|---|---|
| Super Admin | Mengelola sistem & master data; kontrol administratif (reopen temuan). |
| SPI / Auditor | Audit, pemeriksaan, temuan, verifikasi, LHA. |
| Kepala Divisi | Menindaklanjuti & menyelesaikan temuan divisinya. |

## 4. Model Data Utama

```
Division ──1:N── AuditPlan ──1:N── Inspection ──1:N── Finding ──1:N── ActionPlan ──1:N── Verification
  │                │ 1:N   AuditAssignment (auditor)                │ 1:N FollowUpEvidence
  │                └──1:N── FinalReport (LHA)
  └─1:N User (kepala_divisi per divisi; auditor role SPI)
```

### Enum status

| Entitas | Nilai |
|---|---|
| AuditPlan | `draft`, `scheduled`, `in_progress`, `completed`, `cancelled` |
| Inspection result | `satisfactory`, `needs_improvement`, `non_conformity` |
| Finding | `open`, `in_progress`, `waiting_verification`, `closed`, `rejected` |
| ActionPlan | `pending`, `in_progress`, `submitted`, `verified`, `rejected` |
| Verification result | `approved`, `rejected` |

### Nomor otomatis

- Rencana Audit: `PEN_{kode divisi}_{no urut}_{tahun}`
- Temuan: `FND_{kode divisi}_{no urut}_{tahun}` (contoh `FND_PRO_001_2026`)
- LHA: `LHA_{kode divisi}_{no urut}_{tahun}`

## 5. Hak Akses (Policy, Middleware, Data Scoping)

| Modul | Super Admin | SPI/Auditor | Kepala Divisi |
|---|:---:|:---:|:---:|
| Dashboard | ✓ | ✓ | ✓ (divisinya) |
| Audit + Penugasan | Lihat | CRUD | Lihat divisinya |
| Pemeriksaan + Bukti | Lihat | CRUD | Lihat divisinya |
| Temuan | Lihat | CRUD | Lihat divisinya |
| Buka Kembali Temuan | ✓ | ✓ | – |
| Action Plan + Bukti Perbaikan | Lihat | Lihat | CRUD |
| Verifikasi | – | Kelola | lihat hasil |
| LHA | Lihat | Buat/Unduh | Lihat |
| Analisis Perbandingan Temuan | ✓ | ✓ | ✓ (divisi terkunci) |
| Master Data | CRUD | – | – |
| Audit Log | Lihat | lihat | – |
| Profile | Kelola | Kelola | Kelola |

Perlindungan server-side:
- Kepala Divisi di-scope ke `division_id`-nya di semua query + Policy `view/update`.
- Auditor hanya boleh mengubah data audit yang ia tugaskan (`AuditPlan::assignedTo()`).
- Temuan `closed` tidak bisa diedit manual — hanya via alur verifikasi atau `reopen`.

## 6. Alur Utama

```text
Login → Dashboard → Rencana Audit + Assign Auditor → Mulai Pemeriksaan
→ Pemeriksaan (bukti, hasil) → Ada temuan?
   ├─ Tidak → Selesaikan Audit → Buat LHA
   └─ Ya → Buat Temuan (risiko, rekomendasi)
          → Action Plan divisi → Perbaikan → Bukti → Submit
          → Verifikasi SPI → approved: closed | rejected: perbaikan ulang
→ (opsional) Reopen: closed → open → siklus ulang
→ Analisis & Perbandingan Temuan (2 tahun + divisi)
```

## 7. Otomatisasi Status

| Aksi | Efek |
|---|---|
| Mulai Pemeriksaan | Audit `scheduled/draft → in_progress` |
| Selesaikan Audit | Audit `in_progress → completed` |
| Buat Action Plan | Temuan `open → in_progress` |
| Upload bukti | ActionPlan `pending/rejected → in_progress`; Temuan `rejected → in_progress` |
| Kirim verifikasi | ActionPlan `→ submitted`; Temuan `→ waiting_verification` |
| Setujui | ActionPlan `→ verified`; Temuan `→ closed` |
| Tolak | ActionPlan `→ rejected`; Temuan `→ rejected` |
| Reopen (SPI/SA) | Temuan `closed → open` |

Setiap perubahan tercatat di `AuditLog` (`AuditLogHelper::logStatusChange`).

## 8. Fitur Lain

- **Notifications**: notifikasi target role/divisi/user (SPI, Kepala Divisi) + bell dropdown & halaman notifikasi.
- **Kalender**: kalender bulanan dengan penanda jadwal audit & deadline temuan (mini di dashboard + halaman penuh).
- **Export**: Excel/PDF untuk laporan (`dompdf` / `phpspreadsheet` sesuai format).
- **Pagination**: windowed dengan ellipsis + nomor awal/akhir.

## 9. Dashboard

- Filter divisi/tahun (Super Admin/SPI).
- 7 kartu KPI interaktif (klik → tabel detail di halaman yang sama).
- Kalender mini + grafik Hasil Pemeriksaan.
- Grafik Distribusi Status Temuan (donut) & Klasifikasi Tingkat Risiko (bar).
- Progress penyelesaian + daftar temuan terbaru + aktivitas sistem.

## 10. Aturan Bisnis Kunci

- Satu pemeriksaan hanya menjadi dasar satu temuan (`Inspection` → 1 `Finding`).
- Temuan baru selalu `open`; status tidak diubah manual pada form edit.
- Hanya authorisasi SPI (assigned auditor) yang boleh membuat/verifikasi.
- Keamanan data ditetapkan di server (Policy + scoping), bukan sekadar UI.

## 11. Alur Data Antar Role

```text
SUPER ADMIN (master data)
   ↓
SPI / AUDITOR (audit → pemeriksaan → temuan)
   ↓
KEPALA DIVISI (action plan → perbaikan → bukti → submit)
   ↓
SPI / AUDITOR (verifikasi → approved/rejected)
   ↓
CLOSED → LHA
   ↓
SUPER ADMIN / SPI / KEPALA DIVISI
   (monitoring, laporan, perbandingan temuan)
```

## 12. Kesimpulan

Alur besar: `PERENCANAAN → PEMERIKSAAN → TEMUAN → TINDAK LANJUT → VERIFIKASI → CLOSED → LAPORAN`, dengan dukungan analisis pertumbuhan/perbandingan temuan lintas divisi & tahun, otomatisasi status, dan audit trail menyeluruh.