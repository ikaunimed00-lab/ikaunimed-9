#!/bin/bash
set -euo pipefail
ROOT="${1:-/home/elawepco/repositories/ikaunimed-9}"
cd "$ROOT"

if [ -f source.zip ]; then
  unzip -o source.zip
  rm -f source.zip
fi

# Atomic-ish swap: keep live assets until new tree is in place.
if [ -d public/build_incoming ]; then
  if [ -d public/build ]; then
    mv public/build "public/build_old_$$"
  fi
  mv public/build_incoming public/build
  rm -rf "public/build_old_$$"
fi

# Windows/OpenSSH SCP + unzip often create 640 files; LiteSpeed (nobody) needs 644.
# Apply to the entire public/ tree (Vite build, Filament js/css/fonts, images, downloads).
find public -type d -exec chmod 755 {} \;
find public -type f -exec chmod 644 {} \;
chmod 755 public 2>/dev/null || true

if [ "${SKIP_COMPOSER:-0}" != "1" ]; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi

php artisan migrate --force

# Refresh Filament published assets, then re-apply readable perms (umask may reintroduce 640).
php artisan filament:assets
find public -type d -exec chmod 755 {} \;
find public -type f -exec chmod 644 {} \;

php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

php -r '
$m = json_decode(file_get_contents("public/build/manifest.json"), true);
$entry = $m["resources/js/app.tsx"] ?? null;
if (!$entry) { fwrite(STDERR, "manifest missing app entry\n"); exit(1); }
$file = "public/build/".$entry["file"];
if (!is_readable($file)) { fwrite(STDERR, "not readable: $file\n"); exit(1); }
foreach (($entry["css"] ?? []) as $css) {
  $p = "public/build/".$css;
  if (!is_readable($p)) { fwrite(STDERR, "not readable: $p\n"); exit(1); }
}
echo "manifest assets readable\n";
'

for f in \
  public/js/filament/filament/app.js \
  public/css/filament/filament/app.css \
  public/js/filament/support/support.js
do
  if [ ! -r "$f" ]; then
    echo "not readable: $f" >&2
    exit 1
  fi
done
echo "filament assets readable"
