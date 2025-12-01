# test-register.ps1
$body = @{
    name = "Juan Pérez"
    email = "juan@example.com"
    password = "password123"
    password_confirmation = "password123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/api/auth/register" `
    -Method POST `
    -Body $body `
    -ContentType "application/json"