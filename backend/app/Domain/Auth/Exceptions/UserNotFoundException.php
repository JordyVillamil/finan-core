<?php

namespace App\Domain\Auth\Exceptions;

use DomainException;

/**
 * Excepción cuando no se encuentra un usuario
 * 
 * Se lanza cuando:
 * - Buscamos un usuario por ID y no existe
 * - Buscamos un usuario por email y no existe
 */
class UserNotFoundException extends DomainException
{
    public function __construct(string $message = 'Usuario no encontrado')
    {
        parent::__construct($message, 404); // 404 = Not Found
    }

    /**
     * Usuario no encontrado por ID
     */
    public static function withId(int $id): self
    {
        return new self("No se encontró un usuario con ID: {$id}");
    }

    /**
     * Usuario no encontrado por email
     */
    public static function withEmail(string $email): self
    {
        return new self("No se encontró un usuario con email: {$email}");
    }
}