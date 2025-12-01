<?php

namespace App\Application\Auth\Services;

use App\Application\Auth\DTOs\RegisterUserDTO;
use App\Application\Auth\DTOs\UserResponseDTO;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Log;

/**
 * Service para registrar un nuevo usuario
 * 
 * CASO DE USO: "Registrar un nuevo usuario en el sistema"
 * 
 * FLUJO:
 * 1. Validar que el email no exista
 * 2. Crear entidad User (con validaciones del dominio)
 * 3. Guardar en la base de datos
 * 4. Retornar datos del usuario creado
 * 
 * RESPONSABILIDAD:
 * Orquestar el caso de uso, NO tiene lógica de negocio.
 * La lógica está en la entidad User.
 */
class RegisterUserService
{
    /**
     * Constructor - Inyección de dependencias
     * 
     * Laravel inyecta automáticamente el repository
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Ejecutar el caso de uso
     * 
     * @param RegisterUserDTO $dto Datos del usuario a registrar
     * @return UserResponseDTO Datos del usuario registrado
     * @throws InvalidCredentialsException Si el email ya existe
     */
    public function execute(RegisterUserDTO $dto): UserResponseDTO
    {
        // ============================================
        // 1. VALIDAR EMAIL ÚNICO
        // ============================================
        
        /**
         * Esta es una REGLA DE APLICACIÓN (no de negocio)
         * 
         * ¿Por qué?
         * - La entidad User no sabe si hay otros usuarios en la BD
         * - Es el Service quien debe verificar unicidad
         */
        $email = Email::fromString($dto->email);
        
        if ($this->userRepository->existsEmail($email)) {
            throw new InvalidCredentialsException(
                "El email {$dto->email} ya está registrado"
            );
        }

        // ============================================
        // 2. GENERAR ID
        // ============================================
        
        $userId = $this->userRepository->nextIdentity();

        // ============================================
        // 3. CREAR ENTIDAD USER
        // ============================================
        
        /**
         * Aquí es donde se ejecutan las REGLAS DE NEGOCIO:
         * - Nombre mínimo 2 caracteres
         * - Password mínimo 8 caracteres
         * - Email válido (en el Value Object)
         * - Hasheo de password
         */
        $user = User::register(
            id: $userId,
            name: $dto->name,
            email: $email,
            plainPassword: $dto->password
        );

        // ============================================
        // 4. PERSISTIR EN BASE DE DATOS
        // ============================================
        
        $this->userRepository->save($user);

        // ============================================
        // 5. ASIGNAR ROL POR DEFECTO
        // ============================================
        
        /**
         * Asignamos el rol "Usuario" por defecto a nuevos registros.
         * 
         * Para asignar roles, necesitamos el UserModel (Eloquent)
         * porque Spatie trabaja con Eloquent Models.
         */
        $userModel = \App\Infrastructure\Persistence\Eloquent\Models\UserModel::find($user->id()->value());
        
        if ($userModel) {
            // Asignar rol "Usuario" por defecto
            $userModel->assignRole('Usuario');
        }

        // ============================================
        // 6. PROCESAR EVENTOS DE DOMINIO
        // ============================================
        
        /**
         * Aquí podríamos procesar eventos como:
         * - Enviar email de bienvenida
         * - Crear log de auditoría
         * - Notificar a otros sistemas
         */
        $events = $user->pullDomainEvents();
        
        foreach ($events as $event) {
            // Por ahora solo logueamos
            // Más adelante implementaremos event handlers
            Log::info('Domain Event', $event);
            
            // Ejemplo de lo que podríamos hacer:
            // if ($event['event'] === 'UserRegistered') {
            //     Mail::to($user->email())->send(new WelcomeEmail($user));
            // }
        }

        // ============================================
        // 7. RETORNAR RESPUESTA
        // ============================================
        
        return UserResponseDTO::fromEntity($user);
    }
}