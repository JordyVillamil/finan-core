<?php

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para Email
 * 
 * Un Email no es solo un string, tiene reglas de validación.
 * Este objeto garantiza que siempre sea un email válido.
 */
final class Email
{
    private string $value;

    /**
     * Constructor privado - usar método estático fromString()
     */
    private function __construct(string $value)
    {
        // Normalizar primero (trim y lowercase)
        $value = strtolower(trim($value));

        // Verificar que no esté vacío
        if ($value === '') {
            throw new InvalidArgumentException("El email no puede estar vacío");
        }

        // Validar que sea un email válido
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El email no es válido: {$value}");
        }

        $this->value = $value;
    }

    /**
     * Crear Email desde un string
     * 
     * @param string $value El email en formato string
     * @return self Nueva instancia de Email
     * @throws InvalidArgumentException Si el email no es válido
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Obtener el valor del email
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Convertir a string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Comparar dos emails
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Método mágico para convertir a string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}