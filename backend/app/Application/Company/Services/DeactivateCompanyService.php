<?php

namespace App\Application\Company\Services;

use App\Application\Company\DTOs\CompanyResponseDTO;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;

/**
 * Service: DeactivateCompanyService
 * 
 * Caso de uso: Desactivar una empresa activa
 * 
 * RESPONSABILIDADES:
 * - Buscar empresa
 * - Desactivarla
 * - Guardar cambios
 * - Retornar respuesta
 * 
 * REGLA DE NEGOCIO:
 * Una empresa desactivada no puede emitir facturas.
 */
class DeactivateCompanyService
{
    /**
     * Constructor
     */
    public function __construct(
        private CompanyRepositoryInterface $companyRepository
    ) {}

    /**
     * Ejecutar caso de uso
     * 
     * @param int $companyId
     * @return CompanyResponseDTO
     * @throws CompanyNotFoundException
     */
    public function execute(int $companyId): CompanyResponseDTO
    {
        // Buscar empresa
        $company = $this->companyRepository->findById(
            CompanyId::fromInt($companyId)
        );

        // Desactivar
        $company->deactivate();

        // Guardar cambios
        $this->companyRepository->save($company);

        // Retornar respuesta
        return CompanyResponseDTO::fromEntity($company);
    }
}