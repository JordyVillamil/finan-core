<?php

namespace App\Domain\Company\Exceptions;

/**
 * Exception: CompanyNotFoundException
 * 
 * Se lanza cuando se busca una empresa que no existe.
 * 
 * PROPÓSITO:
 * - Hacer el código más expresivo
 * - Facilitar el manejo de errores
 * - Permitir diferentes mensajes según el contexto
 */
class CompanyNotFoundException extends \DomainException
{
    /**
     * Crear excepción por ID
     * 
     * @param int $companyId
     * @return self
     */
    public static function withId(int $companyId): self
    {
        return new self(
            "No se encontró la empresa con ID: {$companyId}",
            404
        );
    }

    /**
     * Crear excepción por RFC/NIT
     * 
     * @param string $taxId
     * @return self
     */
    public static function withTaxId(string $taxId): self
    {
        return new self(
            "No se encontró la empresa con RFC/NIT: {$taxId}",
            404
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
            'La empresa solicitada no existe',
            404
        );
    }
}