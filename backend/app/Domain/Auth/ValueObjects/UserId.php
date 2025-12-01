<?php

namespace App\Domain\Auth\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para ID de Usuario
 * 
 * Representa el identificador único de un usuario.
 * Puede ser un número entero o UUID según tu diseño.
 */
final class UserId
{
    private int $value;

    /**
     * Constructor privado
     */
    private function __construct(int $value)
    {
        // Validar que sea un ID positivo
        if ($value <= 0) {
            throw new InvalidArgumentException("UserId debe ser un número positivo, recibido: {$value}");
        }

        $this->value = $value;
    }

    /**
     * Crear UserId desde un entero
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    /**
     * Crear UserId desde un string (útil al venir de la BD)
     */
    public static function fromString(string $value): self
    {
        $intValue = (int) $value;
        
        if ((string) $intValue !== $value) {
            throw new InvalidArgumentException("UserId inválido: {$value}");
        }
        
        return new self($intValue);
    }

    /**
     * Obtener el valor del ID
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Convertir a string
     */
    public function toString(): string
    {
        return (string) $this->value;
    }

    /**
     * Comparar dos IDs
     */
    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Método mágico para convertir a string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}