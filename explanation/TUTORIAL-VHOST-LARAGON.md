# Tutorial Instalasi Laravel di Laragon — PC Fresh

Panduan lengkap dari **PC baru tanpa aplikasi apa pun** sampai project Laravel
(Laravel 13) berjalan di Laragon dan bisa diakses lewat nama domain `.test`
maupun lewat IP dari perangkat lain.

> Lingkungan contoh: Windows, instalasi di `D:\laragon`. Sesuaikan huruf drive
> bila berbeda.

---

## Persyaratan

- Windows 10/11
- Koneksi internet (untuk unduh installer & PHP 8.4)
- Project `Sistem-SPI` lengkap (termasuk folder `vendor`) — atau project Laravel
  lain dengan dokumentasinya

---

## Langkah 1 — Pasang Laragon

1. Unduh **Laragon Full** dari [laragon.org](https://laragon.org/download/).
   Pilih versi Full (sudah termasuk Apache, Nginx, MySQL, dll).
2. Jalankan installer → pilih folder tujuan **`D:\laragon`** → Install.
3. Buka Laragon → muncul menu bar di atas.

> Laragon sifatnya **portable** — tidak mendaftarkan service Windows terus
> menerus; Apache/MySQL jalan saat Laragon "Start All", berhenti saat "Stop All".

---

## Langkah 2 — Cek versi PHP bawaan

Laragon baru biasanya membawa PHP 8.3 atau lebih lama. Project ini butuh
PHP **≥ 8.4**. Cek:

1. Menu **PHP** → lihat versi yang dicentang (mis. `php-8.3.x`).
2. Kalau < 8.4 → lanjut Langkah 3 untuk menambah PHP 8.4.

---

## Langkah 3 — Tambah PHP 8.4 ke Laragon

### Cara A — Pakai versi manager Laragon (disarankan)

1. Menu **PHP** → **Version** → pilih versi 8.4 terbaru di daftar (kalau ada),
   Laragon akan men-download & mengekstrak sendiri.
2. Setelah selesai, centang versi 8.4 tersebut di menu yang sama.

### Cara B — Manual (pasti berhasil)

1. Unduh **PHP 8.4 Thread-Safe (TS) for Windows x64** dari
   [windows.php.net/downloads/releases](https://windows.php.net/downloads/releases/).
   Contoh: `php-8.4.25-Win32-vs17-x64.zip`.
   **Wajib pilih build tanpa `-nts`** (Laragon/Apache butuh Thread-Safe).
2. Ekstrak zip ke:
   ```
   D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64
   ```
   Nama folder harus berpola `php-8.4.25-...` supaya dikenali Laragon.
3. Buat file `php.ini` di folder itu (opsional, untuk ekstensi yang benar):
   ```bat
   cd D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64
   copy php.ini-development php.ini
   ```
4. Edit `php.ini`, pastikan baris ini ada/aktif (hapus `;`):
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
   (pastikan file `php_curl.dll`, `php_pdo_mysql.dll` dll ada di folder `ext`)
5. Verifikasi CLI:
   ```bat
   D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe -v
   ```

### Aktifkan PHP 8.4 sebagai versi pilihan

Dua hal yang harus konsisten:

1. **GUI Laragon:** menu **PHP** → **Version** → centang `php-8.4.25-...`.
2. **File profil** `D:\laragon\usr\profile\default.ini`, bagian `[php]`:
   ```ini
   [php]
   Version=php-8.4.25-Win32-vs17-x64
   ```
3. Cek config PHP Apache `D:\laragon\etc\apache2\mod_php.conf` — setelah
   memilih versi di GUI, Laragon mengisi otomatis. Harus menunjuk:
   ```apache
   LoadFile "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64/php8ts.dll"
   LoadModule php_module "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64/php8apache2_4.dll"
   PHPIniDir "D:/laragon/bin/php/php-8.4.25-Win32-vs17-x64"
   ```
   > Baris `LoadFile php8ts.dll` memberi tahu Apache lokasi inti PHP (Zend).
   > Tanpa ini, Windows bisa load `php8ts.dll` dari lokasi lain dan versi PHP
   > bisa jadi salah (lihat Troubleshooting #2).

---

## Langkah 4 — Salin project ke www

1. Salin folder project ke `D:\laragon\www\Sistem-SPI` (folder `vendor` wajib
   ikut).
2. Pastikan file `.env` ada (kalau belum, salin dari `.env.example`).

> Laragon **otomatis membuat virtual host** untuk tiap folder di `www\`,
> berbentuk `namafolder.test`. Untuk project ini otomatis: **`Sistem-SPI.test`**,
> dengan `DocumentRoot` diarahkan ke `www\Sistem-SPI\public`.
> File vhost dibuat di `D:\laragon\etc\apache2\sites-enabled\auto.Sistem-SPI.test.conf`.

Laragon juga otomatis menambahkan ke file `hosts` Windows:
```
127.0.0.1 Sistem-SPI.test
```

---

## Langkah 5 — Siapkan database

1. Laragon **Start All** → Apache & MySQL berjalan (indikator hijau).
2. Buka MySQL (menu **Laragon** → **Quick Start** → **MySQL CLI**), atau pakai
   HeidiSQL yang sudah termasuk Laragon (menu Tools → HeidiSQL).
3. Buat database (sesuai `DB_DATABASE` di `.env` project):
   ```sql
   CREATE DATABASE db_spi_pindad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Jalankan migration + seeder (pakai PHP 8.4):
   ```bat
   D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" migrate --force
   D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" db:seed
   ```

---

## Langkah 6 — Edit `.env`

`D:\laragon\www\Sistem-SPI\.env`:

```env
APP_NAME=Sistem SPI
APP_URL=http://Sistem-SPI.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_spi_pindad
DB_USERNAME=root
DB_PASSWORD=
```

Bersihkan cache Laravel:

```bat
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" config:clear
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" cache:clear
D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64\php.exe "D:\laragon\www\Sistem-SPI\artisan" route:clear
```

---

## Langkah 7 — Uji di PC server

1. Laragon **Start All** (atau cukup Stop/Start Apache dari menu Laragon).
2. Browser: `http://Sistem-SPI.test/` → harusnya halaman login.
3. Verifikasi versi PHP yang benar-benar dipakai — buat file sementara:
   ```bat
   echo ^<?php echo PHP_VERSION . "|" . php_sapi_name(); ^> D:\laragon\www\Sistem-SPI\public\phpinfo-test.php
   ```
   Buka `http://Sistem-SPI.test/phpinfo-test.php` → harus
   **`8.4.25|apache2handler`**. Hapus file itu setelahnya.

---

## Langkah 8 — Akses dari perangkat lain (LAN / HP)

Nama `.test` hanya dikenali PC server (lewathosts). Perangkat lain tidak kenal
`.test`, jadi akses lewat **IP**:

1. Cek IP server: `ipconfig` (contoh `192.168.250.192`).
2. Di `.env` ubah `APP_URL=http://192.168.250.192` → jalankan lagi config:clear.
3. Karena diakses lewat IP, request jatuh ke folder `www` (produksi) — bukan ke
   project. Supaya `http://<IP>/` langsung ke project, buat redirect:
   **`D:\laragon\www\index.php`**:
   ```php
   <?php
   header('Location: /Sistem-SPI/public/', true, 302);
   exit;
   ```
4. Perangkat lain buka: `http://192.168.250.192/`
5. Pastikan **Windows Firewall** mengizinkan Apache:
   Inbound Rules → New Rule → Port → TCP `80` → Allow.
   (Biasanya sudah otomatis diminta saat Apache pertama kali jalan.)

> Kalau mau URL rapi `http://Sistem-SPI.test:80` dari perangkat lain: tambahkan
> 1 baris ke file `hosts` **tiap perangkat klien**:
> `192.168.250.192 Sistem-SPI.test`. Ini batasan semua metode nama domain lokal.

---

## Langkah 9 — Auto-start saat PC dinyalakan

Laragon bisa jalan otomatis saat Windows start:

1. Menu **Tools** (ikon kunci pas) → **Preferences**.
2. Centang **"Run Laragon when Windows starts"**.
3. Centang **"Start All automatically"**.
4. Check file `D:\laragon\usr\laragon.ini` — baris ini harus `-1`:
   ```ini
   RunAtStartup=-1
   AutoStart=-1
   AutoVirtualHosts=-1
   ```
5. Restart Windows → Laragon (Apache + MySQL) menyala sendiri → aplikasi siap.

---

## Troubleshooting

### 1. `Composer ... require a PHP version ">= 8.4.1". You are running 8.x`
Versi PHP aktif masih < 8.4. Ulangi Langkah 3, pastikan GUI Laragon memilih
versi 8.4 dan profil `default.ini` sudah cocok, lalu Stop All → Start All.

### 2. Versi PHP sudah 8.4 di config tapi aplikasi tetap versi lama / error DLL
Gejala:
```
PHP Fatal error: ... in platform_check.php
The procedure entry point zend_register_internal_class_with_flags could not
be located in ...\php-8.4.x\ext\php_curl.dll
```
Akar masalah: ada `php8ts.dll` (inti PHP) versi lama dari folder lain yang
di-load lebih dulu. **Windows memprioritaskan DLL di folder executable Apache**
(`D:\laragon\bin\apache\<versi>\bin\`).

Cek siapa pencuri versinya:
```powershell
Get-Process httpd | ForEach-Object { $_.Modules } |
  Where-Object { $_.ModuleName -match "^php" } | Select FileName
```
Jika muncul baris `...\bin\php8ts.dll` dari folder bin Apache (bukan dari
`bin\php\php-8.4...\`):

1. Stop All.
2. Rename DLL lama di folder bin Apache:
   ```bat
   cd D:\laragon\bin\apache\httpd-2.4.66-...\bin
   ren php8ts.dll php8ts.bak.dll
   ren php8apache2_4.dll php8apache2_4.bak.dll
   ren php8phpdbg.dll php8phpdbg.bak.dll
   ```
3. Pastikan `mod_php.conf` memuat `LoadFile ...\php8ts.dll` dari folder PHP 8.4.
4. Start All → tes ulang `phpinfo-test`.

### 3. Halaman default Laragon muncul (bukan aplikasi) saat lewat IP
Request ke `http://<IP>/` diarahkan ke root `www`. Set file `www\index.php`
redirect (Langkah 8 poin 3), atau buka langsung `http://<IP>/Sistem-SPI/public/`.

### 4. Halaman login muncul tapi login gagal / CSRF 419
Browser menyimpan cookie dari sesi lama. Buka **incognito** atau bersihkan
cookie situs, lalu muat ulang halaman.

### 5. Perangkat lain tidak bisa membuka aplikasi
Urutan cek:
1. `ping <IP-server>` dari perangkat lain → timeout = jaringan (AP/client
   isolation di hotspot/router).
2. Firewall server: port 80 izinkan masuk.
3. `.env` `APP_URL` memakai IP terkini; cache sudah dibersihkan.

---

## Ringkasan folder penting

| Peran | Lokasi |
|---|---|
| Project (document root otomatis) | `D:\laragon\www\Sistem-SPI\public` |
| PHP 8.4 | `D:\laragon\bin\php\php-8.4.25-Win32-vs17-x64` |
| Config PHP di Apache | `D:\laragon\etc\apache2\mod_php.conf` |
| Vhost per project | `D:\laragon\etc\apache2\sites-enabled\auto.*.conf` |
| Profil versi (PHP/MySQL/dll) | `D:\laragon\usr\profile\default.ini` |
| Database MySQL | `D:\laragon\bin\mysql\mysql-8.4.x-winx64\bin\` |