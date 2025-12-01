<?php

namespace App\Domain\Auth\Exceptions;

use DomainException;

/**
 * Excepción cuando un usuario está desactivado
 * 
 * Se lanza cuando:
 * - Un usuario intenta hacer login pero está desactivado
 * - Se intenta realizar una acción con un usuario inactivo
 */
class UserNotActiveException extends DomainException
{
    public function __construct(string $message = 'El usuario está desactivado')
    {
        parent::__construct($message, 403); // 403 = Forbidden
    }

    /**
     * Usuario desactivado intentando hacer login
     */
    public static function cannotLogin(): self
    {
        return new self('No puedes iniciar sesión porque tu cuenta está desactivada. Contacta al administrador.');
    }
}