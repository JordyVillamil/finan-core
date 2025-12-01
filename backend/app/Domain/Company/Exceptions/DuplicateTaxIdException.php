<?php

namespace App\Domain\Company\Exceptions;

/**
 * Exception: DuplicateTaxIdException
 * 
 * Se lanza cuando se intenta registrar una empresa con un RFC/NIT
 * que ya existe en el sistema.
 * 
 * PROPÓSITO:
 * - Garantizar unicidad de RFC/NIT
 * - Regla de negocio crítica
 */
class DuplicateTaxIdException extends \DomainException
{
    /**
     * Crear excepción con RFC/NIT duplicado
     * 
     * @param string $taxId RFC o NIT duplicado
     * @return self
     */
    public static function with(string $taxId): self
    {
        return new self(
            "Ya existe una empresa registrada con el RFC/NIT: {$taxId}",
            409 // 409 Conflict
        );
    }

    /**
     * Crear excepción genérica
     * 
     * @return self
     */
    public static function generic(): self
    {
        return new self(
            'El RFC/NIT ya está registrado en el sistema',
            409
        );
    }
}