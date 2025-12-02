<?php

namespace App\Application\Company\Services;

use App\Application\Company\DTOs\CompanyResponseDTO;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;

/**
 * Service: GetCompanyService
 * 
 * Caso de uso: Obtener detalle de una empresa específica
 * 
 * RESPONSABILIDADES:
 * - Buscar empresa por ID
 * - Convertir a DTO de respuesta
 * 
 * FLUJO:
 * 1. Buscar empresa en repositorio
 * 2. Convertir a DTO
 * 3. Retornar respuesta
 */
class GetCompanyService
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
     * @throws CompanyNotFoundException Si la empresa no existe
     */
    public function execute(int $companyId): CompanyResponseDTO
    {
        // 1. Buscar empresa
        $company = $this->companyRepository->findById(
            CompanyId::fromInt($companyId)
        );

        // 2. Convertir a DTO y retornar
        return CompanyResponseDTO::fromEntity($company);
    }
}