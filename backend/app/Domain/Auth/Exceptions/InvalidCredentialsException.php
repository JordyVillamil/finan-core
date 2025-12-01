<?php

namespace App\Domain\Auth\Exceptions;

use DomainException;

/**
 * Excepción para credenciales inválidas
 * 
 * Se lanza cuando:
 * - El password es incorrecto
 * - El email no coincide
 * - Las credenciales no son válidas
 * 
 * Hereda de DomainException (no de Exception) para indicar
 * que es un error del DOMINIO, no técnico.
 */
class InvalidCredentialsException extends DomainException
{
    /**
     * Constructor
     * 
     * @param string $message Mensaje personalizado
     */
    public function __construct(string $message = 'Las credenciales proporcionadas son inválidas')
    {
        parent::__construct($message, 401); // 401 = Unauthorized
    }

    /**
     * Factory method para password incorrecto
     */
    public static function incorrectPassword(): self
    {
        return new self('La contraseña es incorrecta');
    }

    /**
     * Factory method para email no encontrado
     */
    public static function emailNotFound(string $email): self
    {
        return new self("No se encontró un usuario con el email: {$email}");
    }
}