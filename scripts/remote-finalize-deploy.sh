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

# Windows/OpenSSH SCP often creates 640 files; LiteSpeed (nobody) needs 644.
find public/build -type d -exec chmod 755 {} \;
find public/build -type f -exec chmod 644 {} \;
chmod 755 public/build public/build/assets 2>/dev/null || true
chmod 755 public 2>/dev/null || true
find public -maxdepth 1 -type f -exec chmod 644 {} \; 2>/dev/null || true

if [ "${SKIP_COMPOSER:-0}" != "1" ]; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi

php artisan migrate --force
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
