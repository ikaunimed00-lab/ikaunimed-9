<#
.SYNOPSIS
  Deploy IKA UNIMED (ikaunimed-9) ke produksi via git archive + SCP.

.DESCRIPTION
  - Build Vite, upload ke build_incoming, lalu atomic swap (aset lama tetap
    dilayani sampai aset baru siap).
  - Selalu chmod SELURUH public/ ke dirs 755 / files 644 — SCP/unzip dari Windows
    sering membuat 640 yang ditolak LiteSpeed/nobody (HTTP 403 pada Vite DAN Filament).
  - Setelah deploy: php artisan filament:assets lalu chmod ulang public/.
#>

[CmdletBinding()]
param(
    [string]$SshKey = "C:\Users\Asus\.ssh\id_ikaunimed",
    [string]$HostName = "103.185.53.246",
    [string]$User = "elawepco",
    [string]$RemoteRoot = "/home/elawepco/repositories/ikaunimed-9",
    [switch]$SkipBuild,
    [switch]$SkipComposer
)

$ErrorActionPreference = "Stop"
$RepoRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
Set-Location $RepoRoot

Write-Host "==> 1/6 npm run build" -ForegroundColor Cyan
if (-not $SkipBuild) {
    npm run build
    if ($LASTEXITCODE -ne 0) { throw "npm run build failed" }
}

if (-not (Test-Path "public\build\manifest.json")) {
    throw "public/build/manifest.json missing after build"
}

Write-Host "==> 2/6 git archive source.zip" -ForegroundColor Cyan
if (Test-Path "source.zip") { Remove-Item "source.zip" -Force }
git archive -o source.zip HEAD
if ($LASTEXITCODE -ne 0) { throw "git archive failed" }

Write-Host "==> 3/6 upload source.zip + finalize script" -ForegroundColor Cyan
scp -i $SshKey -o ConnectTimeout=30 -o BatchMode=yes source.zip "${User}@${HostName}:${RemoteRoot}/source.zip"
if ($LASTEXITCODE -ne 0) { throw "scp source.zip failed" }
scp -i $SshKey -o ConnectTimeout=30 -o BatchMode=yes scripts/remote-finalize-deploy.sh "${User}@${HostName}:${RemoteRoot}/remote-finalize-deploy.sh"
if ($LASTEXITCODE -ne 0) { throw "scp finalize script failed" }

Write-Host "==> 4/6 upload build to staging (keep live assets until swap)" -ForegroundColor Cyan
ssh -i $SshKey -o ConnectTimeout=30 -o BatchMode=yes "${User}@${HostName}" "rm -rf ${RemoteRoot}/public/build_incoming && mkdir -p ${RemoteRoot}/public/build_incoming"
scp -i $SshKey -o ConnectTimeout=120 -o BatchMode=yes -r public/build/* "${User}@${HostName}:${RemoteRoot}/public/build_incoming/"
if ($LASTEXITCODE -ne 0) { throw "scp build staging failed" }

Write-Host "==> 5/6 remote extract, atomic swap, chmod 755/644, caches" -ForegroundColor Cyan
$skip = if ($SkipComposer) { "1" } else { "0" }
ssh -i $SshKey -o ConnectTimeout=30 -o BatchMode=yes "${User}@${HostName}" "chmod +x ${RemoteRoot}/remote-finalize-deploy.sh && SKIP_COMPOSER=${skip} bash ${RemoteRoot}/remote-finalize-deploy.sh ${RemoteRoot} && rm -f ${RemoteRoot}/remote-finalize-deploy.sh"
if ($LASTEXITCODE -ne 0) { throw "remote deploy steps failed" }

Write-Host "==> 6/6 cleanup local source.zip" -ForegroundColor Cyan
Remove-Item "source.zip" -Force -ErrorAction SilentlyContinue

Write-Host "Deploy selesai. Verifikasi:" -ForegroundColor Green
Write-Host "  curl -I https://www.ikaunimed.or.id/build/assets/...  -> 200 + MIME benar"
Write-Host "  curl -I https://www.ikaunimed.or.id/js/filament/filament/app.js -> 200"
Write-Host "  curl -I https://www.ikaunimed.or.id/css/filament/filament/app.css -> 200"
