# Tutorial Instalasi Laravel di XAMPP — PC Fresh

Panduan lengkap dari **PC baru tanpa aplikasi apa pun** sampai project Laravel
(Laravel 13, butuh PHP ≥ 8.4.1) berjalan di XAMPP dan bisa diakses lewat IP
maupun nama folder dari perangkat lain.

> Lingkungan contoh: Windows, XAMPP di `D:\xampp`. Sesuaikan huruf drive.

---

## Persyaratan

- Windows 10/11
- Koneksi internet
- Project `Sistem-SPI` lengkap (termasuk folder `vendor`)
- Hak administrator (untuk install XAMPP, sett servis, buka firewall)

---

## Langkah 1 — Pasang XAMPP

1. Unduh XAMPP dari [apachefriends.org](https://www.apachefriends.org/download.html).
   Versi PHP bawaan (biasanya 8.2/8.3) **tidak cukup** — kita akan pasang PHP
   8.4 terpisah di Langkah 3.
2. Jalankan installer → pilih **`D:\xampp`** → Install (centang semua komponen
   default: Apache, MySQL, PHP).
3. Buka XAMPP Control Panel **sebagai Administrator** (klik kanan).

---

## Langkah 2 — Jalankan & pasang servis (biar auto-start)

1. Di XAMPP Control Panel, klik **Start** pada baris **Apache** dan **MySQL**
   untuk tes pertama.
2. Matikan dulu keduanya (Stop).
3. Pasang sebagai service Windows (agar otomatis saat PC nyala):
   - klik tombol **X** (kotak di kanan) pada baris Apache → instal service.
   - lakukan sama untuk baris **MySQL**.
4. Verifikasi otomatis start saat boot:
   ```powershell
   Get-Service Apache2.4, mysql | Select Name, Status, StartType
   ```
   StartType harus `Automatic`. Kalau belum: `services.msc` → Properties →
   Startup type **Automatic**.

---

## Langkah 3 — Pasang PHP 8.4 (XAMPP bawaan tidak cukup)

XAMPP memuat PHP lewat file `D:\xampp\apache\conf\extra\httpd-xampp.conf`.
Kita pasang PHP 8.4 **berdampingan** tanpa merusak bawaan XAMPP:

1. Unduh **PHP 8.4 Thread-Safe (TS) x64** dari
   [windows.php.net/downloads/releases](https://windows.php.net/downloads/releases/).
   Contoh: `php-8.4.25-Win32-vs17-x64.zip`.
   **Wajib tanpa `-nts`** (Apache butuh Thread-Safe).
2. Ekstrak ke `D:\xampp\php84`.
3. Salin pengaturan ekstensi dari PHP bawaan:
   ```bat
   copy D:\xampp\php\php.ini D:\xampp\php84\php.ini
   ```
4. Edit `D:\xampp\php84\php.ini`, perbaiki `extension_dir`:
   ```ini
   extension_dir="D:\xampp\php84\ext"
   ```
   Pastikan ekstensi penting aktif (hapus `;` di depannya):
   `curl`, `fileinfo`, `mbstring`, `mysqli`, `openssl`, `pdo_mysql`, `zip`.
5. Verifikasi CLI:
   ```bat
   D:\xampp\php84\php.exe -v
   D:\xampp\php84\php.exe -m
   ```

---

## Langkah 4 — Arahkan Apache ke PHP 8.4

Edit **`D:\xampp\apache\conf\extra\httpd-xampp.conf`**. XAMPP sering
**menonaktifkan** PHP (semua baris diawali `#`). Aktifkan & ganti path-nya:

```apache
LoadFile "D:/xampp/php84/php8ts.dll"
LoadFile "D:/xampp/php84/libpq.dll"
LoadFile "D:/xampp/php84/libsqlite3.dll"
LoadModule php_module "D:/xampp/php84/php8apache2_4.dll"

<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>

<IfModule php_module>
    PHPIniDir "D:/xampp/php84"
</IfModule>
```

**Verifikasi config sebelum restart** (wajib):
```bat
D:\xampp\apache\bin\httpd.exe -t
```
harus `Syntax OK`. Dan cek module ter-load:
```bat
D:\xampp\apache\bin\httpd.exe -t -D DUMP_MODULES
```
harus ada `php_module (shared)`.

> **PENTING (kolom api PHP):** kalau di folder `D:\xampp\apache\bin\` ada file
> `php8ts.dll` / `php8apache2_4.dll` versi lama, Windows akan memprioritaskannya
> (DLL search order dari folder executable Apache) dan mengalahkan PHP 8.4.
> Rename dulu:
> ```bat
> cd D:\xampp\apache\bin
> ren php8ts.dll php8ts.bak.dll
> ren php8apache2_4.dll php8apache2_4.bak.dll
> ```

---

## Langkah 5 — Salin project ke htdocs

1. Salin folder project ke `D:\xampp\htdocs\Sistem-SPI` (folder `vendor` wajib
   ikut).
2. Pastikan file `.env` ada (kalau belum, salin dari `.env.example`).

---

## Langkah 6 — Siapkan database

1. Dari XAMPP Control Panel (admin): Start Apache & MySQL.
2. Buat database (via aplikasi MySQL/XAMPP atau command):
   ```bat
   D:\xampp\mysql\bin\mysql.exe -uroot -e "CREATE DATABASE spi_pindad CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
3. Migration + seeder (pakai PHP 8.4):
   ```bat
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan migrate --force
   D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan db:seed
   ```

---

## Langkah 7 — Edit `.env`

`D:\xampp\htdocs\Sistem-SPI\.env`:

```env
APP_NAME=Sistem SPI
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spi_pindad
DB_USERNAME=root
DB_PASSWORD=
```

> Gunakan `localhost:8000` di server sendiri; ganti ke `http://<IP>:8000` saat
> diakses perangkat lain (Langkah 9). `:8000` karena project ini kita letakkan
> di port khusus (lihat Langkah 8).

Bersihkan cache:
```bat
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan config:clear
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan cache:clear
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan route:clear
```

---

## Langkah 8 — VirtualHost: project di port sendiri

XAMPP default menampilkan semua di port 80. Untuk beberapa project Laravel
tanpa saling tabrak, beri **masing-masing port** lewat vhost
`D:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
Listen 8000
Listen 8080

<VirtualHost *:8000>
    DocumentRoot "D:/xampp/htdocs/Sistem-SPI/public"
    ServerName spi.pei
    <Directory "D:/xampp/htdocs/Sistem-SPI/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:8080>
    DocumentRoot "D:/xampp/htdocs/Proyek2/public"
    ServerName proyek2.pei
    <Directory "D:/xampp/htdocs/Proyek2/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

> **Mengapa bukan akses `/folder` di port 80?** Laravel dirancang berjalan di
> akar domain. Bila diakses lewat sub-path (`http://IP/Sistem-SPI`), routing
> & URL internal Laravel kacau (redirect loop `AH00124`). Port terpisah adalah
> cara paling andal. Nama folder tetap bisa dipakai lewat redirect (opsional
> di bawah).

**Opsi redirect nama folder → port** (agar `http://IP/Sistem-SPI` tetap jalan):
```apache
<VirtualHost *:80>
    DocumentRoot "D:/xampp/htdocs"
    RedirectMatch ^/Sistem-SPI/?$ http://localhost:8000/
    RedirectMatch ^/Proyek2/?$ http://localhost:8080/
</VirtualHost>
```

Setelah mengubah config:
```bat
D:\xampp\apache\bin\httpd.exe -t   & REM harus Syntax OK
```

---

## Langkah 9 — Restart & uji

1. XAMPP Control Panel (admin) → **Stop** → **Start** untuk Apache
   (restart penuh wajib setelah mengubah `LoadModule`).
2. Uji di server: `http://localhost:8000/` → halaman login.
3. Verifikasi versi PHP aktual — file sementara
   `D:\xampp\htdocs\Sistem-SPI\public\phpinfo-test.php`:
   ```php
   <?php echo PHP_VERSION . '|' . php_sapi_name();
   ```
   Buka `http://localhost:8000/phpinfo-test.php` → harus
   **`8.4.25|apache2handler`**. Hapus setelahnya.

---

## Langkah 10 — Akses dari perangkat lain (LAN)

1. Cari IP server: `ipconfig` (contoh `192.168.250.192`).
2. Di `.env` ubah `APP_URL=http://192.168.250.192:8000`, lalu config:clear.
3. **Firewall Windows** — izinkan port masuk 8000 (dan 8080 dsb untuk project
   lain), plus 80 kalau pakai redirect:
   - `wf.msc` (run sebagai admin) → **Inbound Rules** → **New Rule** → **Port**
   - TCP, **Specific local ports: 8000** (ulangi untuk 8080,80)
   - **Allow the connection** → semua profile → beri nama → Finish.
4. Perangkat lain buka `http://192.168.250.192:8000/`.

> Jangan buka port 3306 (MySQL) ke jaringan — cukup untuk server lokal, itu
> permukaan serangan yang tidak perlu.

---

## Langkah 11 — IP statis (agar URL tidak berubah-ubah)

IP dari DHCP bisa berubah. Supaya URL server tetap:

1. Cek nilai sekarang: `ipconfig` (IP, Subnet mask, Default gateway, DNS).
2. Control Panel → Network & Internet → Network Connections → klik kanan
   adapter (Wi-Fi/Ethernet) → **Properties**.
3. Pilih **Internet Protocol Version 4 (TCP/IPv4)** → **Properties**.
4. Pilih **Use the following IP address** → isi dengan nilai dari `ipconfig`.
5. OK → tersimpan; IP tidak berubah lagi.

---

## Troubleshooting

### 1. Kode PHP tampil mentah (tidak dieksekusi)
Module PHP tidak aktif. Kembali ke Langkah 4: pastikan tidak ada `#` di baris
`LoadModule php_module`, blok `<FilesMatch>` dan `PHPIniDir` aktif. Restart
penuh Apache.

### 2. `Composer ... require a PHP version ">= 8.4.1". You are running 8.x`
Apache masih memuat PHP lama. Pastikan ikut Langkah 4 (path `php84`,
`PHPIniDir`) **dan** tidak ada `php8ts.dll` versi lama di `D:\xampp\apache\bin\`.
Restart penuh sebagai admin.

### 3. Versi PHP tetap lama padahal config sudah 8.4
Windows mendahulukan DLL dari folder executable Apache. Ulangi prosedur rename
`php*.dll` di Langkah 4. Verifikasi:
```powershell
Get-Process httpd | ForEach-Object { $_.Modules } |
  Where-Object { $_.ModuleName -match "^php" } | Select FileName
```

### 4. 404 Not Found saat akses `http://IP/Sistem-SPI`
Laravel tidak bisa jalan di sub-path (lihat Langkah 8). Pakai port:
`http://localhost:8000/` atau gunakan RedirectMatch `:80`.

### 5. HTTP 500 setelah PHP aktif
Cek `D:\xampp\htdocs\Sistem-SPI\storage\logs\laravel.log`. Umumnya:
- `sessions' doesn't exist` → migration belum dijalankan (Langkah 6).

### 6. 419 Page Expired saat login
Cookie/login halaman lama. Buka incognito / bersihkan cookie situs, muat ulang.

### 7. Perangkat lain tidak bisa membuka aplikasi
Urutan: `ping <IP>` (fail = jaringan terisolasi / AP isolation hotspot),
firewall port 80/8000 (Langkah 10), `.env` `APP_URL` pakai IP saat ini.

---

## Ringkasan perintah

```bat
D:\xampp\apache\bin\httpd.exe -t
D:\xampp\apache\bin\httpd.exe -t -D DUMP_MODULES
D:\xampp\php84\php.exe -v
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan migrate --force
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan config:clear
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan cache:clear
D:\xampp\php84\php.exe D:\xampp\htdocs\Sistem-SPI\artisan route:clear
```

## Perbandingan singkat — XAMPP vs Laragon

| | XAMPP | Laragon |
|---|---|---|
| PHP 8.4 | Pasang manual (Langkah 3-4) | Pilih menu / salin folder |
| Vhost multi-project | Vhost manual per port | Otomatis `.test` per folder |
| Auto-start | Service Windows (Automatic) | Preferences "Run when Windows starts" |
| Install/portable | Installer | Portable |
| Cocok jika | Sudah dipakai tim/IT, butuh tool bawaan (FileZilla dsb) | Development ringan & cepat |