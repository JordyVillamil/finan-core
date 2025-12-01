<?php

namespace App\Domain\Company\Exceptions;

/**
 * Exception: InvalidTaxIdException
 * 
 * Se lanza cuando el RFC/NIT no es válido.
 * 
 * PROPÓSITO:
 * - Validación de negocio específica
 * - Mensajes claros sobre qué está mal
 */
class InvalidTaxIdException extends \DomainException
{
    /**
     * RFC con formato inválido
     * 
     * @param string $taxId
     * @return self
     */
    public static function invalidFormat(string $taxId): self
    {
        return new self(
            "El RFC/NIT '{$taxId}' tiene un formato inválido",
            400
        );
    }

    /**
     * RFC/NIT ya existe en el sistema
     * 
     * @param string $taxId
     * @return self
     */
    public static function alreadyExists(string $taxId): self
    {
        return new self(
            "El RFC/NIT '{$taxId}' ya está registrado en el sistema",
            409 // 409 Conflict
        );
    }

    /**
     * Dígito verificador inválido (para NIT)
     * 
     * @param string $nit
     * @return self
     */
    public static function invalidCheckDigit(string $nit): self
    {
        return new self(
            "El dígito verificador del NIT '{$nit}' es inválido",
            400
        );
    }
}