# Deploy Native PHP API ke Hosting Bersama Laravel

## Overview

- **Laravel Web**: folder `operasional/` (sudah di-hosting)
- **Native PHP API**: folder `operasional_app/api/` (belum di-hosting)
- **Flutter App**: folder `operasional_app/flutter_app/`
- **Database**: `umumcsrc_operasional` (sama untuk web dan API)

---

## Struktur Folder di Hosting

```
operasional/                        ← root project Laravel
├── app/
├── bootstrap/
├── config/
├── database/
├── routes/
├── vendor/
├── storage/
│   └── app/
│       └── public/
│           └── foto-meter/         ← foto upload tersimpan di sini
├── public/                         ← document root (di-expose ke web)
│   ├── index.php                   ← Laravel entry point
│   ├── .htaccess                   ← Laravel rewrite rules
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── storage → ../storage/app/public   ← SYMLINK (wajib)
│   └── api/                        ← NATIVE PHP API
│       ├── config.php
│       ├── login.php
│       ├── logout.php
│       ├── user.php
│       ├── dashboard.php
│       ├── areas.php
│       ├── titik_meter.php
│       ├── tagihan_air.php
│       └── meter_lalu.php
```

## URL Akses

| Komponen | URL |
|---|---|
| Web Laravel (login) | `https://domainanda.com/login` |
| Web Laravel (dashboard) | `https://domainanda.com/dashboard` |
| API Login | `https://domainanda.com/api/login.php` |
| API Logout | `https://domainanda.com/api/logout.php` |
| API User | `https://domainanda.com/api/user.php` |
| API Dashboard | `https://domainanda.com/api/dashboard.php` |
| API Areas | `https://domainanda.com/api/areas.php` |
| API Titik Meter | `https://domainanda.com/api/titik_meter.php` |
| API Tagihan Air | `https://domainanda.com/api/tagihan_air.php` |
| API Meter Lalu | `https://domainanda.com/api/meter_lalu.php` |
| Foto Meter | `https://domainanda.com/storage/foto-meter/foto_xxx.jpg` |

---

## Langkah-langkah Deploy

### Step 1: Upload Laravel ke Hosting

Upload **seluruh isi folder `operasional/`** ke hosting menggunakan File Manager atau FTP.

Yang di-upload:
```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
vendor/
artisan
composer.json
composer.lock
package.json
.env
```

### Step 2: Setting Document Root

Setting **document root** hosting mengarah ke folder `public/`:

```
public_html/  →  operasional/public/
```

> Di cPanel: `Addon Domains` atau `Subdomains` → Document Root arahkan ke `operasional/public`

### Step 3: Copy API ke dalam public/

Copy **isi folder `operasional_app/api/`** ke **`operasional/public/api/`**:

```
 operasional_app/api/config.php       →  operasional/public/api/config.php
 operasional_app/api/login.php        →  operasional/public/api/login.php
 operasional_app/api/logout.php       →  operasional/public/api/logout.php
 operasional_app/api/user.php         →  operasional/public/api/user.php
 operasional_app/api/dashboard.php    →  operasional/public/api/dashboard.php
 operasional_app/api/areas.php        →  operasional/public/api/areas.php
 operasional_app/api/titik_meter.php  →  operasional/public/api/titik_meter.php
 operasional_app/api/tagihan_air.php  →  operasional/public/api/tagihan_air.php
 operasional_app/api/meter_lalu.php   →  operasional/public/api/meter_lalu.php
```

### Step 4: Update Database Credentials di API

Edit file **`public/api/config.php`** — ganti bagian database:

```php
// SEBELUM (lokal)
$db_host = "localhost";
$db_port = "3306";
$db_name = "umumcsrc_operasional";
$db_user = "root";
$db_pass = "";

// SESUDAH (hosting) — sesuaikan dengan credential hosting Anda
$db_host = "localhost";              // biasanya tetap localhost
$db_port = "3306";                   // sesuaikan jika berbeda
$db_name = "umumcsrc_operasional";   // nama database di hosting
$db_user = "username_anda";          // username database hosting
$db_pass = "password_anda";          // password database hosting
```

### Step 5: Update Path Upload Foto di API

Edit file **`public/api/tagihan_air.php`**:

#### 5a. Ganti path upload pada bagian POST (sekitar baris 183-184):

```php
// SEBELUM (hardcoded lokal — tidak akan jalan di hosting)
$uploadDir = __DIR__ . '/uploads/foto-meter/';
$laravelStorageDir = 'E:/laragon/www/operasional/storage/app/public/foto-meter/';

// SESUDAH (relatif — portable di hosting manapun)
$uploadDir = __DIR__ . '/uploads/foto-meter/';
$laravelStorageDir = __DIR__ . '/../storage/app/public/foto-meter/';
```

#### 5b. Ganti URL generation pada bagian `format_tagihan_item` (sekitar baris 32):

```php
// SEBELUM
$url = "http://{$_SERVER['HTTP_HOST']}/operasional/public/storage/" . ltrim($path, '/');

// SESUDAH (auto-detect protocol dan domain)
$url = get_base_url() . "/../storage/" . ltrim($path, '/');
```

#### 5c. Ganti path delete pada bagian DELETE (sekitar baris 240):

```php
// SEBELUM
@unlink('E:/laragon/www/operasional/storage/app/public/' . $f['path_foto']);

// SESUDAH
@unlink(__DIR__ . '/../storage/app/public/' . $f['path_foto']);
```

### Step 6: Buat Symlink Storage

Symlink diperlukan agar foto di `storage/app/public/foto-meter/` bisa diakses via URL `/storage/foto-meter/...`.

#### Via Terminal/SSH hosting (jalankan di root project Laravel):
```bash
cd operasional
php artisan storage:link
```

#### Jika tidak punya akses SSH, buat manual via File Manager:
1. Buka File Manager di cPanel
2. Navigasi ke `operasional/public/`
3. Klik kanan → **New Symlink** (atau **Create Shortcut**)
   - **Symlink Name**: `storage`
   - **Target**: `../storage/app/public`

#### Atau buat via PHP (upload file ini ke `public/` lalu akses sekali):
Buat file **`public/create_storage_link.php`**:
```php
<?php
$target = __DIR__ . '/../storage/app/public';
$link   = __DIR__ . '/storage';

if (!is_dir($target)) {
    mkdir($target, 0777, true);
}

if (!file_exists($link)) {
    // Windows: junction
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec("mklink /J \"$link\" \"$target\"");
    } else {
        // Linux/Mac: symlink
        symlink($target, $link);
    }
    echo "Symlink berhasil dibuat: $link → $target";
} else {
    echo "Symlink sudah ada: $link";
}
?>
```
> **PENTING**: Hapus `create_storage_link.php` setelah berhasil dijalankan!

### Step 7: Update Konfigurasi Laravel (.env)

Edit file **`.env`** di root project Laravel:

```env
APP_URL=https://domainanda.com
```

### Step 8: Update Flutter App Base URL

Edit file **`operasional_app/flutter_app/lib/config/api_config.dart`**:

```dart
class ApiConfig {
  // Untuk production (hosting)
  static const String baseUrl = 'https://domainanda.com/api';

  // Untuk testing lokal (nonaktifkan yang di atas, aktifkan ini):
  // static const String baseUrl = 'http://localhost/operasional_app/api';

  // Untuk Android Emulator (testing lokal):
  // static const String baseUrl = 'http://10.0.2.2/operasional_app/api';

  // Untuk HP fisik (ganti IP sesuai WiFi laptop):
  // static const String baseUrl = 'http://192.168.x.x/operasional_app/api';

  static const Duration connectTimeout = Duration(seconds: 15);
  static const Duration receiveTimeout = Duration(seconds: 15);

  static Map<String, String> get defaultHeaders => {
        'Accept': 'application/json',
      };
}
```

### Step 9: Jalankan Migration di Hosting

Jika database di hosting masih kosong, jalankan migration:

```bash
php artisan migrate --force
```

### Step 10: Set Permission Folder Storage

Pastikan folder `storage/` writable:

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---

## Flow Upload Foto

```
Flutter App (user ambil foto)
        │
        ▼
POST https://domainanda.com/api/tagihan_air.php
  (multipart/form-data dengan field foto_meter[])
        │
        ▼
tagihan_air.php memproses upload:
  ├── 1. Simpan ke public/api/uploads/foto-meter/foto_xxx.jpg
  └── 2. Copy ke public/storage/app/public/foto-meter/foto_xxx.jpg
        │
        ▼
Simpan path "foto-meter/foto_xxx.jpg" ke database
  (tabel tagihan_air_foto)
        │
        ▼
Flutter / Web Laravel membaca URL foto:
  https://domainanda.com/storage/foto-meter/foto_xxx.jpg
  (melalui symlink public/storage → storage/app/public)
```

---

## Testing

### 1. Test API via Browser/Postman

```
GET  https://domainanda.com/api/areas.php
GET  https://domainanda.com/api/titik_meter.php
GET  https://domainanda.com/api/dashboard.php
POST https://domainanda.com/api/login.php
     Body: {"username": "admin", "password": "xxxxx"}
```

### 2. Test Upload Foto

```bash
curl -X POST https://domainanda.com/api/tagihan_air.php \
  -F "titik_meter_id=1" \
  -F "periode=2026-08" \
  -F "meter_ini=100" \
  -F "meter_faktor=1" \
  -F "tarif=5000" \
  -F "foto_meter[]=@/path/to/foto.jpg"
```

### 3. Cek Foto Tersimpan

Buka browser:
```
https://domainanda.com/storage/foto-meter/foto_xxx.jpg
```

### 4. Test dari Flutter App

Jalankan aplikasi Flutter dengan baseUrl sudah diupdate ke production domain.

---

## Troubleshooting

### API mengembalikan 404 / Not Found
- Pastikan document root sudah mengarah ke `public/`
- Pastikan folder `api/` ada di dalam `public/`
- Cek apakah file `.htaccess` di `public/` sudah ter-upload

### Foto tidak muncul / 404
- Pastikan symlink `public/storage` → `storage/app/public` sudah ada
- Cek permission folder `storage/` (harus writable, `775`)

### Database Connection Failed
- Cek credential di `public/api/config.php` sesuai dengan hosting
- Pastikan database sudah di-import ke hosting
- Jalankan `php artisan migrate` jika tabel belum ada

### CORS Error
- `config.php` sudah set `Access-Control-Allow-Origin: *`
- Jika masih error, tambahkan header di `.htaccess` public:

```apache
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
</IfModule>
```

### Upload Gagal (file tidak tersimpan)
- Cek folder `public/api/uploads/` writable (`chmod 775`)
- Cek folder `public/storage/app/public/` writable
- Cek `php.ini` untuk `upload_max_filesize` dan `post_max_size` (minimal 5MB)

---

## Catatan Penting

1. **Database shared** — API dan Laravel web menggunakan database yang sama (`umumcsrc_operasional`), sehingga data yang diinput dari Flutter (via API) langsung terlihat di web Laravel, begitu juga sebaliknya

2. **Foto shared** — Foto yang di-upload via API disimpan di `storage/app/public/foto-meter/`, sehingga bisa diakses baik dari API maupun web Laravel

3. **Base URL API otomatis** — `config.php` menggunakan fungsi `get_base_url()` yang otomatis mendeteksi protocol dan domain, sehingga tidak perlu hardcode URL

4. **Keamanan** — Untuk production, pertimbangkan untuk:
   - Menambahkan autentikasi API key
   - Membatasi akses folder `public/api/uploads/` via `.htaccess`
   - Menghapus file `create_storage_link.php` jika sudah dipakai
