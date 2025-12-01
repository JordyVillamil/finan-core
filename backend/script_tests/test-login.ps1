$body = @{
    email = "juan@example.com"
    password = "password123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/api/auth/login" `
    -Method POST `
    -Body $body `
    -ContentType "application/json"