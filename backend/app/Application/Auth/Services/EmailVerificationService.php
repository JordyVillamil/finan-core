<?php

namespace App\Application\Auth\Services;

use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Exceptions\UserNotFoundException;
use App\Mail\VerifyEmailMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Service para verificación de email
 * 
 * CASOS DE USO:
 * 1. Enviar email de verificación
 * 2. Verificar email con token
 * 3. Reenviar email de verificación
 */
class EmailVerificationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Enviar email de verificación a un usuario recién registrado
     * 
     * @param Email $email Email del usuario
     * @return void
     */
    public function sendVerificationEmail(Email $email): void
    {
        // 1. Buscar usuario
        $user = $this->userRepository->findByEmail($email);

        // 2. Generar token único
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        // 3. Guardar token en BD
        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $email->value()],
            [
                'token' => $hashedToken,
                'created_at' => now(),
            ]
        );

        // 4. Generar URL de verificación
        $verificationUrl = config('app.frontend_url', 'http://localhost:3000') 
            . '/verify-email?token=' . $plainToken 
            . '&email=' . urlencode($email->value());

        // 5. Enviar email
        Mail::to($email->value())->send(new VerifyEmailMail(
            userName: $user->name(),
            verificationToken: $plainToken,
            verificationUrl: $verificationUrl
        ));
    }

    /**
     * Verificar email con token
     * 
     * @param string $token Token de verificación
     * @return void
     * @throws \Exception Si el token es inválido o ha expirado
     */
    public function verifyEmail(string $token): void
    {
        // 1. Hashear el token recibido
        $hashedToken = hash('sha256', $token);

        // 2. Buscar token en BD
        $record = DB::table('email_verification_tokens')
            ->where('token', $hashedToken)
            ->first();

        if (!$record) {
            throw new \Exception('Token de verificación inválido.');
        }

        // 3. Verificar que no haya expirado (24 horas)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addHours(24)->isPast()) {
            // Eliminar token expirado
            DB::table('email_verification_tokens')
                ->where('token', $hashedToken)
                ->delete();
            
            throw new \Exception('El token de verificación ha expirado. Solicita uno nuevo.');
        }

        // 4. Marcar email como verificado
        $email = Email::fromString($record->email);
        $user = $this->userRepository->findByEmail($email);
        
        // Actualizar en la BD
        DB::table('users')
            ->where('id', $user->id()->value())
            ->update(['email_verified_at' => now()]);

        // 5. Eliminar token usado
        DB::table('email_verification_tokens')
            ->where('token', $hashedToken)
            ->delete();
    }

    /**
     * Reenviar email de verificación
     * 
     * @param Email $email Email del usuario
     * @return void
     * @throws UserNotFoundException Si el usuario no existe
     * @throws \Exception Si el email ya está verificado
     */
    public function resendVerificationEmail(Email $email): void
    {
        // 1. Buscar usuario
        $user = $this->userRepository->findByEmail($email);

        // 2. Verificar que el email no esté ya verificado
        if ($user->isEmailVerified()) {
            throw new \Exception('El email ya está verificado.');
        }

        // 3. Enviar nuevo email de verificación
        $this->sendVerificationEmail($email);
    }
}
