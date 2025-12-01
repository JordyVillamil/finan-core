<?php

namespace App\Application\Auth\Services;

use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Exceptions\UserNotFoundException;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Service para reset de contraseña
 * 
 * CASOS DE USO:
 * 1. Solicitar reset de contraseña (enviar email)
 * 2. Restablecer contraseña con token
 */
class PasswordResetService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Solicitar reset de contraseña (enviar email)
     * 
     * @param Email $email Email del usuario
     * @return void
     * @throws UserNotFoundException Si el usuario no existe
     */
    public function sendResetEmail(Email $email): void
    {
        // ============================================
        // 1. VERIFICAR QUE EL USUARIO EXISTE
        // ============================================
        
        /**
         * Buscamos el usuario para obtener su nombre
         * Si no existe, lanzamos excepción
         */
        $user = $this->userRepository->findByEmail($email);

        // ============================================
        // 2. GENERAR TOKEN ÚNICO
        // ============================================
        
        /**
         * Token aleatorio de 64 caracteres
         */
        $plainToken = Str::random(64);
        
        /**
         * Guardamos el HASH (no el texto plano)
         */
        $hashedToken = hash('sha256', $plainToken);

        // ============================================
        // 3. GUARDAR TOKEN EN BD
        // ============================================
        
        /**
         * Eliminamos cualquier token anterior del mismo email
         * y creamos uno nuevo
         */
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email->value()],
            [
                'token' => $hashedToken,
                'created_at' => now(),
            ]
        );

        // ============================================
        // 4. GENERAR URL DE RESET
        // ============================================
        
        /**
         * URL: http://localhost/reset-password?token=xxx&email=xxx
         */
        $frontendUrl = config('app.frontend_url', 'http://localhost');
        $resetUrl = "{$frontendUrl}/reset-password?token={$plainToken}&email=" . urlencode($email->value());

        // ============================================
        // 5. ENVIAR EMAIL
        // ============================================
        
        Mail::to($email->value())
            ->send(new ResetPasswordMail($user->name(), $resetUrl));
    }

    /**
     * Restablecer contraseña con token
     * 
     * @param string $plainToken Token en texto plano
     * @param Email $email Email del usuario
     * @param string $newPassword Nueva contraseña
     * @return bool True si fue restablecida exitosamente
     * @throws \Exception Si el token es inválido, expiró o el usuario no existe
     */
    public function resetPassword(string $plainToken, Email $email, string $newPassword): bool
    {
        // ============================================
        // 1. HASHEAR TOKEN RECIBIDO
        // ============================================
        
        $hashedToken = hash('sha256', $plainToken);

        // ============================================
        // 2. BUSCAR TOKEN EN BD
        // ============================================
        
        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $email->value())
            ->where('token', $hashedToken)
            ->first();

        if (!$tokenRecord) {
            throw new \Exception('Token de reset inválido o email incorrecto');
        }

        // ============================================
        // 3. VERIFICAR EXPIRACIÓN (60 minutos)
        // ============================================
        
        $expirationTime = now()->subMinutes(60);
        
        if ($tokenRecord->created_at < $expirationTime) {
            // Eliminar token expirado
            DB::table('password_reset_tokens')
                ->where('email', $email->value())
                ->delete();
            
            throw new \Exception('El token ha expirado. Solicita uno nuevo.');
        }

        // ============================================
        // 4. BUSCAR USUARIO Y CAMBIAR CONTRASEÑA
        // ============================================
        
        try {
            $user = $this->userRepository->findByEmail($email);
            
            /**
             * IMPORTANTE: Aquí NO usamos changePassword() de la entidad
             * porque requiere la contraseña actual.
             * 
             * En reset, el usuario NO tiene la contraseña actual.
             * Por eso necesitamos un método especial o hacer el cambio directo.
             * 
             * Para mantener la arquitectura limpia, vamos a:
             * 1. Hashear la nueva contraseña
             * 2. Actualizar directamente en el Model
             */
            
            // Validar nueva contraseña
            if (strlen($newPassword) < 8) {
                throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
            }
            
            // Hashear nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Actualizar en BD directamente
            \App\Infrastructure\Persistence\Eloquent\Models\UserModel::where('id', $user->id()->value())
                ->update(['password' => $hashedPassword, 'updated_at' => now()]);
            
        } catch (UserNotFoundException $e) {
            throw new \Exception('Usuario no encontrado');
        }

        // ============================================
        // 5. ELIMINAR TOKEN USADO
        // ============================================
        
        /**
         * Una vez usado, el token se elimina
         * No puede reutilizarse
         */
        DB::table('password_reset_tokens')
            ->where('email', $email->value())
            ->delete();

        return true;
    }
}