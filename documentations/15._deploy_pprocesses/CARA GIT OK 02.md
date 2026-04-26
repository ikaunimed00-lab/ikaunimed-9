# SOP DEPLOY FINAL (KONSISTEN) - IKAUNIMED

Dokumen ini menggantikan prosedur lama yang kontradiktif.

## Prinsip Tetap

1. Parent path web WAJIB `755`, bukan `711`.
2. Static assets publik WAJIB `dir=755` dan `file=644`.
3. `storage` dan `bootstrap/cache` WAJIB writable (`775`).
4. Deploy dilakukan ke satu target saja:
   - `/home/elawepco/repositories/ikaunimed-9`

## A. Persiapan Lokal

```bash
git checkout main
git pull origin main
npm run build
```

Commit dan push perubahan:

```bash
git add .
git commit -m "pesan perubahan"
git push origin main
```

## B. Upload ke Hosting (Metode Standar Aman)

Catatan: jika folder hosting bukan git repo, gunakan metode ZIP/SCP ini.

### 1) Buat arsip source bersih dari HEAD

```bash
git archive -o source.zip HEAD
```

### 2) Upload source + build

```bash
scp -i "C:\Users\Asus\.ssh\id_ikaunimed" source.zip elawepco@103.185.53.246:/home/elawepco/repositories/ikaunimed-9/
scp -i "C:\Users\Asus\.ssh\id_ikaunimed" -r public/build elawepco@103.185.53.246:/home/elawepco/repositories/ikaunimed-9/public/
```

## C. Eksekusi di Hosting

Masuk SSH:

```bash
ssh -i "C:\Users\Asus\.ssh\id_ikaunimed" elawepco@103.185.53.246
cd /home/elawepco/repositories/ikaunimed-9
```

Ekstrak source:

```bash
unzip -o source.zip
rm -f source.zip
```

Install dependency + migrasi + cache:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

## D. Permission Final (Wajib)

### 1) Parent path (jangan 711)

```bash
chmod 755 /home/elawepco
chmod 755 /home/elawepco/repositories
chmod 755 /home/elawepco/repositories/ikaunimed-9
chmod 755 /home/elawepco/repositories/ikaunimed-9/public
```

### 2) Public assets

```bash
find /home/elawepco/repositories/ikaunimed-9/public -type d -exec chmod 755 {} \;
find /home/elawepco/repositories/ikaunimed-9/public -type f -exec chmod 644 {} \;
```

### 3) Writable Laravel folders

```bash
chmod -R 775 /home/elawepco/repositories/ikaunimed-9/storage
chmod -R 775 /home/elawepco/repositories/ikaunimed-9/bootstrap/cache
```

### 4) Ownership

```bash
chown -R elawepco:elawepco /home/elawepco/repositories/ikaunimed-9
```

## E. Verifikasi Pasca Deploy

```bash
curl -I https://ikaunimed.or.id/
curl -I "https://ikaunimed.or.id/news?cb=1"
curl -I "https://ikaunimed.or.id/courses?cb=1"
curl -I "https://ikaunimed.or.id/shop?cb=1"
curl -I "https://ikaunimed.or.id/favicon.ico?cb=1"
```

Expected:
- Homepage: `200`
- News/Courses/Shop: `200`
- Favicon: `200`

Catatan:
- `/contact` saat ini tidak terdaftar sebagai route publik di Laravel, jadi `404` adalah expected sampai route tersebut dibuat.

## F. Troubleshooting Cepat

1. **Asset 403/MIME error**
   - Jalankan ulang blok Permission Final.
   - Pastikan `public/build` dan `public/images` terbaca publik (`755/644`).

2. **Route 404 tapi `/index.php/...` bisa**
   - Biasanya cache edge/LiteSpeed masih menyimpan 404 lama.
   - Uji dengan query string (`?cb=timestamp`) dan purge cache.

3. **Migrate gagal key too long / duplicate index**
   - Gunakan migrasi yang sudah kompatibel MariaDB key-length + idempotent.