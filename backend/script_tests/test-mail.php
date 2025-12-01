<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailMail;

// Generar token de verificación (simulando lo que haría el sistema)
$token = bin2hex(random_bytes(32));
$userName = 'Juan Pérez';
$userEmail = 'juan@example.com';
$verificationUrl = "http://localhost:3000/verify-email?token={$token}";

echo "📧 Enviando email de verificación...\n";
echo "   Usuario: {$userName}\n";
echo "   Email: {$userEmail}\n";
echo "   Token: {$token}\n";
echo "\n";

Mail::to($userEmail)->send(new VerifyEmailMail(
    userName: $userName,
    verificationToken: $token,
    verificationUrl: $verificationUrl
));

echo "✅ Email enviado! Revisa http://localhost:8025\n";
