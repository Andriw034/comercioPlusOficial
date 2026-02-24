param(
  [string]$BaseUrl = "https://comercio-plus-oficial.vercel.app",
  [int]$TimeoutSec = 30,
  [switch]$SinProxy
)

$ErrorActionPreference = "Stop"

function Normalize-BaseUrl {
  param([string]$Url)
  $value = [string]$Url
  $value = $value.Trim()
  if ([string]::IsNullOrWhiteSpace($value)) {
    throw "BaseUrl es obligatorio."
  }
  if ($value.EndsWith("/")) {
    return $value.TrimEnd("/")
  }
  return $value
}

function Invoke-UrlCheck {
  param(
    [string]$Url,
    [int]$Timeout
  )

  $sw = [System.Diagnostics.Stopwatch]::StartNew()
  try {
    $resp = Invoke-WebRequest -Uri $Url -Method Get -UseBasicParsing -MaximumRedirection 5 -TimeoutSec $Timeout
    $sw.Stop()
    return [PSCustomObject]@{
      Url          = $Url
      Ok           = $true
      Status       = [int]$resp.StatusCode
      Ms           = [int]$sw.ElapsedMilliseconds
      ContentType  = [string]$resp.Headers["Content-Type"]
      CacheControl = [string]$resp.Headers["Cache-Control"]
      Server       = [string]$resp.Headers["Server"]
      Location     = [string]$resp.Headers["Location"]
      Length       = ([string]$resp.Content).Length
      Error        = ""
      Content      = [string]$resp.Content
    }
  } catch {
    $sw.Stop()
    $status = ""
    $contentType = ""
    $cacheControl = ""
    $server = ""
    $location = ""

    if ($_.Exception.Response) {
      $response = $_.Exception.Response
      try { $status = [int]$response.StatusCode } catch {}
      try { $contentType = [string]$response.Headers["Content-Type"] } catch {}
      try { $cacheControl = [string]$response.Headers["Cache-Control"] } catch {}
      try { $server = [string]$response.Headers["Server"] } catch {}
      try { $location = [string]$response.Headers["Location"] } catch {}
    }

    return [PSCustomObject]@{
      Url          = $Url
      Ok           = $false
      Status       = $status
      Ms           = [int]$sw.ElapsedMilliseconds
      ContentType  = $contentType
      CacheControl = $cacheControl
      Server       = $server
      Location     = $location
      Length       = 0
      Error        = $_.Exception.Message
      Content      = ""
    }
  }
}

$normalizedBaseUrl = Normalize-BaseUrl -Url $BaseUrl
$proxyVars = @(
  "HTTP_PROXY", "HTTPS_PROXY", "ALL_PROXY", "NO_PROXY",
  "http_proxy", "https_proxy", "all_proxy", "no_proxy"
)

$proxyBackup = @{}
if ($SinProxy) {
  foreach ($proxyVar in $proxyVars) {
    $proxyBackup[$proxyVar] = [Environment]::GetEnvironmentVariable($proxyVar, "Process")
    [Environment]::SetEnvironmentVariable($proxyVar, "", "Process")
  }
}

try {
  $routes = @(
    "/",
    "/dashboard",
    "/dashboard/reports",
    "/dashboard/inventory",
    "/login"
  )

  $checks = New-Object System.Collections.Generic.List[object]

  foreach ($route in $routes) {
    $url = "$normalizedBaseUrl$route"
    $checks.Add((Invoke-UrlCheck -Url $url -Timeout $TimeoutSec))
  }

  $homeCheck = $checks | Where-Object { $_.Url -eq "$normalizedBaseUrl/" } | Select-Object -First 1

  $assetPaths = @()
  if ($homeCheck -and $homeCheck.Ok -and $homeCheck.Content) {
    $matches = [regex]::Matches($homeCheck.Content, '(?i)(?:src|href)=["''](?<path>/assets/[^"''>]+\.(?:js|css)(?:\?[^"''>]+)?)["'']')
    $assetPaths = $matches |
      ForEach-Object { $_.Groups["path"].Value } |
      Where-Object { $_ -and $_.StartsWith("/assets/") } |
      Select-Object -Unique -First 6
  }

  foreach ($assetPath in $assetPaths) {
    $checks.Add((Invoke-UrlCheck -Url "$normalizedBaseUrl$assetPath" -Timeout $TimeoutSec))
  }

  Write-Host ""
  Write-Host "=== Verificacion Vercel ===" -ForegroundColor Cyan
  Write-Host "Base URL: $normalizedBaseUrl"
  Write-Host "SinProxy: $SinProxy"
  Write-Host ""

  $checks |
    Select-Object Url, Status, Ms, ContentType, CacheControl, Server, Location, Ok |
    Format-Table -AutoSize

  Write-Host ""
  $spaTargets = @(
    "$normalizedBaseUrl/dashboard",
    "$normalizedBaseUrl/dashboard/reports",
    "$normalizedBaseUrl/dashboard/inventory"
  )

  $spaFailures = $checks | Where-Object {
    $contentType = [string]$_.ContentType
    $spaTargets -contains $_.Url -and (
      $_.Status -ne 200 -or -not ($contentType.ToLower().Contains("text/html"))
    )
  }

  $assetFailures = $checks | Where-Object {
    $_.Url -like "$normalizedBaseUrl/assets/*" -and $_.Status -ne 200
  }

  if ($spaFailures.Count -eq 0 -and $assetFailures.Count -eq 0) {
    Write-Host "OK: SPA routes y assets principales responden correctamente." -ForegroundColor Green
    exit 0
  }

  Write-Host "FALLO: se detectaron rutas o assets con errores." -ForegroundColor Red

  if ($spaFailures.Count -gt 0) {
    Write-Host ""
    Write-Host "Rutas SPA con problema:" -ForegroundColor Yellow
    $spaFailures | Select-Object Url, Status, ContentType, Error | Format-Table -AutoSize
  }

  if ($assetFailures.Count -gt 0) {
    Write-Host ""
    Write-Host "Assets con problema:" -ForegroundColor Yellow
    $assetFailures | Select-Object Url, Status, ContentType, Error | Format-Table -AutoSize
  }

  exit 1
}
finally {
  if ($SinProxy) {
    foreach ($proxyVar in $proxyVars) {
      [Environment]::SetEnvironmentVariable($proxyVar, $proxyBackup[$proxyVar], "Process")
    }
  }
}
