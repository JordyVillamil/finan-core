<?php

namespace App\Domain\Company\Exceptions;

/**
 * Exception: InactiveCompanyException
 * 
 * Se lanza cuando se intenta realizar una operación que requiere
 * que la empresa esté activa.
 * 
 * PROPÓSITO:
 * - Regla de negocio: solo empresas activas pueden operar
 * - Prevenir operaciones en empresas desactivadas
 */
class InactiveCompanyException extends \DomainException
{
    /**
     * No puede emitir facturas
     * 
     * @param int $companyId
     * @return self
     */
    public static function cannotIssueInvoices(int $companyId): self
    {
        return new self(
            "La empresa (ID: {$companyId}) no puede emitir facturas porque está inactiva",
            403 // 403 Forbidden
        );
    }

    /**
     * Operación no permitida
     * 
     * @param int $companyId
     * @param string $operation
     * @return self
     */
    public static function cannotPerformOperation(int $companyId, string $operation): self
    {
        return new self(
            "No se puede realizar '{$operation}' porque la empresa (ID: {$companyId}) está inactiva",
            403
        );
    }

    /**
     * Genérica
     * 
     * @return self
     */
    public static function generic(): self
    {
        return new self(
            'Esta operación no está permitida para empresas inactivas',
            403
        );
    }
}