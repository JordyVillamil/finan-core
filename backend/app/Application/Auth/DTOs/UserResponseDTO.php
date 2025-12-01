<?php

namespace App\Application\Auth\DTOs;

use App\Domain\Auth\Entities\User;

/**
 * DTO para respuestas que contienen datos de usuario
 * 
 * PROPÓSITO:
 * Retornar datos del usuario desde el Service hacia el Controller.
 * NO exponemos la entidad completa, solo los datos necesarios.
 * 
 * VENTAJAS:
 * - Ocultamos datos sensibles (password)
 * - Solo exponemos lo que la API necesita
 * - Desacoplamos la entidad de la respuesta HTTP
 */
final readonly class UserResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isActive,
        public bool $isEmailVerified,
        public ?string $emailVerifiedAt,
        public string $createdAt,
    ) {}

    /**
     * Crear DTO desde una entidad User
     * 
     * @param User $user Entidad del dominio
     * @return self
     */
    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            isActive: $user->isActive(),
            isEmailVerified: $user->isEmailVerified(),
            emailVerifiedAt: $user->emailVerifiedAt()?->format('Y-m-d H:i:s'),
            createdAt: $user->createdAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Convertir a array (para JSON)
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'is_email_verified' => $this->isEmailVerified,
            'email_verified_at' => $this->emailVerifiedAt,
            'created_at' => $this->createdAt,
        ];
    }
}