# Tutorial Instalasi Laravel (Laragon) — PC Fresh

Panduan **lengkap & berurutan** mulai dari PC baru (tanpa aplikasi apa pun)
sampai project Laravel (Laravel 13) berjalan di Laragon, bisa diakses dari
perangkat lain, IP stabil, dan otomatis start saat PC dinyalakan.

> Setiap langkah memakai format **Apa → Cara → Verifikasi** supaya kamu tahu
> persis kapan suatu tahap sukses sebelum lanjut.

> Lingkungan contoh: Windows 10/11, instalasi di `D:\laragon`, web server
> Apache. Sesuaikan huruf drive bila berbeda.

---

## Daftar Isi

- [Langkah 1 — Pasang Laragon](#langkah-1--pasang-laragon)
- [Langkah 2 — Kenali struktur folder Laragon](#langkah-2--kenali-struktur-folder-laragon)
- [Langkah 3 — Cek & tambah PHP 8.4](#langkah-3--cek--tambah-php-84)
- [Langkah 4 — Aktifkan PHP 8.4 sebagai versi pilihan](#langkah-4--aktifkan-php-84-sebagai-versi-pilihan)
- [Langkah 5 — Cek persyaratan project](#langkah-5--cek-persyaratan-project)
- [Langkah 6 — Salin project ke www](#langkah-6--salin-project-ke-www)
- [Langkah 7 — Siapkan database](#langkah-7--siapkan-database)
- [Langkah 8 — Atur `.env`](#langkah-8--atur-env)
- [Langkah 9 — Jalankan migration & seeder](#langkah-9--jalankan-migration--seeder)
- [Langkah 10 — Uji di PC server](#langkah-10--uji-di-pc-server)
- [Langkah 11 — Akses dari perangkat lain (IP)](#langkah-11--akses-dari-perangkat-lain-ip)
- [Langkah 12 — IP statis](#langkah-12--ip-statis)
- [Langkah 13 — Auto-start saat PC dinyalakan](#langkah-13--auto-start-saat-pc-dinyalakan)
- [Troubleshooting](#troubleshooting)
- [Ringkasan folder penting](#ringkasan-folder-penting)
- [Perintah cepat](#perintah-cepat)

---

## Persyaratan

| Kebutuhan | Keterangan |
|---|---|
| Windows | 10 atau 11 (64-bit) |
| Koneksi internet | Untuk mengunduh installer & PHP 8.4 |
| Hak akses | Bisa install program & edit file di `C:\Windows\System32\drivers\etc` (admin) |
| Project | Folder `Sistem-SPI` lengkap (termasuk folder `vendor`) |

> **Kenapa PHP 8.4?** Project ini memakai Laravel 13 + Symfony 8 yang **wajib
> PHP ≥ 8.4.1**. PHP bawaan Laragon (8.3) tidak akan lolos pemeriksaan Composer.

---

## Langkah 1 — Pasang Laragon

**Apa:** Menginstal Laragon (portable web server environment).

**Cara:**
1. Kunjungi [laragon.org/download](https://laragon.org/download/).
2. Unduh **Laragon Full** (yang berisi Apache + Nginx + MySQL + PostgreSQL +
   Redis + dll), bukan edisi "Portable" atau ".lrg" jika belum yakin.
3. Jalankan installer.
4. Pilih folder tujuan: **`D:\laragon`**.
   - Simpan di drive **D** atau non-C — lebih aman dari UAC & tetap ikut
     tersimpan saat reinstall Windows.
5. Pilih komponen **Apache** (web server utama tutorial ini).
6. **Install** → buka Laragon.

**Verifikasi:**
- Muncul menu bar Laragon di atas (ikon bernama `Laragon`).
- Folder `D:\laragon\bin\apache\` dan `D:\laragon\bin\php\` terisi.

> Laragon **portable** — bukan service Windows. Apache/MySQL hanya hidup saat
> kamu klik **Start All**, berhenti saat **Stop All**. (Auto-start diatur di
> Langkah 13.)

---

## Langkah 2 — Kenali struktur folder Laragon

Agar tidak tersesat, ini peta folder penting:

```
D:\laragon\
├── www\                  ← tempat project (setiap folder = 1 website)
│   └── Sistem-SPI\       ← project kamu
│       └── public\       ← document root (Laravel wajib diarahkan ke sini)
├── bin\
│   ├── apache\...\       ← web server Apache
│   ├── php\
│   │   ├── php-8.3.x\    ← PHP bawaan
│   │   └── php-8.4.x\    ← PHP tambahan (Langkah 3)
│   └── mysql\...\        ← MySQL
├── etc\apache2\
│   ├── mod_php.conf      ← config PHP untuk Apache
│   └── sites-enabled\    ← vhost Apache (mengarahkan domain → folder)
├── usr\profile\
│   └── default.ini       ← versi default PHP/MySQL/dll
```

---

## Langkah 3 — Cek & tambah PHP 8.4

### 3a. Cek versi PHP bawaan

Buka Laragon → menu **PHP** → lihat versi yang dicentang. Biasanya
`php-8.3.x`. Cek via CLI juga:

```bat
D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -v
```

Jika output `PHP 8.3...` → berarti belum cukup, lanjut **3b**.

### 3b. Tambah PHP 8.4 (dua cara)

**Cara A — Versi manager Laragon (paling mudah):**
1. Menu **PHP** → **Version** → cari list versi.
2. Pilih versi **8.4** terbaru → Laragon unduh & ekstrak otomatis ke `bin\php\`.
3. Setelah muncul di daftar, **centang** versi 8.4 tersebut.

**Cara B — Manual (pasti berhasil, disarankan jika cara A tidak tersedia):**

1. Unduh **PHP 8.4 Thread-Safe (TS) x64** dari
   [windows.php.net/downloads/releases](https://windows.php.net/downloads/releases/).
   Contoh file: **`php-8.4.25-Win32-vs17-x64.zip`**
   ⚠️ **Kritis:** pilih file yang **tanpa** `-nts` di nama. Apache XAMPP/Laragon
   butuh build **Thread-Safe (TS)**. File `...-nts-...` tidak menyediakan
   `php8apache2_4.dll` dan tidak akan jalan dengan Apache.

2. Ekstrak zip ke:
   ```
   D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64
   ```
   ⚠️ Nama folder wajib berpola `php-8.4.25-..` agar dikenali Laragon.

3. Cek isi folder — harus ada:
   - `php.exe` → CLI
   - `php8apache2_4.dll` → modul untuk Apache
   - `php8ts.dll` → inti (Thread-Safe runtime)
   - folder `ext\` → berisi `php_pdo_mysql.dll`, `php_curl.dll`, dll

4. Buat file `php.ini` (kalau belum ada):
   ```bat
   cd D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64
   copy php.ini-development php.ini
   ```

5. Edit `php.ini`, pastikan baris-baris ini **aktif** (hapus `;` di depannya):
   ```ini
   extension_dir = "D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\ext"
   extension=curl
   extension=fileinfo
   extension=mbstring
   extension=mysqli
   extension=openssl
   extension=pdo_mysql
   extension=zip
   ```
   > Verifikasi dulu bahwa nama file `.dll` yang dimaksud ada di `ext\`
   > (mis. `php_curl.dll`). Extension yang di `php.ini` tapi file-nya tidak ada
   > hanya menghasilkan warning saat start — tidak fatal, tapi lebih baik benar.

### 3c. Verifikasi PHP 8.4

```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe -v
```
Harus menampilkan `PHP 8.4.25 ... (cli) (ZTS ...)`. Tanda **ZTS** menunjukkan
klien Thread-Safe — sudah benar.

```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe -m
```
Pastikan ada baris `pdo_mysql`, `mbstring`, `curl`, `openssl`, `fileinfo`.

--- 

## Langkah 4 — Aktifkan PHP 8.4 sebagai versi pilihan

PHP baru yang terpasang belum tentu otomatis dipakai. Tiga tempat harus
konsisten:

### 4a. GUI Laragon
Menu **PHP** → **Version** → centang **`php-8.4.25-Win32-vs17-x64`**.

### 4b. File profil

Edit `D:\laragon\usr\profile\default.ini`:

```ini
[php]
Version=php-8.4.25-Win32-vs17-x64
```

### 4c. Config Apache PHP

Buka `D:\laragon\etc\apache2\mod_php.conf`. Setelah pilih versi di GUI,
Laragon mengisinya otomatis. Isi yang benar:

```apache
LoadFile "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64/php8ts.dll"
LoadFile "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64/libcrypto-3-x64.dll"
LoadFile "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64/libssl-3-x64.dll"
LoadModule php_module "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64/php8apache2_4.dll"
PHPIniDir "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64"
```

> ⚠️ Baris `LoadFile ...php8ts.dll` **wajib ada**. `php8ts.dll` adalah inti
> Zend engine — penentu versi PHP. Tanpa LoadFile ke folder yang benar, Windows
> akan mengambil `php8ts.dll` dari lokasi lain (mis. folder bin Apache sisa
> versi lama) dan muncul error aneh. Lihat Troubleshooting #2.

### Verifikasi syntax Apache

```bat
D:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe -d "D:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18" -t
```
Harus keluar `Syntax OK`.

---

## Langkah 5 — Cek persyaratan project

Sebelum menyalin, pastikan project `Sistem-SPI` ini lengkap:

1. **Folder `vendor` ada?** Folder ini berisi semua dependency Composer. Tanpa
   itu aplikasi langsung error `autoload.php not found`.
   ```
   D:\File Penting\...\Sistem-SPI\vendor\   ← harus ADA
   ```
2. **File `.env` ada?** Jika tidak, salin dari `.env.example`.
3. **Versi HTTP&DB project:** pastikan project butuh MySQL & bisa jalan dengan
   PHP 8.4 (cek `composer.json` → `"php": "^8.3"` atau `^8.4` → 8.4 kelipatan
   itu).

---

## Langkah 6 — Salin project ke www

1. Salin **seluruh** folder project ke:
   ```
   D:\laragon\www\Sistem-SPI
   ```
   ⚠️ Folder `vendor` wajib ikut ter-salin. Pakai copy/cut biasa di Explorer —
   jangan pakai shortcut.

2. Setelah selesai, cek strukturnya:
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
   ├── vendor\
   └── artisan
   ```

> **Vhost otomatis Laragon:** setiap folder di `www\` otomatis jadi satu website
> dengan nama `[namafolder].test`. Untuk project ini domainnya:
> **`http://Sistem-SPI.test`**, dan `DocumentRoot` otomatis diarahkan ke
> `www\Sistem-SPI\public`. File vhost dibuat di:
> `D:\laragon\etc\apache2\sites-enabled\auto.Sistem-SPI.test.conf`.
>
> Laragon juga otomatis menambah `hosts`:
> ```
> 127.0.0.1 Sistem-SPI.test
> ```

---

## Langkah 7 — Siapkan database

**Apa:** Membuat database kosong untuk project.

**Cara:**
1. Laragon → **Start All** → Apache & MySQL hidup (indikator hijau).
2. Buka MySQL — salah satu dari:
   - Menu **Laragon** → **Quick Start** → **MySQL CLI**
   - Menu **Tools** → **HeidiSQL** (sudah termasuk Laragon)
3. Jalankan:
   ```sql
   CREATE DATABASE db_spi_pindad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   > Nama `db_spi_pindad` **harus sama** dengan `DB_DATABASE` di `.env`
   > (Langkah 8).

**Verifikasi:**
```sql
SHOW DATABASES;
```
Harus ada `db_spi_pindad` di daftar.

---

## Langkah 8 — Atur `.env`

Edit `D:\laragon\www\Sistem-SPI\.env`:

```env
APP_NAME=Sistem SPI
APP_ENV=local
APP_KEY=base64:...          ← JANGAN dihapus/diubah
APP_DEBUG=true
APP_URL=http://Sistem-SPI.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_spi_pindad
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Catatan penting:
- `APP_KEY` — wajib ada & unik per project. Jika kosong, generate:
  ```bat
  D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" key:generate
  ```
- `APP_URL` — saat project diakses sendiri di PC server pakai
  `http://Sistem-SPI.test`. Untuk akses dari perangkat lain ganti ke
  `http://<IP-server>` (Langkah 11).

**Verifikasi config:**
```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" config:clear
```

---

## Langkah 9 — Jalankan migration & seeder

**Apa:** Membuat tabel-tabel + data awal (akun user, dst).

```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" migrate --force
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" db:seed
```

**Verifikasi:**
```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" migrate:status
```
Harusnya semua migration `Ran`. Cek tabel via HeidiSQL/MySQL CLI:
```sql
USE db_spi_pindad;
SHOW TABLES;
```
Harus muncul `users`, `sessions`, `cache`, dan tabel-tabel project.

> Data user admin awal bisa dilihat di isi file seeder
> (`database\seeders\`). Simpan kredensial itu untuk login (Langkah 10).

---

## Langkah 10 — Uji di PC server

1. Laragon **Stop All** → **Start All** (agar semua config baru terbaca).
2. Buka browser: **`http://Sistem-SPI.test/`**
3. Harus muncul halaman login (judul "Masuk - SPI PT Pindad Enjiniring Indonesia").

**Verifikasi versi PHP yang benar-benar dipakai** (penting!):

1. Buat file sementara:
   ```bat
   echo ^<?php echo PHP_VERSION . "|" . php_sapi_name(); ^> D:\laragon\www\Sistem-SPI\public\phpinfo-test.php
   ```
2. Buka `http://Sistem-SPI.test/phpinfo-test.php` → harusnya:
   ```
   8.4.25|apache2handler
   ```
3. **Hapus file itu** setelah selesai:
   ```bat
   del D:\laragon\www\Sistem-SPI\public\phpinfo-test.php
   ```

**Verifikasi login:**
Masuk dengan kredensial user dari seeder. Jika muncul 419 CSRF → buka
**incognito** dulu (cookie lama). (Troubleshooting #5)

---

## Langkah 11 — Akses dari perangkat lain (IP)

Nama `.test` hanya dikenali PC server (lewat `hosts`). Perangkat lain (HP/PC
lain) tidak tahu `.test`, jadi akses via **IP** dengan pola
`http://<IP>/namafolder`:

### 11a. Pastikan Apache jalan

Cek dari server (di browser atau curl):
```bat
ipconfig
```
Catat alamat IPv4 (mis. `192.168.250.150`).

### 11b. Firewall Windows

Buka **Windows Defender Firewall** → **Advanced settings** → **Inbound Rules**
→ **New Rule...**:
1. Jenis: **Port** → Protocol: **TCP** → **Specific local ports: 80**
2. Action: **Allow the connection**
3. Profile: centang semua (Domain / Private / Public)
4. Nama: `Apache HTTP (80)` → **Finish**

> Sering kali Windows sudah otomatis meminta izin saat Apache pertama jalan
> (dialog "Allow access"), jadi boleh langsung dicek dulu.

### 11c. Redirect `/namafolder` → folder `public` (anti-listing)

Tanpa ini, `http://IP/Sistem-SPI` akan menampilkan **isi folder project**
termasuk `.env` (bocoran berbahaya).

Edit **`D:\laragon\etc\apache2\sites-enabled\00-default.conf`**:

```apache
<VirtualHost _default_:80>
    DocumentRoot "D:/laragon/www"

    RedirectMatch ^/(Sistem-SPI)(?!/public)(/.*)?$ /Sistem-SPI/public$2
    RedirectMatch ^/(IT-support)(?!/public)(/.*)?$ /IT-support/public$2

    <Directory "D:/laragon/www">
        AllowOverride All
        Options -Indexes
        Require all granted
    </Directory>
</VirtualHost>
```

Penjelasan:
- `RedirectMatch ^/(Sistem-SPI)(?!/public)(/.*)?$ /Sistem-SPI/public$2`
  → `http://IP/Sistem-SPI` dialihkan ke `http://IP/Sistem-SPI/public`
  → halaman login.
- `(?!/public)` = negative lookahead → mencegah URL yang sudah mengandung
  `/public` ter-redirect lagi (mencegah infinite loop `public/public/...`;
  lihat Troubleshooting #4).
- `Options -Indexes` → mematikan opsi daftar isi folder → kalau ada path yang
  salah, hanya tampil 403, bukan listing `.env`.
- Baris `IT-support` adalah contoh project kedua — salin sesuai project milikmu.

Setelah itu **restart Apache** (Stop All → Start All).

### 11d. Uji dari perangkat lain

Pada HP/PC lain (jaringan yang sama), buka:
- `http://192.168.250.150/Sistem-SPI` → aplikasi Sistem-SPI
- `http://192.168.250.150/IT-support` → aplikasi IT-support (jika ada)

> **Opsional — URL rapi `.test` di perangkat lain:** tambahkan 1 baris di
> `C:\Windows\System32\drivers\etc\hosts` **setiap perangkat klien**:
> ```
> 192.168.250.150 Sistem-SPI.test
> ```
> Ini tetap membutuhkan edit `hosts` per-PC — batasan semua metode nama domain
> lokal. Tanpa itu `.test` hanya jalan di PC server.

---

## Langkah 12 — IP statis

**Apa:** Agar IP server tidak berubah-ubah tiap konek (mis. `.192`, lalu `.150`).

**Cara:**
1. `Win+R` → ketik `ncpa.cpl` → Enter.
2. Klik kanan adapter Wi-Fi (atau Ethernet) → **Properties**.
3. Pilih **Internet Protocol Version 4 (TCP/IPv4)** → **Properties**.
4. Pilih **Use the following IP address**:
   - IP address: `192.168.250.150`
   - Subnet mask: `255.255.255.0`
   - Default gateway: lihat dari `ipconfig` (contoh `192.168.250.1`)
5. Bagian DNS, pilih **Use the following DNS server addresses**:
   - Preferred: `192.168.250.1`
   - Alternate: `8.8.8.8`
6. **OK** → **OK** (wifi putus-nyambung sesaat — normal).

**Verifikasi:**
```bat
ipconfig
```
IPv4 harus tetap `192.168.250.150`. Buka browser `http://192.168.250.150/Sistem-SPI`.

**Jika statis ditolak / konflik:**
- Angka `.150` sudah dipakai atau ditolak router → pilih angka lain yang kosong
  di subnet yang sama, mis. `192.168.250.200`.
- Kalau di kantor dan router mengunci DHCP → minta IT buat **DHCP reservation**
  (MAC Wi-Fi laptop → pin ke IP).

**Sinkronkan IP final ke semua tempat:**
- `.env` kedua project → `APP_URL=http://192.168.250.150`
- `www\index.php` (jika ada) → angka IP
- Contoh angka di tutorial & `hosts` PC klien

---

## Langkah 13 — Auto-start saat PC dinyalakan

Laragon bisa hidup otomatis begitu Windows start:

1. Menu **Tools** (ikon kunci pas) → **Preferences**.
2. Centang **"Run Laragon when Windows starts"**.
3. Centang **"Start All automatically"**.
4. Verifikasi di `D:\laragon\usr\laragon.ini` — baris berikut bernilai `-1`:
   ```ini
   RunAtStartup=-1
   AutoStart=-1
   AutoVirtualHosts=-1
   ```
5. Restart Windows → tunggu ±10 detik → coba dari HP/PC lain:
   `http://192.168.250.150/Sistem-SPI`

> ⚠️ **Penting** untuk akses via IP: **Start All** membuka Apache **dan**
> MySQL. Kalau MySQL tidak start (misal hanya Apache), error login jadi
> `Connection refused: 3306`. Selalu pastikan dua-duanya hijau.

---

## Troubleshooting

### 1. `Composer ... require a PHP version ">= 8.4.1". You are running 8.3.x`
**Penyebab:** Versi PHP yang dipakai Apache masih 8.3.
**Cek:** file `phpinfo-test` (Langkah 10) — kalau masih `8.3...` berarti konfig
tak tercapai.
**Fix:** Ulangi Langkah 3-4; pastikan ketiganya konsisten: centang GUI, nilai di
`default.ini`, dan `mod_php.conf`. Lalu **Stop All → Start All** (jangan hanya
restart Apache dari menu).

### 2. PHP sudah 8.4 di config tapi tetap versi lama + error DLL
Gejala:
```
The procedure entry point zend_register_internal_class_with_flags could not
be located in ...\php-8.4.x\ext\php_curl.dll
```
atau `phpinfo-test` tetap versi lama.

**Akar masalah:** ada `php8ts.dll` (inti PHP) versi lama di folder **bin
Apache** yang di-load duluan. Windows mengutamakan DLL dari folder executable
(`bin\apache\<versi>\bin\`) ketimbang dari folder PHP.

**Cek siapa pencurinya:**
```powershell
Get-Process httpd | ForEach-Object { $_.Modules } |
  Where-Object { $_.ModuleName -match "^php" } | Select FileName
```
Jika muncul baris `...\bin\apache\...\bin\php8ts.dll` → itu biang keroknya.

**Fix:**
1. Stop All.
2. Rename file `php*.dll` versi lama di folder bin Apache:
   ```bat
   cd D:\laragon\bin\apache\httpd-2.4.66-...\bin
   ren php8ts.dll php8ts.bak.dll
   ren php8apache2_4.dll php8apache2_4.bak.dll
   ren php8phpdbg.dll php8phpdbg.bak.dll
   ```
3. Pastikan `mod_php.conf` memuat `LoadFile ...\php8ts.dll` dari folder PHP 8.4.
4. Start All → tes lagi `phpinfo-test` → harus `8.4.25|apache2handler`.

### 3. Halaman "Index of /sistem-spi" (isi folder) muncul saat lewat IP
**Penyebab:** `http://IP/Sistem-SPI` diarahkan Apache ke folder project asli
(Windows tidak case-sensitive), sehingga menampilkan daftar file termasuk `.env`.
**Fix:** pasang `RedirectMatch` di `00-default.conf` + `Options -Indexes`
(Langkah 11c). Setelah itu path dialihkan ke `/public`.

### 4. Redirect berulang `/public/public/public/...`
**Penyebab:** `RedirectMatch` tanpa `(?!/public)` ikut menangkap URL yang sudah
berisi `/public`, lalu menambahkan `/public` terus.
**Fix:** pakai negative lookahead `(?!/public)` seperti contoh di Langkah 11c,
lalu hapus browser cache (atau buka incognito) sebelum tes ulang.

### 5. Halaman login muncul tapi login gagal / 419 Page Expired
**Penyebab:** cookie/session dari halaman lama (biasanya setelah diganti
`APP_URL` atau server direstart).
**Fix:** buka **incognito** (Ctrl+Shift+N), atau bersihkan cookie situs, lalu
muat ulang.

### 6. Perangkat lain tidak bisa membuka aplikasi
Urutan cek:
1. **Jaringan sama?** `ping 192.168.250.150` dari perangkat lain. Timeout =
   perangkat terisolasi (AP/client isolation di hotspot/router).
2. **Firewall?** Pastikan port 80 izinkan masuk (Langkah 11b).
3. **URL benar?** `http://IP/Sistem-SPI` — bukan `http://IP/Sistem-SPI.test`
   (`.test` tidak dikenal perangkat lain).
4. **IP stabil?** kalau sering tidak kebuka tiba-tiba, set IP statis
   (Langkah 12).
5. **MySQL start?** error login `Connection refused :3306` = MySQL tidak jalan.
   Klik Start All di Laragon, pastikan dua-duanya hijau.

### 7. `SQLSTATE[HY000] [2002] No connection ... target machine actively refused`
**Penyebab:** MySQL tidak berjalan.
**Fix:** Laragon → **Start All** (Start Apache + MySQL). Cek port:
```bat
netstat -ano | findstr :3306
```
Harus ada baris `LISTENING`.

---

## Ringkasan folder penting

| Peran | Lokasi |
|---|---|
| Project (document root otomatis) | `D:\laragon\www\Sistem-SPI\public` |
| PHP 8.4 | `D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64` |
| Config PHP di Apache | `D:\laragon\etc\apache2\mod_php.conf` |
| Vhost auto per project (`.test`) | `D:\laragon\etc\apache2\sites-enabled\auto.*.conf` |
| Vhost akses via IP (redirect + anti-listing) | `D:\laragon\etc\apache2\sites-enabled\00-default.conf` |
| Profil versi (PHP/MySQL/dll) | `D:\laragon\usr\profile\default.ini` |
| Hostname lokal (`.test`) | `C:\Windows\System32\drivers\etc\hosts` |
| Database MySQL | `D:\laragon\bin\mysql\mysql-8.4.x-winx64\bin\` |

---

## Perintah cepat

```bat
:: Versi PHP
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe -v

:: Syntax Apache
D:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe -d "D:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18" -t

:: Migrate & seed
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" migrate --force
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" db:seed

:: Bersihkan cache
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" config:clear
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" cache:clear
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" route:clear

:: Forensik versi PHP yang dipakai Apache
Get-Process httpd | ForEach-Object { $_.Modules } |
  Where-Object { $_.ModuleName -match "^php" } | Select FileName

:: Cek port
netstat -ano | findstr :80
netstat -ano | findstr :3306
```