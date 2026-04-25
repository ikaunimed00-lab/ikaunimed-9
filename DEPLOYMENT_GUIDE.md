# Panduan Deployment Manual (SCP & SSH)

Dokumen ini berisi langkah-langkah untuk melakukan deployment project `ikaunimed-9.or.id` ke hosting menggunakan CMD dan SSH.

## 1. Persiapan Build (Lokal)

Jalankan perintah ini di **PowerShell / CMD Lokal** (di dalam folder project):

```powershell
# PENTING: Downgrade PHP Platform ke 8.3 (agar sesuai hosting) sebelum build
composer config platform.php 8.3.30
composer update --no-dev

# 1. Build frontend assets (Vite)
npm run build

# 2. Buat ZIP source code (menggunakan git archive agar bersih dari node_modules/vendor)
if (Test-Path source.zip) { Remove-Item source.zip }
git archive -o source.zip HEAD

# 3. Buat ZIP assets build
if (Test-Path assets.zip) { Remove-Item assets.zip }
Compress-Archive -Path public/build -DestinationPath assets.zip -Force
```

## 2. Upload File (Lokal)

Gunakan `scp` untuk mengupload file ke server.
**PENTING**: Pastikan path SSH Key (`-i`) sesuai dengan lokasi di komputer Anda.

```powershell
# Upload ke folder repositories/ikaunimed-9 (pastikan folder tujuan sudah dibuat di server)
# Sertakan juga composer.json dan composer.lock yang sudah di-downgrade
scp -i "C:\Users\Asus\.ssh\id_ikaunimed" source.zip assets.zip composer.json composer.lock elawepco@103.185.53.246:repositories/ikaunimed-9/
```

**Troubleshooting Upload:**
- Jika error `Permission denied (publickey)`, pastikan path key `-i "..."` benar dan file key ada.
- Jika error `host key verification failed`, hapus baris terkait IP server di `C:\Users\Asus\.ssh\known_hosts` atau tambahkan `-o StrictHostKeyChecking=no`.

## 3. Ekstrak & Setup (Server / SSH)

Login ke server via SSH, lalu jalankan:

```bash
# Masuk ke folder tujuan
cd repositories/ikaunimed-9

# Ekstrak source code
unzip source.zip

# Ekstrak assets (overwrite jika ada)
unzip -o assets.zip

# Hapus file zip setelah ekstrak
rm source.zip assets.zip
```

## 4. Langkah Selanjutnya (Setelah Upload)

Setelah file diekstrak, lakukan langkah berikut di server:

1.  **Copy .env**: `cp ../ikaunimed-8/.env .env` (sesuaikan path folder lama)
2.  **Install Vendor**: `composer install --no-dev --optimize-autoloader`
3.  **Link Storage**: `php artisan storage:link`
4.  **Migrasi DB**: `php artisan migrate --force` (HATI-HATI: Backup DB dulu!)
5.  **Seed Data** (Jika konten kosong): `php artisan db:seed --class=HomepageSeeder --force`
6.  **Clear Cache**: `php artisan optimize:clear`

### 4. Switch Live Site

#### Option A: Symlink Switch (Zero Downtime - Recommended)
Use this if you want to keep `public_html` as the Document Root (best for subdomains in subfolders).

1.  **Update `public_html/index.php`**:
    Edit `public_html/index.php` to point to the new repository paths.

    ```php
    <?php
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;

    define('LARAVEL_START', microtime(true));

    // Adjust path to new repository (e.g., ikaunimed-9)
    if (file_exists($maintenance = __DIR__.'/../repositories/ikaunimed-9/storage/framework/maintenance.php')) {
        require $maintenance;
    }

    require __DIR__.'/../repositories/ikaunimed-9/vendor/autoload.php';

    /** @var Application $app */
    $app = require_once __DIR__.'/../repositories/ikaunimed-9/bootstrap/app.php';

    $app->handleRequest(Request::capture());
    ```

2.  **Update Symlinks in `public_html`**:
    Run these commands via SSH to point assets to the new build:

    ```bash
    cd public_html
    # Remove old build directory/symlink
    rm -rf build
    # Link new build
    ln -s ../repositories/ikaunimed-9/public/build build
    # Link storage (if not already linked to a shared location)
    ln -sfn ../repositories/ikaunimed-9/storage/app/public storage
    # Link images (if managed via repository)
    ln -sfn ../repositories/ikaunimed-9/public/images images
    ```

#### Option B: cPanel Document Root Change (Cleanest)
Use this for major version upgrades or if you want to isolate the new version completely.

1.  **Login to cPanel**.
2.  Go to **Domains**.
3.  Click **Manage** on `ikaunimed.or.id`.
4.  Update **Document Root** to `/repositories/ikaunimed-9/public`.
5.  Click **Update**.
6.  **Verify**: Create a test file (e.g., `v9.txt`) in the new public folder and access it via browser.

---

### 5. Post-Deployment Checks

#### PHP Version Mismatch (Composer)
If server runs PHP 8.3 but Composer expects 8.4:
1.  Run locally: `composer config platform.php 8.3.30`
2.  Run `composer update`
3.  Commit `composer.json` and `composer.lock`
4.  Re-deploy.

#### 404 on Assets
- Ensure `public_html/build` is a symlink to `repositories/ikaunimed-9/public/build`.
- Ensure permissions are correct (755 for folders, 644 for files).
- Check `manifest.json` in `public/build`.

#### 403 Forbidden
- Ensure `repositories` folder has execute permission (`chmod 711 repositories`).
- Ensure `repositories/ikaunimed-9` has execute permission (`chmod 755`).
- Ensure `public_html` index.php has 644 permission.

---

## 6. Troubleshooting Migrasi (SQLSTATE[42000]: Key too long)

Jika Anda menemui error seperti ini saat migrasi:
`SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 1000 bytes`

Ini terjadi karena panjang kolom index melebihi batas (biasanya pada tabel `transactions` atau `permissions`).

**Solusi:**
1.  Edit file migrasi terkait di lokal.
2.  Kurangi panjang kolom `string` dari default (255) menjadi `100` atau `125`.
    Contoh: `$table->string('model_type', 100);`
3.  Upload ulang file migrasi yang sudah diperbaiki via SCP:
    ```powershell
    scp -i "C:\Users\Asus\.ssh\id_ikaunimed" database/migrations/NAMA_FILE_MIGRASI.php elawepco@103.185.53.246:repositories/ikaunimed-9/database/migrations/
    ```
4.  Di server (SSH), hapus tabel "setengah jadi" menggunakan `tinker`:
    ```bash
    php artisan tinker
    > Schema::dropIfExists('nama_tabel_bermasalah');
    > DB::table('migrations')->where('migration', 'NAMA_FILE_MIGRASI_TANPA_PHP')->delete();
    > exit
    ```
5.  Jalankan migrasi ulang: `php artisan migrate --force`
