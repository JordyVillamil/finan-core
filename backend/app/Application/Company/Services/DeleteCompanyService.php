<?php

namespace App\Application\Company\Services;

use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;

/**
 * Service: DeleteCompanyService
 * 
 * Caso de uso: Eliminar empresa (soft delete)
 * 
 * RESPONSABILIDADES:
 * - Verificar que la empresa existe
 * - Eliminar (soft delete)
 * 
 * FLUJO:
 * 1. Verificar existencia
 * 2. Eliminar del repositorio
 * 
 * NOTA:
 * Es soft delete, la empresa no se borra físicamente.
 * Solo se marca con deleted_at.
 */
class DeleteCompanyService
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
     * @return void
     * @throws CompanyNotFoundException Si la empresa no existe
     */
    public function execute(int $companyId): void
    {
        $id = CompanyId::fromInt($companyId);
        
        // Verificar que existe (lanza excepción si no existe)
        $this->companyRepository->findById($id);
        
        // Eliminar (soft delete)
        $this->companyRepository->delete($id);
    }
}