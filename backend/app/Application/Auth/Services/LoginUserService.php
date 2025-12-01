<?php

namespace App\Application\Auth\Services;

use App\Application\Auth\DTOs\LoginUserDTO;
use App\Application\Auth\DTOs\UserResponseDTO;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Exceptions\UserNotFoundException;
use App\Domain\Auth\Exceptions\UserNotActiveException;

/**
 * Service para login de usuario
 * 
 * CASO DE USO: "Autenticar usuario en el sistema"
 * 
 * FLUJO:
 * 1. Buscar usuario por email
 * 2. Verificar que existe
 * 3. Verificar que está activo
 * 4. Verificar contraseña
 * 5. Generar token de autenticación
 * 6. Retornar usuario y token
 */
class LoginUserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Ejecutar el caso de uso
     * 
     * @param LoginUserDTO $dto Credenciales de login
     * @return array ['user' => UserResponseDTO, 'token' => string]
     * @throws InvalidCredentialsException Si las credenciales son incorrectas
     * @throws UserNotActiveException Si el usuario está desactivado
     */
    public function execute(LoginUserDTO $dto): array
    {
        // ============================================
        // 1. BUSCAR USUARIO POR EMAIL
        // ============================================
        
        $email = Email::fromString($dto->email);
        
        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (UserNotFoundException $e) {
            // NO revelamos si el email existe o no (seguridad)
            // Siempre el mismo mensaje genérico
            throw InvalidCredentialsException::emailNotFound($dto->email);
        }

        // ============================================
        // 2. VERIFICAR QUE ESTÁ ACTIVO
        // ============================================
        
        if (!$user->isActive()) {
            throw UserNotActiveException::cannotLogin();
        }

        // ============================================
        // 3. VERIFICAR CONTRASEÑA
        // ============================================
        
        /**
         * La lógica de verificación está en la ENTIDAD
         * El service solo ORQUESTA
         */
        if (!$user->verifyPassword($dto->password)) {
            throw InvalidCredentialsException::incorrectPassword();
        }

        // ============================================
        // 4. GENERAR TOKEN DE AUTENTICACIÓN
        // ============================================
        
        /**
         * Usamos Laravel Sanctum para generar tokens
         * 
         * Sanctum genera tokens como:
         * "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
         * 
         * Estos tokens:
         * - Se guardan hasheados en la tabla personal_access_tokens
         * - Tienen nombre (ej: "web", "mobile")
         * - Tienen permisos opcionales
         * - Tienen expiración opcional
         */
        
        // Obtener el UserModel de Eloquent (Sanctum trabaja con Eloquent)
        $userModel = \App\Infrastructure\Persistence\Eloquent\Models\UserModel::find($user->id()->value());
        
        if (!$userModel) {
            throw new \RuntimeException('No se pudo obtener el modelo de usuario');
        }
        
        // Generar token con Sanctum
        $token = $userModel->createToken('web')->plainTextToken;

        // ============================================
        // 5. RETORNAR RESPUESTA
        // ============================================
        
        return [
            'user' => UserResponseDTO::fromEntity($user),
            'token' => $token,
        ];
    }
}