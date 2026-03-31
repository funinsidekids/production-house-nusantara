param(
    [Parameter(Mandatory = $true)]
    [string]$Php85Source,
    [string]$XamppRoot = 'D:\xampp',
    [string]$ExpectedMajorMinor = '8.5',
    [switch]$StartApache
)

$ErrorActionPreference = 'Stop'

function Fail([string]$Message) {
    Write-Host "ERROR: $Message" -ForegroundColor Red
    exit 1
}

function Ensure-Exists([string]$Path) {
    if (-not (Test-Path $Path)) {
        Fail "Path tidak ditemukan: $Path"
    }
}

function Get-Version([string]$Path) {
    return (Get-Item $Path).VersionInfo.FileVersion
}

function Assert-VersionPrefix([string]$Path, [string]$Expected) {
    $version = Get-Version $Path
    if ($version -notlike "$Expected*") {
        Fail "Versi tidak sesuai untuk $(Split-Path $Path -Leaf). Dapat: $version, expected prefix: $Expected"
    }
}

$source = (Resolve-Path $Php85Source).Path
$phpTarget = Join-Path $XamppRoot 'php'
$apacheBin = Join-Path $XamppRoot 'apache\bin\httpd.exe'
$apacheConf = Join-Path $XamppRoot 'apache\conf\extra\httpd-xampp.conf'
$checkScript = Join-Path $PSScriptRoot 'check-php85-stack.ps1'

Ensure-Exists $source
Ensure-Exists $phpTarget
Ensure-Exists $apacheBin
Ensure-Exists $apacheConf
Ensure-Exists $checkScript

$requiredSource = @(
    'php.exe',
    'php8ts.dll',
    'php8apache2_4.dll',
    'ext\php_openssl.dll',
    'libssl-3-x64.dll',
    'libcrypto-3-x64.dll',
    'php.ini-development',
    'php.ini-production'
) | ForEach-Object { Join-Path $source $_ }

foreach ($path in $requiredSource) {
    Ensure-Exists $path
}

Assert-VersionPrefix (Join-Path $source 'php.exe') $ExpectedMajorMinor
Assert-VersionPrefix (Join-Path $source 'php8ts.dll') $ExpectedMajorMinor
Assert-VersionPrefix (Join-Path $source 'php8apache2_4.dll') $ExpectedMajorMinor
Assert-VersionPrefix (Join-Path $source 'ext\php_openssl.dll') $ExpectedMajorMinor

Get-CimInstance Win32_Process | Where-Object { $_.Name -match '^httpd(\.exe)?$|^php(\.exe)?$|^mysqld(\.exe)?$' } | ForEach-Object {
    try {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction Stop
    } catch {
    }
}

Start-Sleep -Milliseconds 800

$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$backupPath = Join-Path $XamppRoot ("php_backup_" + $timestamp)
$tempPath = Join-Path $XamppRoot ("php_new_" + $timestamp)

Write-Host "Backup current PHP folder -> $backupPath" -ForegroundColor Yellow
Move-Item -Path $phpTarget -Destination $backupPath

Write-Host "Copy new PHP 8.5 folder -> $tempPath" -ForegroundColor Yellow
New-Item -ItemType Directory -Path $tempPath | Out-Null
Copy-Item -Path (Join-Path $source '*') -Destination $tempPath -Recurse

Write-Host "Switch new PHP folder -> $phpTarget" -ForegroundColor Yellow
Move-Item -Path $tempPath -Destination $phpTarget

$phpIni = Join-Path $phpTarget 'php.ini'
if (-not (Test-Path $phpIni)) {
    Copy-Item -Path (Join-Path $phpTarget 'php.ini-development') -Destination $phpIni
}

$iniContent = Get-Content -Path $phpIni -Raw
$iniContent = [regex]::Replace($iniContent, '(?m)^\s*upload_max_filesize\s*=.*$', 'upload_max_filesize=600M')
$iniContent = [regex]::Replace($iniContent, '(?m)^\s*post_max_size\s*=.*$', 'post_max_size=650M')
$iniContent = [regex]::Replace($iniContent, '(?m)^\s*memory_limit\s*=.*$', 'memory_limit=1024M')
if ($iniContent -notmatch '(?m)^\s*upload_max_filesize\s*=') { $iniContent += "`r`nupload_max_filesize=600M" }
if ($iniContent -notmatch '(?m)^\s*post_max_size\s*=') { $iniContent += "`r`npost_max_size=650M" }
if ($iniContent -notmatch '(?m)^\s*memory_limit\s*=') { $iniContent += "`r`nmemory_limit=1024M" }
Set-Content -Path $phpIni -Value $iniContent -Encoding ASCII

$apacheText = Get-Content -Path $apacheConf -Raw
$apacheText = [regex]::Replace($apacheText, 'LoadFile\s+"D:/Xampp/php/php8ts\.dll"', 'LoadFile "D:/Xampp/php/php8ts.dll"')
$apacheText = [regex]::Replace($apacheText, 'LoadModule\s+php_module\s+"D:/Xampp/php/php8apache2_4\.dll"', 'LoadModule php_module "D:/Xampp/php/php8apache2_4.dll"')
$apacheText = [regex]::Replace($apacheText, 'PHPINIDir\s+"D:/Xampp/php"', 'PHPINIDir "D:/Xampp/php"')
Set-Content -Path $apacheConf -Value $apacheText -Encoding ASCII

if ($StartApache) {
    Write-Host "Start Apache..." -ForegroundColor Yellow
    & $apacheBin -k start | Out-Null
    Start-Sleep -Milliseconds 1000
}

Write-Host ''
Write-Host "Run verification script..." -ForegroundColor Cyan
& powershell -ExecutionPolicy Bypass -File $checkScript -XamppRoot $XamppRoot -ExpectedMajorMinor $ExpectedMajorMinor
$exitCode = $LASTEXITCODE
if ($exitCode -ne 0) {
    Write-Host ''
    Write-Host "Migration selesai namun verifikasi masih FAIL. Cek output check script di atas." -ForegroundColor Red
    Write-Host "Backup lama tersimpan di: $backupPath" -ForegroundColor Yellow
    exit $exitCode
}

Write-Host ''
Write-Host "Migration SUCCESS. Backup lama tersimpan di: $backupPath" -ForegroundColor Green
exit 0
