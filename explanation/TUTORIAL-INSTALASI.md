# Tutorial Instalasi Sistem SPI untuk PC Baru

Panduan lengkap menginstall project **Sistem SPI** di PC lain menggunakan XAMPP
(Windows), lengkap dengan cara mengatasi error yang sudah pernah ditemukan.

> Semua path di tutorial ini contoh: `D:\xampp\...`. Sesuaikan huruf drive jika
> XAMPP terpasang di drive lain.

---

## Persyaratan

| Kebutuhan | Nilai                                                                        |
|-----------|------------------------------------------------------------------------------|
| XAMPP     | Versi apa saja (Apache 2.4 + MariaDB)                                        |
| PHP       | **harus ≥ 8.4.1** (XAMPP bawaan biasanya 8.2 → tidak cukup, lihat langkah 3) |
| Project   | Folder `Sistem-SPI` lengkap termasuk folder `vendor`                         |

---

## Langkah 1 — Salin project ke htdocs

1. Salin seluruh folder project `Sistem-SPI` ke `D:\xampp\htdocs\Sistem-SPI`.
2. Pastikan folder `vendor` ikut tersalin. Ini penting — tanpa `vendor` Laravel
   tidak bisa jalan (perlu `composer install`, dan PC baru belum tentu punya Composer).

---

## Langkah 2 — Pastikan konfigurasi database

Buka `D:\xampp\htdocs\Sistem-SPI\.env` dan sesuaikan:

```ini
APP_URL=http://<IP-LAN-PC-INi>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spi_pindad
DB_USERNAME=root
DB_PASSWORD=
```

Catatan:
- `APP_URL` harus berisi IP LAN PC server (cari lewat `ipconfig` di CMD).
  Nilai ini dipakai untuk membuat URL di halaman (form action, redirect).
- Database `spi_pindad` **harus dibuat** (lihat Langkah 6).
- `APP_KEY` jangan dihapus. Kalau tidak ada/berubah, jalankan:
  `php artisan key:generate` (perlu PHP 8.4, lihat Langkah 4).

---

## Langkah 3 — Cek versi PHP di XAMPP

Buka CMD lalu cek versi PHP:

```bat
D:\xampp\php\php.exe -v
```

- Output ≥ 8.4 → lanjut ke Langkah 5.
- Output 8.2 / 8.1 / 8.3 → **dibutuhkan PHP 8.4** (project ini wajib ≥ 8.4.1).
  Lanjut ke Langkah 4.

---

## Langkah 4 — Pasang PHP 8.4 di XAMPP (opsional tapi penting)

PHP ini dibuat berdampingan dengan PHP bawaan XAMPP, tidak menghapus yang lama.

1. **Unduh** PHP 8.4 Thread-Safe (TS) Windows x64:
   `https://windows.php.net/downloads/releases/php-8.4.25-Win32-vs17-x64.zip`
   - Pilih file **tanpa** kata `-nts` (mis. `php-8.4.25-Win32-vs17-x64.zip`)
     karena Apache XAMPP butuh build Thread-Safe.

2. **Ekstrak** zip tersebut ke `D:\xampp\php84`.

3. **Salin** file `php.ini` dari XAMPP lama agar pengaturan ekstensi ikut:
   ```bat
   copy D:\xampp\php\php.ini D:\xampp\php84\php.ini
   ```

4. **Perbaiki path `extension_dir`** di `D:\xampp\php84\php.ini` menjadi:
   ```ini
   extension_dir="D:\xampp\php84\ext"
   ```

5. **Cek** PHP 8.4 jalan dan ekstensi penting aktif:
   ```bat
   D:\xampp\php84\php.exe -v
   D:\xampp\php84\php.exe -m
   ```
   Pastikan ada: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`.

---

## Langkah 5 — Arahkan Apache ke PHP 8.4

Edit `D:\xampp\apache\conf\extra\httpd-xampp.conf`.

**Penting:** XAMPP biasanya sudah men-comment load module PHP. Cek baris
`LoadModule php_module ...` — kalau diawali `#`, hapus tanda `#`-nya.

Ubah path dari PHP lama ke PHP 8.4 (3 baris di bagian atas + `PHPINIDir`):

```apache
LoadFile "D:/xampp/php84/php8ts.dll"
LoadFile "D:/xampp/php84/libpq.dll"
LoadFile "D:/xampp/php84/libsqlite3.dll"
LoadModule php_module "D:/xampp/php84/php8apache2_4.dll"

<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>
<FilesMatch "\.phps$">
    SetHandler application/x-httpd-php-source
</FilesMatch>

<IfModule php_module>
    PHPINIDir "D:/xampp/php84"
</IfModule>
```

**Verifikasi config** sebelum restart (jangan lewat, mencegah Apache gagal start):

```bat
D:\xampp\apache\bin\httpd.exe -t
```

Harus keluar `Syntax OK`. Untuk memastikan module PHP termuat:

```bat
D:\xampp\apache\bin\httpd.exe -t -D DUMP_MODULES
```

Harus ada baris `php_module (shared)`.

---

## Langkah 6 — Buat database & jalankan migration

1. Nyalakan Apache + MySQL (**Run as Administrator** di XAMPP Control Panel).
2. Buka shell MySQL dan buat database:
   ```bat
   D:\xampp\mysql\bin\mysql.exe -uroot
   ```
   ```sql
   CREATE DATABASE spi_pindad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```
3. Jalankan migration (harus pakai PHP 8.4):
   ```bat
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan migrate --force
   ```
4. Buat akun user awal lewat seeder (jika project punya seeder User):
   ```bat
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan db:seed
   ```
   > Sesuaikan dengan seeder yang ada di `database/seeders`. Email & password
   > pengguna awal bisa dilihat dari isi seeder.

---

## Langkah 7 — VirtualHost (agar akses langsung ke folder public/)

Edit `D:\xampp\apache\conf\extra\httpd-vhosts.conf`, tambahkan:

```apache
<VirtualHost *:80>
    DocumentRoot "D:/xampp/htdocs/Sistem-SPI/public"
    ServerName sistem-spi.pei

    <Directory "D:/xampp/htdocs/Sistem-SPI/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

> `ServerName` bebas (mis. `sistem-spi.pei`). Karena vhost ini pertama,
> semua request ke port 80 (termasuk via IP) masuk ke project.

Jalankan `httpd.exe -t` lagi untuk memastikan tetap `Syntax OK`.

---

## Langkah 8 — Restart Apache

**Wajib stop total** (bukan sekadar restart), karena perubahan `LoadModule`
hanya berlaku setelah proses Apache dimulai ulang penuh:

1. Tutup XAMPP Control Panel.
2. **Klik kanan → Run as administrator** pada XAMPP Control Panel.
3. Klik **Stop** lalu **Start** pada Apache.
4. Buka `http://localhost/` → harusnya tampil halaman login Sistem SPI.

---

## Langkah 9 — Siapkan akses dari PC lain (LAN)

1. Cari IP laptop server:
   ```bat
   ipconfig
   ```
   Catat alamat IPv4 (contoh: `10.186.152.1`).
2. Pastikan `APP_URL` di `.env` berisi IP tersebut, lalu bersihkan cache config:
   ```bat
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan config:clear
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan cache:clear
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan route:clear
   ```
3. **Buka Windows Firewall** → Advanced settings → Inbound Rules → New Rule:
   - Rule Type: **Port**, Protocol: **TCP**, Port: **80**
   - Action: **Allow the connection**, Profile: semua
4. Dari PC lain buka browser: `http://<IP-laptop-server>/`

---

## Langkah 10 — Auto-start saat PC dinyalakan (opsional)

Jika Apache & MySQL sudah menjadi service Windows (indikator "Service" aktif),
mereka sudah otomatis jalan saat boot:

```powershell
Get-Service Apache2.4, mysql | Select Name, Status, StartType
```

- `StartType` harus `Automatic`.
- Kalau bukan service, alternatifnya: **Task Scheduler** → Create Task →
  Trigger "At log on" → Action mulai `D:\xampp\xampp-control.exe`.

---

## Troubleshooting — Error yang pernah terjadi

### 1. Kode PHP tampil mentah (tidak dieksekusi)
```
<?php ... handleRequest(Request::capture());
```
**Penyebab:** Module PHP tidak aktif di Apache.
**Cek:** `LoadModule php_module` di `httpd-xampp.conf` masih diawali `#`.
**Solusi:** Hapus `#`, hidupkan blok `LoadFile` + `<FilesMatch>` + `PHPINIDir`,
seperti Langkah 5. Lalu **restart penuh** Apache.

### 2. Composer: PHP version requirement
```
Fatal error: ... Composer dependencies require a PHP version ">= 8.4.1".
You are running 8.2.12. in ...\vendor\composer\platform_check.php:22
```
**Penyebab:** Project butuh PHP ≥ 8.4.1, XAMPP masih 8.2.
**Solusi:** Pasang PHP 8.4 (Langkah 4) dan arahkan Apache (Langkah 5).
**Perhatian:** Setelah mengganti versi PHP, Apache **harus di-restart penuh**
sebagai admin — `httpd.exe -k restart` saja tidak cukup.

### 3. Apache tetap pakai versi PHP lama setelah diganti
Gejala: error di atas masih muncul padahal config sudah diubah.
**Penyebab:** Proses Apache lama masih berjalan; `LoadModule` hanya dibaca
saat proses start.
**Solusi:** Stop total dari XAMPP Control Panel **as Administrator** lalu Start.
`taskkill`/`Restart-Service` tidak jalan tanpa hak admin.

### 4. 404 Not Found Laravel saat akses `<IP>/Sistem-SPI`
**Penyebab:** URL menyertakan nama folder, padahal vhost sudah arahkan
DocumentRoot langsung ke `public/`. Route yang ada tidak mengenali
`/Sistem-SPI`.
**Solusi:** Akses root: `http://<IP>/` (bukan `/Sistem-SPI`).

### 5. HTTP 500 setelah PHP 8.4 aktif
Cek file log error aplikasi:
```bat
type D:\xampp\htdocs\Sistem-SPI\storage\logs\laravel.log
```
Diagnosa umum:
- `SQLSTATE[42S02] ... 'sessions' doesn't exist` → migration belum dijalankan
  (Langkah 6).
- `Access denied for user` → cek kredensial di `.env`.

### 6. 419 Page Expired saat login
**Penyebab:** Browser mengirim token CSRF dari halaman yang dimuat sebelum
server di-restart/ganti PHP → token basi.
**Solusi:** Bersihkan cache & cookie browser (atau pakai mode incognito),
buka `http://<IP>/`, login ulang. Tanpa action ini, cek bahwa session cookie
`laravel-session` tersimpan (tab developer tools → Application → Cookies).

### 7. PC lain tidak bisa membuka aplikasi
**Cek urutan:**
1. Dari PC lain: `ping <IP-server>` → kalau timeout, kemungkinan **AP/client
   isolation** di hotspot/router (mis. hotspot HP). Matikan fitur "client
   isolation"
2. Firewall Windows di server (Langkah 9).
3. Server dan PC lain harus benar-benar satu jaringan sama.

---

## Ringkasan perintah yang berguna

```bat
D:\xampp\apache\bin\httpd.exe -t                                    & REM cek config
D:\xampp\apache\bin\httpd.exe -t -D DUMP_MODULES                    & REM cek module PHP
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan migrate --force
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan config:clear
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan cache:clear
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan route:clear
```