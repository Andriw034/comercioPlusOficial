# PowerShell script to test login POST request with form data

$loginUrl = "http://127.0.0.1:8000/login"
$form = @{
    email = "andriw034@gmail.com"
    password = "1234567"
}

$response = Invoke-WebRequest -Uri $loginUrl -Method POST -Body $form -SessionVariable session -MaximumRedirection 0 -ErrorAction SilentlyContinue

if ($response.StatusCode -eq 302) {
    Write-Host "Login successful, redirected to:" $response.Headers.Location
} else {
    Write-Host "Login failed or unexpected response:"
    Write-Host $response.StatusCode
    Write-Host $response.Content
}
