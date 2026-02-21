#!/bin/bash

echo "=========================================="
echo "FIXING FILAMENT CSS ISSUES"
echo "=========================================="

# 1. Backup files lama
echo "1. Backing up old config files..."
cp tailwind.config.js tailwind.config.js.backup 2>/dev/null || echo "No old tailwind.config.js"
cp vite.config.ts vite.config.ts.backup 2>/dev/null || echo "No old vite.config.ts"
cp package.json package.json.backup 2>/dev/null || echo "No old package.json"

# 2. Install dependencies baru
echo ""
echo "2. Installing missing dependencies..."
npm install -D @tailwindcss/forms @tailwindcss/typography autoprefixer postcss

# 3. Downgrade Tailwind ke v3 (Filament compatible)
echo ""
echo "3. Downgrading Tailwind CSS to v3..."
npm install -D tailwindcss@^3.4.18

# 4. Remove Tailwind v4 vite plugin
echo ""
echo "4. Removing Tailwind v4 Vite plugin..."
npm uninstall @tailwindcss/vite

# 5. Clear cache
echo ""
echo "5. Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
rm -rf node_modules/.vite
rm -rf public/build

# 6. Rebuild assets
echo ""
echo "6. Building assets..."
npm run build

echo ""
echo "=========================================="
echo "✅ DONE! Now test your admin panel"
echo "=========================================="
echo ""
echo "If issues persist, run:"
echo "  php artisan filament:optimize-clear"
echo "  php artisan optimize:clear"
