<?php
/**
 * Script de prueba para reset de password
 * USO: docker-compose exec php php test-reset-password.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

$email = 'testuser@example.com';

echo "🔐 Generando token de reset para: {$email}\n\n";

// 1. Generar token
$plainToken = Str::random(64);
$hashedToken = hash('sha256', $plainToken);

// 2. Guardar en BD
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $email],
    [
        'token' => $hashedToken,
        'created_at' => now(),
    ]
);

// 3. Mostrar el token
echo "✅ Token generado exitosamente!\n\n";
echo "📧 Email: {$email}\n";
echo "🔑 Token (copia esto): \n\n";
echo $plainToken . "\n\n";

// 4. URL para frontend (cuando exista)
$resetUrl = "http://localhost:3000/reset-password?token={$plainToken}&email=" . urlencode($email);
echo "🔗 URL completa:\n{$resetUrl}\n\n";

// 5. Comando para probar el reset
echo "📝 Para probar el reset, ejecuta en PowerShell:\n\n";
echo '$body = @{' . "\n";
echo '    token = "' . $plainToken . '"' . "\n";
echo '    email = "' . $email . '"' . "\n";
echo '    password = "newpassword123"' . "\n";
echo '    password_confirmation = "newpassword123"' . "\n";
echo '} | ConvertTo-Json' . "\n\n";
echo 'Invoke-RestMethod -Uri "http://localhost/api/auth/reset-password" -Method POST -Body $body -ContentType "application/json"' . "\n";
