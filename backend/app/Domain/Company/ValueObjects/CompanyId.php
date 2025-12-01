<?php

namespace App\Domain\Company\ValueObjects;

/**
 * Value Object: CompanyId
 * 
 * Representa el identificador único de una empresa.
 * 
 * PROPÓSITO:
 * - Encapsular la lógica de validación del ID
 * - Evitar usar int directamente (type safety)
 * - Hacer el código más expresivo
 * 
 * REGLAS DE NEGOCIO:
 * - El ID debe ser un número entero positivo
 * - No puede ser 0 o negativo
 */
final class CompanyId
{
    /**
     * Constructor privado para forzar uso de factory methods
     */
    private function __construct(
        private int $value
    ) {
        $this->validate();
    }

    /**
     * Crear desde un entero
     * 
     * @param int $value ID de la empresa
     * @return self
     * @throws \InvalidArgumentException Si el ID no es válido
     */
    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    /**
     * Crear desde un string
     * 
     * @param string $value ID de la empresa como string
     * @return self
     * @throws \InvalidArgumentException Si no es un número válido
     */
    public static function fromString(string $value): self
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('El ID de empresa debe ser numérico');
        }

        return new self((int) $value);
    }

    /**
     * Obtener el valor del ID
     * 
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Validar el ID
     * 
     * @throws \InvalidArgumentException Si el ID no es válido
     */
    private function validate(): void
    {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException(
                'El ID de empresa debe ser un número positivo'
            );
        }
    }

    /**
     * Comparar con otro CompanyId
     * 
     * @param CompanyId $other
     * @return bool True si son iguales
     */
    public function equals(CompanyId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Representación en string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}