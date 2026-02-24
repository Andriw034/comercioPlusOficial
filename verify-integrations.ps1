param(
  [string]$BackendUrl = "http://127.0.0.1:8000",
  [switch]$Pretty
)

$ErrorActionPreference = "Stop"
$base = $BackendUrl.TrimEnd('/')

function Hit([string]$url) {
  try {
    $resp = Invoke-WebRequest -UseBasicParsing -Method Get -Uri $url -Headers @{ 'Accept' = 'application/json' } -TimeoutSec 25
    return @{
      ok = $true
      status = [int]$resp.StatusCode
      body = $resp.Content
    }
  }
  catch {
    if ($_.Exception.Response) {
      $code = [int]$_.Exception.Response.StatusCode
      $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
      return @{
        ok = $false
        status = $code
        body = $reader.ReadToEnd()
      }
    }
    return @{
      ok = $false
      status = 0
      body = $_.Exception.Message
    }
  }
}

$health = Hit("$base/api/health")
$integrations = Hit("$base/api/health/integrations")

if ($Pretty) {
  Write-Host "Health status: $($health.status)" -ForegroundColor Cyan
  Write-Host $health.body
  Write-Host "Integrations status: $($integrations.status)" -ForegroundColor Cyan
  Write-Host $integrations.body
} else {
  [pscustomobject]@{
    health_status = $health.status
    health_body = $health.body
    integrations_status = $integrations.status
    integrations_body = $integrations.body
  } | ConvertTo-Json -Depth 8
}
