# Tutorial Update Sistem-SPI ke PC Kantor (via Hardisk)

Panduan menyalin **project Sistem-SPI + database** dari laptop ke PC kantor
lewat **hardisk eksternal**. Database kantor bernama **sama persis** dengan
laptop (`db_spi_pindad`), jadi cara paling aman adalah **mengekspor DB laptop ke
file `.sql`, lalu diimpor di kantor** (menimpa database lama).

> Hasil akhir: kode & data DB di PC kantor **identik** dengan laptop. Data
> lama di PC kantor **akan tertimpa** → lakukan backup dulu (Bagian 2, Langkah
> B5).

Ada **2 cara mengerjakan export/import database** — pilih salah satu:

| Cara | Cocok kalau | Perlu terminal? |
|---|---|---|
| **CARA A — Terminal** (`mysqldump`) | Bisa buka Command Prompt | **Ya** |
| **CARA B — phpMyAdmin** | Ingin semua lewat klik-klik GUI | **Tidak** |

- **CARA A** dijelaskan lengkap di [Bagian 2](#bagian-2--cara-a-terminal-mysqldump).
- **CARA B** dijelaskan lengkap & berdiri sendiri di [Bagian 3](#bagian-3--cara-b-phpmyadmin-tanpa-terminal).
- Kedua cara menghasilkan file `dump_spi.sql` yang sama fungsinya & melewati
  langkah-langkah umum yang sama (persiapan, salin project, atur `.env`, uji).

> **Buat yang baru pindah:** langsung ikuti **Bagian 3 (phpMyAdmin)** — tidak
> butuh perintah apa pun kecuali kalau muncul data lama (Bagian 3, Langkah 8).

---

## Daftar Isi

- [Persyaratan](#persyaratan)
- [Bagian 1 — Persiapan (sama untuk kedua cara)](#bagian-1--persiapan-sama-untuk-kedua-cara)
  - [Langkah 1 — Pahami isi project](#langkah-1--pahami-isi-project)
  - [Langkah 2 — Persiapan di laptop](#langkah-2--persiapan-di-laptop)
  - [Langkah 3 — Back up kode di laptop (opsional)](#langkah-3--back-up-kode-di-laptop-opsional)
  - [Langkah 4 — Salin project ke hardisk](#langkah-4--salin-project-ke-hardisk)
- [Bagian 2 — CARA A: Terminal (mysqldump)](#bagian-2--cara-a-terminal-mysqldump)
  - [Langkah A5 — Export DB laptop](#langkah-a5--export-db-laptop)
  - [Langkah A6 — Back up DB kantor](#langkah-a6--back-up-db-kantor)
  - [Langkah A7 — Salin project & import DB kantor](#langkah-a7--salin-project--import-db-kantor)
- [Bagian 3 — CARA B: phpMyAdmin (tanpa terminal)](#bagian-3--cara-b-phpmyadmin-tanpa-terminal)
  - [Langkah B5 — Back up DB kantor](#langkah-b5--back-up-db-kantor)
  - [Langkah B6 — Export DB laptop](#langkah-b6--export-db-laptop)
  - [Langkah B7 — Salin project & import DB kantor](#langkah-b7--salin-project--import-db-kantor)
- [Bagian 4 — Penutup (kedua cara)](#bagian-4--penutup-kedua-cara)
- [Troubleshooting](#troubleshooting)
- [Ringkasan perintah cepat (CARA A)](#ringkasan-perintah-cepat-cara-a)

---

## Persyaratan

| Kebutuhan | Keterangan |
|---|---|
| Laptop (sumber) | Project `D:\laragon\www\Sistem-SPI` + MySQL `db_spi_pindad` berjalan |
| PC kantor (tujuan) | Sudah pernah menjalankan project `D:\laragon\www\Sistem-SPI` + Laragon (atau XAMPP) |
| Hardisk eksternal | Kapasitas ≥ 1 GB (project + file `.sql` relatif kecil) |
| Cara A (terminal) | MySQL client: `D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\` |
| Cara B (phpMyAdmin) | phpMyAdmin di browser (XAMPP) atau HeidiSQL (Laragon) |

> Versi teruji:
> - PHP `8.4.25` → `D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe`
> - MySQL `8.4.3` → `D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\`
> - PHPMyAdmin tersedia untuk PC yang memakai **XAMPP**; **Laragon** menyertakan
>   **HeidiSQL** (program sejenis, menu Tools). Bagian 3 tetap berlaku — hanya
>   nama tombolnya sedikit beda (lihat catatan per langkah).

---

# Bagian 1 — Persiapan (sama untuk kedua cara)

## Langkah 1 — Pahami isi project

Yang perlu disalin ke hardisk ada **2 hal**:

1. **Kode project** — folder `D:\laragon\www\Sistem-SPI`.
   Tidak semua folder wajib ikut (lihat Langkah 4).
2. **Database** — data tersimpan di MySQL, bukan di file project. Karena itu
   harus **di-export jadi 1 file `.sql`**, lalu **di-import** di kantor.

> Kenapa tidak cukup copy folder saja? Karena data (user, master data, temuan,
> dll.) ada di dalam MySQL server `db_spi_pindad`. Folder data MySQL bisa
> di-copy, tapi caranya kaku & rawan error versi MySQL beda. Export/import SQL
> adalah cara bawaan MySQL yang paling aman & portabel.

## Langkah 2 — Persiapan di laptop

1. Buka Laragon → **Stop All** → **Start All**. Pastikan indikator
   **Apache & MySQL** hijau.
2. Pastikan aplikasi bisa dibuka & di-login (uraian data yang akan di-update
   sudah benar).
3. Matikan dulu semua jendela PHP/artisan yang mungkin memegang DB.

## Langkah 3 — Back up kode di laptop (opsional)

Biar aman sebelum mulai, kalau kodemu belum tersimpan di git:

```bat
cd D:\laragon\www\Sistem-SPI
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan config:clear
git add -A
git commit -m "backup sebelum update ke PC kantor"
```

Kalau foldermu bukan repo git, lewati langkah ini — tujuan utamanya tinggal
memastikan **file `.env` laptop** tercatat isinya (untuk dibandingkan dengan
`.env` kantor di Bagian 4, Langkah 6).

## Langkah 4 — Salin project ke hardisk

Di Windows Explorer, salin ke hardisk:

```
D:\laragon\www\Sistem-SPI   →   E:\sistem-spi-update\Sistem-SPI
```

> **Boleh pakai copy biasa** (seluruh folder), tetapi untuk hemat waktu &
> menghindari file tak berguna, **boleh skip** folder/file ini (PC kantor
> sudah punya):
>
> | Jangan ikut (sudah ada / khas tiap PC) | Alasan |
> |---|---|
> | `vendor\` | Dependency Composer — di kantor sudah ada |
> | `node_modules\` | Dependency npm — kantor sudah ada; app ini bahkan tak butuh build |
> | `.env` | Berisi `APP_URL` (IP) khas setiap PC — tidak ikut disalin |
> | `storage\framework\cache\*`, `storage\framework\sessions\*`, `storage\framework\views\*` | File cache/session temporer |
> | `storage\logs\*.log` | Log lama |
> | `.git\` | Riwayat git (kantor pakai foldernya sendiri) |
> | `public\storage` | Symlink (bukan folder fisik) |
> | `public\build`, `public\hot` | Artefak build Vite (project ini tidak memakainya) |

> **Yang WAJIB ikut:** file bukti/evidence yang pernah diupload ada di
> `storage\app\public\` — ikut ter-salin bersama folder project. Kalau cara
> "skip" dipakai, jangan sampai folder `storage\app\public` ikut di-skip.

Sekarang bagian export/import DB. **Pilih salah satu cara:**

- Kalau berani buka terminal → **Bagian 2 (CARA A)**.
- Kalau mau klik-klik di browser → **Bagian 3 (CARA B)**.

Setelah selesai bagian 2 **atau** 3, lanjut ke **Bagian 4 (penutup)**.

---

# Bagian 2 — CARA A: Terminal (mysqldump)

> Jalanan perintah: **Command Prompt (cmd.exe)**. Bukan PowerShell kecuali
> diberi catatan khusus — di sinilah "setting" export/import sering jadi
> bikin pusing.

## Langkah A5 — Export DB laptop

**Apa:** Menjadikan seluruh isi `db_spi_pindad` laptop menjadi **satu file
`dump_spi.sql`** yang akan dibawa ke hardisk.

> ⚠️ **Setting penting (encoding):** Jangan pakai `>` di **PowerShell** —
> PowerShell menulis file `.sql` dengan encoding **UTF-16** yang rusak saat
> di-import ("Illegal character sequence"). Perintah di bawah memakai opsi
> `--result-file` yang aman dari shell apa pun (cmd maupun PowerShell).

**Cara:**

```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=E:\sistem-spi-update\dump_spi.sql
```

Penjelasan opsi:
- `--default-character-set=utf8mb4` — wajib karena database memakai charset
  `utf8mb4`. Tanpa ini karakter non-ASCII bisa rusak.
- `--single-transaction` — ekspor konsisten tanpa mengunci tabel (InnoDB).
- `--routines` — ikut menyertakan stored procedure/function jika ada.
- `--result-file=...` — menulis hasil **tanpa lewat stdout redirect**, jadi aman
  dari masalah UTF-16.

**Verifikasi:**
```bat
dir E:\sistem-spi-update\dump_spi.sql
```
- File harus ada, ukuran mencerminkan isi DB (master-data-only di laptop saat ini
  → ukuran kecil, beberapa KB).
- Buka awal filenya dengan Notepad: harus dimulai dengan komentar `-- MySQL
  dump 8.4...` dan berisi `CREATE TABLE`, `INSERT INTO`, dll. **File tidak boleh
  tampak biner / ada karakter aneh.**

## Langkah A6 — Back up DB kantor

Import nanti **menimpa** database kantor. Backup dulu supaya masih bisa kembali
kalau ada yang salah.

```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=C:\backup_kantor.sql
```

> Jika PC kantor memakai versi MySQL lain, sesuaikan jalur (cek isi
> `D:\laragon\bin\mysql\`).

**Verifikasi:** `dir C:\backup_kantor.sql` → file ada, tidak 0 byte.

> Backup `.env` kantor juga: salin `D:\laragon\www\Sistem-SPI\.env` →
> `.env.backup` di folder yang sama. (Dipakai di Bagian 4, Langkah 6.)

## Langkah A7 — Salin project & import DB kantor

**7a. Salin project dari hardisk (di PC kantor):**
1. **Stop All** di Laragon (supaya Apache tidak memegang file).
2. Salin isi `E:\sistem-spi-update\Sistem-SPI\` ke `D:\laragon\www\Sistem-SPI\`
   dengan opsi **overwrite/merge**.
   - File yang sama → ditimpa versi laptop.
   - File yang sudah dihapus di laptop tapi masih ada di kantor → pilih "skip"
     (jangan dihapus), kecuali kamu yakin artefak lama.
   - **JANGAN** menimpa `.env` kantor (diatur di Bagian 4).
3. Cek struktur tetap ada: `app\`, `bootstrap\`, `config\`, `database\`,
   `public\`, `resources\`, `routes\`, `storage\`, `vendor\`, dan file `artisan`.

**Verifikasi (CMD/PowerShell di folder project):**
```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan --version
```
Tanpa error `autoload.php not found` (pertanda `vendor` kantor hilang).

**7b. Import DB kantor** — isi `db_spi_pindad` kantor dengan `dump_spi.sql`.

> ⚠️ **Setting penting (import):** Jangan pakai `<` di **PowerShell** —
> PowerShell tidak mendukung input redirect. Pilih Cara A (cmd) atau Cara B
> (`source`), di bawah.

**Cara A — Command Prompt:**
```bat
cd /d E:\sistem-spi-update
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 db_spi_pindad < dump_spi.sql
```

**Cara B — Aman dari PowerShell / cmd (tanpa `<`):**
```powershell
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 --execute="source E:/sistem-spi-update/dump_spi.sql" db_spi_pindad
```

> Jika user root di kantor berpassword: tambahkan `-p` lalu ketik password saat
> diminta: `mysql.exe --user=root -p --default-character-set=utf8mb4 db_spi_pindad < dump_spi.sql`

**Verifikasi:**
```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --execute="USE db_spi_pindad; SHOW TABLES;"
```
Semua tabel aplikasi harus tampil. Cek jumlah data (di laptop, master data
saja): `SELECT COUNT(*) FROM divisions;` → 17, dan `SELECT COUNT(*) FROM
findings;` → 0.

> **Catatan tabel `migrations`:** file `.sql` sudah menyertakan isi tabel
> `migrations` (riwayat migrasi laptop). Setelah import, **tidak perlu**
> `php artisan migrate` — status migrasi kantor otomatis sinkron.

---

# Bagian 3 — CARA B: phpMyAdmin (tanpa terminal)

> Tanpa terminal — semua lewat klik di browser phpMyAdmin. Export & import
> adalah **2 klik** memakai tab **Export** dan **Import**. Perhatian utamanya
> cuma satu: **"Add DROP TABLE"** saat export supaya tabel lama di kantor
> benar-benar tertimpa (Langkah B6).

> **Catatan Laragon:** kalau PC-mu pakai Laragon (bukan XAMPP), buka **HeidiSQL**
> (Menu **Tools** → **HeidiSQL**) — langkah sama: klik kanan DB → *Export database
> as SQL* untuk export, *Run SQL file...* untuk import. Template phpMyAdmin di
> bawah tetap jadi acuan.

## Langkah B5 — Back up DB kantor

Import nanti **menimpa** database kantor. Backup dulu (di PC kantor!):

1. Buka phpMyAdmin kantor di browser: `http://localhost/phpmyadmin`
   (tampil halaman login kalau root berpassword).
2. Klik database **`db_spi_pindad`** (di panel kiri).
3. Klik tab **Export**.
4. Pilih **"Quick"** → klik **Export**. File `backup_kantor.sql` terunduh.
5. Simpan ke tempat aman (folder proyek / hardisk). Jangan dihapus dulu sampai
   semuanya jalan.

> Backup `.env` kantor juga: salin `D:\laragon\www\Sistem-SPI\.env` →
> `.env.backup` di folder yang sama. (Dipakai di Bagian 4, Langkah 6.)

## Langkah B6 — Export DB laptop

**Apa:** Menjadikan `db_spi_pindad` laptop menjadi satu file `dump_spi.sql`.

1. Buka phpMyAdmin laptop: `http://localhost/phpmyadmin`.
2. Klik database **`db_spi_pindad`** (panel kiri).
3. Klik tab **Export**.
4. Pilih **"Custom"** (jangan Quick — biar kita bisa atur "DROP"):
   - Bagian **Format**: pastikan **SQL**.
   - Bagian **"Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER
     statement"**: **centang** ✅ — ini yang membuat tabel lama di kantor
     terhapus dulu lalu dibuat ulang dari file (import jadi bersih, tanpa error).
   - Opsi lain boleh dibiarkan default (termasuk **charset `utf-8`**).
   - **Output**: pilih "Save output to a file" → nama `dump_spi.sql`.
5. Klik **Export** → file terunduh.

**Verifikasi:** buka `dump_spi.sql` dengan Notepad — isinya harus diawali komentar
`-- phpMyAdmin SQL Dump` dan berisi baris `DROP TABLE IF EXISTS ...` lalu
`CREATE TABLE ...` dan `INSERT INTO ...`. Kalau tidak ada `DROP TABLE`, ulangi
langkah 4 dengan centang yang aktif.

> Di HeidiSQL (Laragon): klik kanan `db_spi_pindad` → *Export database as SQL*
> → tab **Output**: "file" → opsi **"Create triggers"**, **"Add DROP"** → Export
> → di kanan bawah pilih karakter **UTF-8**.

## Langkah B7 — Salin project & import DB kantor

**7a. Salin project dari hardisk (di PC kantor):**
1. **Stop All** di Laragon (supaya Apache tidak memegang file).
2. **Bisa rename project lama** dulu biar ada cadangan: folder
   `D:\laragon\www\Sistem-SPI` → `Sistem-SPI-lama` (cara paling aman & mudah
   diingat — tidak perlu hapus apa pun).
3. Copy folder dari hardisk: `E:\sistem-spi-update\Sistem-SPI` →
   `D:\laragon\www\Sistem-SPI`.
4. JANGAN menyalin `.env` dari laptop — `.env` kantor diatur di Bagian 4,
   Langkah 6.

**7b. Import DB kantor via phpMyAdmin:**
1. Buka phpMyAdmin kantor → klik database **`db_spi_pindad`**.
   - Kalau database belum ada: klik tab **SQL** di halaman utama, isi
     `CREATE DATABASE db_spi_pindad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
     lalu Go.
2. Klik tab **Import**.
3. **File to import**: pilih `dump_spi.sql` (dari hardisk).
4. Bagian **Format**: **SQL** (default sudah benar).
5. Klik tombol **Import** → tunggu sampai ada pesan sukses ("Import has been
   successfully finished").

> Karena `dump_spi.sql` berisi `DROP TABLE IF EXISTS`, tabel lama otomatis
> diganti isi laptop — tidak perlu menghapus tabel manual dulu.

**Verifikasi:** kembali ke daftar database, klik `db_spi_pindad` → lihat daftar
tabel (kiri bawah): semua tabel aplikasi harus ada (`users`, `audit_plans`,
`findings`, `monitoring_reports`, dll). Klik `divisions` → **Browse** → ada 17
baris; klik `findings` → **Browse** → kosong.

---

# Bagian 4 — Penutup (kedua cara)

## Langkah 6 — Cek & sesuaikan `.env` kantor

`.env` kantor didiamkan (tidak ikut ditimpa). Edit file
`D:\laragon\www\Sistem-SPI\.env` dengan Notepad, pastikan 4 hal ini:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:...TOMBOL...     ← SEJAK SEKARANG pakai APP_KEY laptop (lihat catatan)
APP_DEBUG=false
APP_URL=http://<IP-PC-KANTOR>   ← IP kantor sendiri, bukan IP laptop 192.168.250.192

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_spi_pindad       ← harus persis nama ini (sama dengan laptop)
DB_USERNAME=root
DB_PASSWORD=                    ← kosong (default Laragon) / isi bila kantor berpw
```

- **`DB_DATABASE`** harus `db_spi_pindad` — sama dengan nama database hasil import.
- **`APP_URL`** — ganti ke IP/hostname PC kantor, mis. `http://192.168.250.155`
  (lihat hasil `ipconfig` di PC kantor). Kalau hanya diakses di PC itu sendiri,
  boleh juga `http://Sistem-SPI.test` (vhost Laragon) atau
  `http://localhost/Sistem-SPI/public` (XAMPP).
- **`APP_KEY`** — isi dengan **nilai yang sama dengan laptop**
  (`base64:Mq6D39/mxbfaTPTnVBs4W6622uY+DiAVeKKgxYsFmU4=`).
  Alasan: sesi & data terenkripsi di DB (driver `database`) memakai `APP_KEY`.
  Kalau beda dari laptop, efeknya hanya "harus login ulang"; disamakan supaya
  perilaku identik.

## Langkah 7 — Bersihkan cache & uji

1. (Kedua cara) **Stop All → Start All** di Laragon agar Apache & MySQL membaca
   semua yang baru.
2. Buka browser: `http://Sistem-SPI.test/` (Laragon) atau
   `http://localhost/Sistem-SPI/public` (XAMPP).
3. **Login ulang** — sesi lama hangus karena data `sessions` ikut diganti hasil
   import (normal). Kalau muncul 419/CSRF, buka **incognito**.

**Hanya kalau data lama masih tampil** → cache aplikasi belum bersih. Jalankan 1
perintah ini dari folder project (CMD/PowerShell):
```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan optimize:clear
```
(Setara `config:clear` + `cache:clear` + `route:clear` + `view:clear`.)

**Verifikasi akhir (sudah masuk aplikasi):**
- Menu master data: `divisi`, `tipe audit`, `kategori temuan`, `tingkat risiko`,
  `user`, `kalender` → semua ada.
- Daftar **Temuan** / **Audit** → kosong (karena DB laptop hanya berisi master
  data saat update).
- Form **Buat Temuan** → kolom "Batas Waktu Tindak Lanjut" **tidak terikat jadwal
  audit** — boleh melewati tanggal selesai audit.
- Fungsi baru **Laporan Monitoring** → muncul di menu Laporan & detail Audit.
- Download/pratinjau bukti (PDF/Excel) berfungsi.
- Setelah semua dipastikan jalan, folder cadangan `Sistem-SPI-lama` dan file
  `backup_kantor.sql` boleh dihapus.

---

## Troubleshooting

### 1. Import error `Illegal character sequence` / file korup
**Penyebab:** file `.sql` hasil export `>` di PowerShell → encoding UTF-16.
**Fix:** export ulang pakai `--result-file` (Cara A) atau phpMyAdmin/HeidiSQL
(Cara B).

### 2. Tab Import phpMyAdmin tidak muncul / tombol tersembunyi
**Penyebab:** belum mengklik database `db_spi_pindad` di panel kiri.
**Fix:** klik nama database dulu, baru tab **Import** muncul.

### 3. Import phpMyAdmin berhenti / error `#1064` / "DROP TABLE" gagal
**Penyebab:** file export tidak punya `DROP TABLE IF EXISTS`, atau format tidak
SQL.
**Fix:** export ulang pakai **Custom** + centang **Add DROP TABLE** (Langkah B6),
pastikan Format = SQL.

### 4. Upload file dump ditolak phpMyAdmin (ukuran besar)
**Penyebab:** batas `upload_max_filesize`/`post_max_size` di PHP (default 2MB).
**Fix:** dump proyek ini kecil (master-data-only, beberapa KB), jadi jarang
terjadi. Kalau besar: buka `php.ini`, naikkan nilai `upload_max_filesize` &
`post_max_size` jadi `128M`, restart web server.

### 5. Error `'aa' is not recognized as an internal or external command`
**Penyebab:** menjalankan `<`/`>` di PowerShell, atau path bercelah belum dikutip.
**Fix:** jalankan via **Command Prompt** (Cara A Langkah 7b).

### 6. `Access denied for user 'root'@'localhost'`
**Penyebab:** password root MySQL kantor tidak kosong.
**Fix:** tambahkan `-p` lalu isi password (Cara A); atau sesuaikan
`DB_USERNAME`/`DB_PASSWORD` di `.env` kantor.

### 7. Login gagal / 419 Page Expired setelah update
**Penyebab:** session lama / `APP_KEY` berubah.
**Fix:** buka incognito, atau hapus cookie situs, lalu login ulang.

### 8. Error `The stream does not support seeking`
**Penyebab:** `source` dipakai di lokasi salah / path tidak ber-format.
**Fix:** pastikan path `source` memakai **forward slash** (`/`), mis.
`source E:/sistem-spi-update/dump_spi.sql`.

### 9. Halaman masih menampilkan data / versi kode lama
**Penyebab:** cache aplikasi belum dibersihkan, atau Apache tidak benar-benar
restart.
**Fix:** `artisan optimize:clear` lalu **Stop All → Start All** (jangan hanya
restart). Cek juga `.env` kantor bukan salinan lama yang menunjuk IP lain.

### 10. `No connection ... target machine actively refused :3306`
**Penyebab:** MySQL di kantor tidak jalan.
**Fix:** Laragon → Start All, pastikan Apache & MySQL hijau; cek
`netstat -ano | findstr :3306` harus ada `LISTENING`.

---

## Ringkasan perintah cepat (CARA A)

```bat
:: EXPORT — laptop (aman di PowerShell & cmd, pakai --result-file)
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=E:\sistem-spi-update\dump_spi.sql

:: BACKUP — kantor, sebelum import
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=C:\backup_kantor.sql

:: IMPORT — kantor, CARA A (Command Prompt)
cd /d E:\sistem-spi-update
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 db_spi_pindad < dump_spi.sql

:: IMPORT — kantor, CARA B (PowerShell / cmd, tanpa <)
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 --execute="source E:/sistem-spi-update/dump_spi.sql" db_spi_pindad

:: Clear cache — jalankan dari folder project (kalau data lama masih tampil)
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan optimize:clear

:: Cek data setelah import (divisions=17, findings=0)
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 --execute="USE db_spi_pindad; SELECT COUNT(*) FROM divisions; SELECT COUNT(*) FROM findings;"
```