param(
    [string]$XamppRoot = 'D:\xampp',
    [string]$ExpectedMajorMinor = '8.5',
    [int]$MinUploadMB = 500,
    [int]$MinPostMB = 600,
    [int]$MinMemoryMB = 1024
)

$ErrorActionPreference = 'Stop'
$results = New-Object System.Collections.Generic.List[object]

function Add-Check {
    param(
        [string]$Name,
        [bool]$Pass,
        [string]$Detail
    )
    $results.Add([pscustomobject]@{
            Name   = $Name
            Status = if ($Pass) { 'PASS' } else { 'FAIL' }
            Detail = $Detail
        })
}

function Parse-Bytes {
    param([string]$Value)
    if ([string]::IsNullOrWhiteSpace($Value)) { return 0L }
    $raw = $Value.Trim()
    if ($raw -match '^\d+$') { return [int64]$raw }
    if ($raw -match '^(?<num>\d+)(?<unit>[KMG])$') {
        $num = [int64]$Matches['num']
        $unit = $Matches['unit']
        switch ($unit) {
            'K' { return $num * 1KB }
            'M' { return $num * 1MB }
            'G' { return $num * 1GB }
        }
    }
    return 0L
}

function Version-Ok {
    param([string]$Version, [string]$ExpectedMM)
    return ($Version -like "$ExpectedMM*")
}

$phpDir = Join-Path $XamppRoot 'php'
$apacheConf = Join-Path $XamppRoot 'apache\conf\extra\httpd-xampp.conf'

$required = @(
    'php.exe',
    'php8ts.dll',
    'php8apache2_4.dll',
    'ext\php_openssl.dll',
    'libssl-3-x64.dll',
    'libcrypto-3-x64.dll'
) | ForEach-Object { Join-Path $phpDir $_ }

foreach ($path in $required) {
    $exists = Test-Path $path
    Add-Check -Name "File exists: $path" -Pass $exists -Detail ($(if ($exists) { 'found' } else { 'missing' }))
}

$versions = @{}
foreach ($path in $required) {
    if (Test-Path $path) {
        $v = (Get-Item $path).VersionInfo.FileVersion
        $versions[$path] = $v
    }
}

$phpScoped = @(
    (Join-Path $phpDir 'php.exe'),
    (Join-Path $phpDir 'php8ts.dll'),
    (Join-Path $phpDir 'php8apache2_4.dll'),
    (Join-Path $phpDir 'ext\php_openssl.dll')
)
foreach ($path in $phpScoped) {
    if ($versions.ContainsKey($path)) {
        $v = $versions[$path]
        Add-Check -Name "Version match ($ExpectedMajorMinor): $(Split-Path $path -Leaf)" -Pass (Version-Ok -Version $v -ExpectedMM $ExpectedMajorMinor) -Detail $v
    }
}

$libSsl = Join-Path $phpDir 'libssl-3-x64.dll'
$libCrypto = Join-Path $phpDir 'libcrypto-3-x64.dll'
if ($versions.ContainsKey($libSsl) -and $versions.ContainsKey($libCrypto)) {
    $sslVer = $versions[$libSsl]
    $cryptoVer = $versions[$libCrypto]
    $opensslPairOk = ($sslVer -eq $cryptoVer)
    $opensslMajorOk = ($sslVer -match '^3\.')
    Add-Check -Name 'OpenSSL runtime pair version match' -Pass $opensslPairOk -Detail "libssl=$sslVer | libcrypto=$cryptoVer"
    Add-Check -Name 'OpenSSL runtime major version is 3.x' -Pass $opensslMajorOk -Detail $sslVer
}

$phpVersions = $phpScoped | Where-Object { $versions.ContainsKey($_) } | ForEach-Object { $versions[$_] }
if ($phpVersions.Count -gt 0) {
    $majorMinorSet = $phpVersions | ForEach-Object {
        if ($_ -match '^\d+\.\d+') { $Matches[0] } else { $_ }
    } | Sort-Object -Unique
    Add-Check -Name 'All PHP binaries same major.minor' -Pass ($majorMinorSet.Count -eq 1) -Detail ($majorMinorSet -join ', ')
}

$phpExe = Join-Path $phpDir 'php.exe'
if (Test-Path $phpExe) {
    $phpV = & $phpExe -v 2>&1 | Select-Object -First 1
    Add-Check -Name 'php -v major.minor' -Pass ($phpV -match "PHP\s+$ExpectedMajorMinor") -Detail $phpV

    $phpInfo = & $phpExe -i 2>&1
    $uploadLine = ($phpInfo | Select-String '^upload_max_filesize\s*=>').Line
    $postLine = ($phpInfo | Select-String '^post_max_size\s*=>').Line
    $memLine = ($phpInfo | Select-String '^memory_limit\s*=>').Line

    $uploadVal = if ($uploadLine -match '=>\s*([^\s]+)\s*=>') { $Matches[1] } else { '' }
    $postVal = if ($postLine -match '=>\s*([^\s]+)\s*=>') { $Matches[1] } else { '' }
    $memVal = if ($memLine -match '=>\s*([^\s]+)\s*=>') { $Matches[1] } else { '' }

    $uploadBytes = Parse-Bytes $uploadVal
    $postBytes = Parse-Bytes $postVal
    $memBytes = Parse-Bytes $memVal

    Add-Check -Name "upload_max_filesize >= ${MinUploadMB}M" -Pass ($uploadBytes -ge ($MinUploadMB * 1MB)) -Detail $uploadVal
    Add-Check -Name "post_max_size >= ${MinPostMB}M" -Pass ($postBytes -ge ($MinPostMB * 1MB)) -Detail $postVal
    Add-Check -Name "memory_limit >= ${MinMemoryMB}M" -Pass ($memBytes -ge ($MinMemoryMB * 1MB)) -Detail $memVal
}

if (Test-Path $apacheConf) {
    $apacheText = Get-Content -Path $apacheConf -Raw
    $loadModuleOk = $apacheText -match 'LoadModule\s+php_module\s+"D:/Xampp/php/php8apache2_4\.dll"'
    $iniDirOk = $apacheText -match 'PHPINIDir\s+"D:/Xampp/php"'
    Add-Check -Name 'Apache php_module path points to D:/Xampp/php/php8apache2_4.dll' -Pass $loadModuleOk -Detail ($(if ($loadModuleOk) { 'ok' } else { 'not matched' }))
    Add-Check -Name 'Apache PHPINIDir points to D:/Xampp/php' -Pass $iniDirOk -Detail ($(if ($iniDirOk) { 'ok' } else { 'not matched' }))
}
else {
    Add-Check -Name 'Apache config exists' -Pass $false -Detail $apacheConf
}

$apacheProc = Get-CimInstance Win32_Process | Where-Object { $_.Name -ieq 'httpd.exe' }
Add-Check -Name 'Apache process running' -Pass ($apacheProc.Count -gt 0) -Detail ("count=" + $apacheProc.Count)

Write-Host ''
Write-Host '=== PHP 8.5 STACK CHECK ==='
$results | Format-Table -AutoSize

$failed = $results | Where-Object { $_.Status -eq 'FAIL' }
Write-Host ''
if ($failed.Count -eq 0) {
    Write-Host 'FINAL RESULT: PASS' -ForegroundColor Green
    exit 0
}
else {
    Write-Host "FINAL RESULT: FAIL ($($failed.Count) checks failed)" -ForegroundColor Red
    exit 1
}
