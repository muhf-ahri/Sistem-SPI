# Tutorial Update Sistem-SPI ke PC Kantor (via Hardisk)

Panduan menyalin **project Sistem-SPI + database** dari laptop ke PC kantor
lewat **hardisk eksternal**. Database kantor bernama **sama persis** dengan
laptop (`db_spi_pindad`), jadi cara paling aman adalah **mengekspor DB laptop ke
file `.sql`, lalu diimpor di kantor** (menimpa database lama).

> Hasil akhir: kode & data DB di PC kantor **identik** dengan laptop. Data
> lama di PC kantor **akan tertimpa** → lakukan backup dulu (Langkah 6).

> Format setiap langkah: **Apa → Cara → Verifikasi**, biar jelas kapan satu
> tahap selesai sebelum lanjut. Jalankan perintah di **Command Prompt (cmd.exe)**
> dan **bukan PowerShell** kecuali diberi catatan khusus (lihat Langkah 4 & 7 —
> di situlah "setting" saat export/import yang sering jadi bikin pusing).

---

## Daftar Isi

- [Langkah 1 — Pahami isi project](#langkah-1--pahami-isi-project)
- [Langkah 2 — Persiapan di laptop](#langkah-2--persiapan-di-laptop)
- [Langkah 3 — Create file backup (opsional)](#langkah-3--buat-backup-kode-di-laptop-opsional)
- [Langkah 4 — Export database laptop](#langkah-4--export-database-laptop)
- [Langkah 5 — Copy project ke hardisk](#langkah-5--copy-project-ke-hardisk)
- [Langkah 6 — Backup dulu di PC kantor](#langkah-6--backup-dulu-di-pc-kantor)
- [Langkah 7 — Salin project dari hardisk ke PC kantor](#langkah-7--salin-project-dari-hardisk-ke-pc-kantor)
- [Langkah 8 — Import database di PC kantor](#langkah-8--import-database-di-pc-kantor)
- [Langkah 9 — Cek & sesuaikan `.env` kantor](#langkah-9--cek--sesuaikan-env-kantor)
- [Langkah 10 — Bersihkan cache & uji](#langkah-10--bersihkan-cache--uji)
- [Cara alternatif: HeidiSQL (tanpa command line)](#cara-alternatif-heidisql-tanpa-command-line)
- [Troubleshooting](#troubleshooting)
- [Ringkasan perintah cepat](#ringkasan-perintah-cepat)

---

## Persyaratan

| Kebutuhan | Keterangan |
|---|---|
| Laptop (sumber) | Project `D:\laragon\www\Sistem-SPI` + MySQL `db_spi_pindad` berjalan |
| PC kantor (tujuan) | Sudah pernah menjalankan project `D:\laragon\www\Sistem-SPI` + Laragon |
| Hardisk eksternal | Kapasitas ≥ 1 GB (project + file `.sql` relatif kecil) |
| Kedua PC | MySQL user `root` tanpa password (konfig default Laragon) |

> Versi teruji:
> - PHP `8.4.25` → lokasi `D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe`
> - MySQL `8.4.3` → lokasi `D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\`

---

## Langkah 1 — Pahami isi project

Yang perlu disalin ke hardisk ada **2 hal**:

1. **Kode project** — folder `D:\laragon\www\Sistem-SPI`.
   Tidak semua folder wajib ikut (lihat Langkah 5).
2. **Database** — data tersimpan di MySQL, bukan di file project. Karena itu
   harus **di-export jadi 1 file `.sql`**, lalu **di-import** di kantor.

> Kenapa tidak cukup copy folder saja? Karena data (user, master data, temuan,
> dll.) ada di dalam MySQL server `db_spi_pindad`. Folder `C:\laragon\usr\data`
> / `D:\laragon\data` bisa di-copy, tapi caranya kaku & rawan error versi MySQL
> beda. Export/import SQL adalah cara bawaan MySQL yang paling aman & portabel.

---

## Langkah 2 — Persiapan di laptop

1. Buka Laragon → **Stop All** → **Start All**. Pastikan indikator
   **Apache & MySQL** hijau.
2. Pastikan aplikasi bisa dibuka & di-login (uraian data yang akan di-update
   sudah benar).
3. Matikan dulu semua jendela PHP/artisan yang mungkin memegang DB.

---

## Langkah 3 — Buat backup kode di laptop (opsional)

Biar aman sebelum mulai, kalau kodemu belum tersimpan di git:

```bat
cd D:\laragon\www\Sistem-SPI
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan config:clear
git add -A
git commit -m "backup sebelum update ke PC kantor"
```

Kalau foldermu bukan repo git, lewati langkah ini — tujuan utamanya tinggal
memastikan **file `.env` laptop** tercatat isinya (untuk dibandingkan dengan
`.env` kantor di Langkah 9).

---

## Langkah 4 — Export database laptop

**Apa:** Menjadikan seluruh isi `db_spi_pindad` laptop menjadi **satu file
`dump_spi.sql`** yang akan dibawa ke hardisk.

> ⚠️ **SETTING PENTING #1 (encoding):** Buka **Command Prompt** (bukan
> PowerShell) untuk menjalankan perintah di bawah. Jangan pakai `>` di
> **PowerShell** — PowerShell menulis file `.sql` dengan encoding **UTF-16**
> (bercelah), dan hasilnya error saat di-import ("Illegal character sequence"
> / data korup). Cara paling ampuh di bawah memakai opsi `--result-file` yang
> aman dari shell apa pun.

**Cara (Command Prompt atau PowerShell boleh):**

```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=D:\dump_spi.sql
```

Penjelasan opsi:
- `--default-character-set=utf8mb4` — wajib karena database memakai charset
  `utf8mb4`. Tanpa ini karakter non-ASCII (mis. aksen/emotikon) bisa rusak.
- `--single-transaction` — ekspor konsisten tanpa mengunci tabel (InnoDB).
- `--routines` — ikut menyertakan stored procedure/function jika ada.
- `--result-file=D:\dump_spi.sql` — menulis hasil **tanpa lewat stdout redirect**,
  jadi aman dari masalah UTF-16 & bisa dipakai di PowerShell.

**Verifikasi:**
```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --version
dir D:\dump_spi.sql
```
- `dump_spi.sql` harus ada, ukuran mencerminkan isi DB (master-data-only di
  laptop saat ini → ukuran akan kecil, beberapa KB).
- Buka awal filenya dengan Notepad: harus dimulai dengan komentar `-- MySQL
  dump 8.4...` dan berisi pernyataan SQL (`CREATE TABLE`, `INSERT INTO`, dll).
  **File tidak boleh tampak biner/ada karakter aneh di antaranya.**

---

## Langkah 5 — Copy project ke hardisk

Di Windows Explorer, salin ke hardisk:

```
D:\laragon\www\Sistem-SPI   →   E:\sistem-spi-update\Sistem-SPI
D:\dump_spi.sql             →   E:\sistem-spi-update\dump_spi.sql
```

> **Boleh memakai copy biasa**, tetapi untuk hemat waktu & menghindari file
> tak berguna, **boleh pu-skip** folder/file ini (PC kantor sudah punya):
>
> | Jangan ikut (sudah ada/setiap PC beda) | Alasan |
> |---|---|
> | `vendor\` | Dependency Composer — di kantor sudah ada |
> | `node_modules\` | Dependency npm — kantor sudah ada; app ini bahkan tak butuh build |
> | `.env` | Berisi `APP_URL` (IP) khas setiap PC — tidak ikut disalin |
> | `storage\framework\cache\*`, `storage\framework\sessions\*`, `storage\framework\views\*` | File cache/session temporer |
> | `storage\logs\*.log` | Log lama |
> | `.git\` | Riwayat git (kantor pakai foldernya sendiri) |
> | `public\storage` | Symlink (bukan folder fisik) |
> | `public\build`, `public\hot` | Artefak build Vite (project ini tidak memakainya) |

Lepas hardisk, pindah ke PC kantor.

---

## Langkah 6 — Backup dulu di PC kantor

Import di Langkah 8 **menimpa** database kantor. Backup dulu supaya masih bisa
kembali kalau ada yang salah.

```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=C:\backup_kantor.sql
```

> Jika PC kantor memakai versi MySQL/PHP lain, sesuaikan jalur di atas
> (cek isi `D:\laragon\bin\mysql\` dan `D:\laragon\bin\php\`).

**Verifikasi:**
```bat
dir C:\backup_kantor.sql
```
File harus ada dan tidak berukuran 0 byte.

> Backup `.env` kantor juga: salin `C:\laragon\www\Sistem-SPI\.env` → `.env.backup`
> di folder yang sama. (Langkah 9 pakai isinya.)

---

## Langkah 7 — Salin project dari hardisk ke PC kantor

**Apa:** Menimpa kode lama di `D:\laragon\www\Sistem-SPI` dengan kode terbaru.

**Cara:**
1. **Stop All** di Laragon (supaya Apache tidak memegang file).
2. Salin isi `E:\sistem-spi-update\Sistem-SPI\` ke `D:\laragon\www\Sistem-SPI\`
   dengan opsi **overwrite/merge**.
   - File yang sama → ditimpa versi laptop.
   - File yang sudah dihapus di laptop tapi masih ada di kantor → pilih
     "skip" (jangan dihapus), kecuali kamu yakin itu artefak lama.
   - **JANGAN** menimpa `.env` kantor (langkah 9 yang mengatur).
3. Cek struktur tetap ada:
   ```
   D:\laragon\www\Sistem-SPI\
   ├── app\
   ├── bootstrap\
   ├── config\
   ├── database\
   ├── public\
   ├── resources\
   ├── routes\
   ├── storage\
   ├── vendor\        ← dari kantor (dibiarkan apa adanya)
   └── artisan
   ```

**Verifikasi (jalankan dari Command Prompt / PowerShell di folder project):**
```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan --version
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan about
```
Harus ada output versi Laravel, tanpa error `autoload.php not found`
(pertanda `vendor` hilang — pastikan folder `vendor` kantor masih ada).

---

## Langkah 8 — Import database di PC kantor

**Apa:** Mengisi `db_spi_pindad` kantor dengan isi file `dump_spi.sql` (data
laptop). Tabel existing akan tertimpa.

> ⚠️ **SETTING PENTING #2 (import):**
> - Jangan pakai `<` di **PowerShell** — PowerShell **tidak mendukung input
>   redirect** (`<`). Ada 2 cara aman di bawah: `cmd` dengan `<`, atau
>   `--execute="source ..."` yang aman di shell mana pun.
> - Path `source` wajib pakai **forward slash** (`/`).
> - Posisikan working directory di Command Prompt ke folder berisi `dump_spi.sql`.

**Cara A — Command Prompt (cara paling lazim):**

```bat
cd /d E:\sistem-spi-update
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 db_spi_pindad < dump_spi.sql
```

**Cara B — Aman dari PowerShell / cmd (tanpa `<`):**

```powershell
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 --execute="source E:/sistem-spi-update/dump_spi.sql" db_spi_pindad
```

> Jika user root di kantor berpassword, tambahkan `-p` lalu ketik password saat
> diminta:
> `mysql.exe --user=root -p --default-character-set=utf8mb4 db_spi_pindad < dump_spi.sql`

**Verifikasi:**
```bat
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --execute="USE db_spi_pindad; SHOW TABLES;"
```
- Semua tabel aplikasi harus tampil.
- Tambahan cek jumlah data seni bahwa import sukses (di laptop, master data
  saja): `SELECT COUNT(*) FROM divisions;` dan temuan harus 0:
  `SELECT COUNT(*) FROM findings;`

> **Catatan tabel `migrations`:** file `.sql` sudah menyertakan isi tabel
> `migrations` (riwayat migrasi yang pernah dijalankan di laptop). Karena itu
> setelah import, **tidak perlu** menjalankan `php artisan migrate` lagi — status
> migrasi kantor otomatis sinkron dengan laptop. `migrate` hanya boleh dijalankan
> jika nanti ada migration baru yang belum pernah jalan.

---

## Langkah 9 — Cek & sesuaikan `.env` kantor

`.env` kantor didiamkan (tidak ikut ditimpa). Cek 3 hal berikut:

```
D:\laragon\www\Sistem-SPI\.env
```

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

- **`DB_DATABASE`** harus `db_spi_pindad` — sama dengan nama database hasil
  import Langkah 8.
- **`APP_URL`** — ganti ke IP/hostname PC kantor, mis. `http://192.168.250.155`
  (lihat `ipconfig`). Kalau hanya diakses di PC itu sendiri, boleh juga
  `http://Sistem-SPI.test` (sesuai vhost Laragon).
- **`APP_KEY`** — isi dengan **nilai yang sama dengan laptop**
  (`base64:Mq6D39/mxbfaTPTnVBs4W6622uY+DiAVeKKgxYsFmU4=`).
  Alasan: sesi & data terenkripsi yang tersimpan di DB (driver `database`)
  dienkripsi memakai `APP_KEY`. Kalau beda dari laptop, sesi lama tidak terbaca
  → hanya efek "harus login ulang", tapi lebih baik disamakan agar perilaku
  identik dengan laptop.

**Verifikasi:**
```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan config:clear
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan config:show database
```
Harus tampil `database.default.database => db_spi_pindad` dsb. Tampilkan juga
`APP_URL`: `artisan config:show app`.

---

## Langkah 10 — Bersihkan cache & uji

1. Bersihkan semua cache aplikasi:
   ```bat
   D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan optimize:clear
   ```
   (setara `config:clear` + `cache:clear` + `route:clear` + `view:clear`).
2. **Stop All → Start All** di Laragon agar Apache & MySQL membaca semua yang
   baru.
3. Buka browser: `http://Sistem-SPI.test/` (PC kantor sendiri) atau
   `http://<IP-PC-KANTOR>/Sistem-SPI` (dari perangkat lain).
4. **Login ulang** — sesi lama hangus karena data `sessions` ikut diganti
   hasil import (normal). Kalau muncul 419/CSRF, buka **incognito** atau
   bersihkan cookie situs.

**Verifikasi akhir (sudah masuk aplikasi):**
- Menu master data: `divisi`, `tipe audit`, `kategori temuan`, `tingkat risiko`,
  `user`, `kalender` → data semua ada.
- Daftar **Temuan** / **Audit** → kosong (karena DB laptop hanya berisi master
  data saat update).
- Form **Buat Temuan** → kolom "Batas Waktu Tindak Lanjut" **tidak terikat jadwal
  audit** — boleh diisi tanggal setelah selesai audit (tanpa batasan apa pun).

---

## Cara alternatif: HeidiSQL (tanpa command line)

Kalau tidak mau pakai command line, Laragon menyediakan **HeidiSQL**
(Menu **Tools** → **HeidiSQL**).

**Export (laptop):**
1. Klik kiri nama server → expand `db_spi_pindad` → klik kanan
   `db_spi_pindad` → **Export database as SQL**.
2. Pilih tab **Output**: file → `sql` (jangan "batch"), pilih lokasi tujuan →
   **Export**. Di kanan bawah, pilih karakter **UTF-8**.
3. Simpan sebagai `dump_spi.sql`.

**Import (kantor):**
1. Buka HeidiSQL di PC kantor (pastikan Laragon Start All dulu).
2. Klik kanan `db_spi_pindad` → **Run SQL file...** → pilih `dump_spi.sql`.
3. Biarkan encoding default UTF-8 → **Run**. File berjalan, tabel ditimpa.

---

## Troubleshooting

### 1. Import error `Illegal character sequence` / file korup
**Penyebab:** file `.sql` hasil export `>` di PowerShell → encoding UTF-16.
**Fix:** export ulang pakai `--result-file` (Langkah 4) atau HeidiSQL; jangan
pakai `>` di PowerShell.

### 2. Error `'aa' is not recognized as an internal or external command`
**Penyebab:** menjalankan `<`/`>` di PowerShell, atau path bercelah belum
dikutip.
**Fix:** jalankan via **Command Prompt** (Langkah 8 Cara A), kutip path
bercelah dengan tanda kutip ganda.

### 3. `Access denied for user 'root'@'localhost'`
**Penyebab:** password root MySQL kantor tidak kosong.
**Fix:** tambahkan `-p` pada perintah, lalu isi password; atau sesuaikan
`DB_USERNAME`/`DB_PASSWORD` di `.env` kantor.

### 4. Login gagal / 419 Page Expired setelah update
**Penyebab:** session lama tersimpan di DB lama / `APP_KEY` berubah.
**Fix:** buka incognito, atau hapus cookie situs, lalu login ulang.

### 5. Error `The stream does not support seeking`
**Penyebab:** `source` dipakai di lokasi yang salah / path tak sesuai format.
**Fix:** pastikan working directory tempat `dump_spi.sql` dan path `source`
memakai **forward slash** (`/`), mis. `source E:/sistem-spi-update/dump_spi.sql`.

### 6. Halaman masih menampilkan data lama / versi kode lama
**Penyebab:** cache aplikasi belum dibersihkan — atau Apache tidak benar-benar
restart.
**Fix:** `artisan optimize:clear` lalu **Stop All → Start All** (jangan hanya
klik restart). Cek juga bahwa `.env` kantor bukan salinan lama yang masih
menunjuk IP lain.

### 7. `No connection ... target machine actively refused :3306`
**Penyebab:** MySQL di kantor tidak jalan.
**Fix:** Laragon → Start All, pastikan Apache & MySQL hijau; cek
`netstat -ano | findstr :3306` harus ada `LISTENING`.

---

## Ringkasan perintah cepat

```bat
:: EXPORT — laptop (aman di PowerShell & cmd, pakai --result-file)
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=D:\dump_spi.sql

:: BACKUP — kantor, sebelum import
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe --user=root --default-character-set=utf8mb4 --single-transaction --routines db_spi_pindad --result-file=C:\backup_kantor.sql

:: IMPORT — kantor, CARA A (Command Prompt)
cd /d E:\sistem-spi-update
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 db_spi_pindad < dump_spi.sql

:: IMPORT — kantor, CARA B (PowerShell / cmd, tanpa <)
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 --execute="source E:/sistem-spi-update/dump_spi.sql" db_spi_pindad

:: Clear cache + cek versi — jalankan dari folder project
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan optimize:clear
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe artisan --version

:: Cek data setelah import (harusnya divisions=17, findings=0)
D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --user=root --default-character-set=utf8mb4 --execute="USE db_spi_pindad; SELECT COUNT(*) FROM divisions; SELECT COUNT(*) FROM findings;"
```