<?php

namespace App\Application\Company\Services;

use App\Application\Company\DTOs\CompanyResponseDTO;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;

/**
 * Service: ListCompaniesService
 * 
 * Caso de uso: Listar empresas con paginación y filtros
 * 
 * RESPONSABILIDADES:
 * - Obtener empresas del repositorio
 * - Aplicar filtros
 * - Aplicar paginación
 * - Convertir a DTOs de respuesta
 * 
 * FLUJO:
 * 1. Obtener empresas del repositorio
 * 2. Convertir a DTOs
 * 3. Retornar respuesta con metadata de paginación
 */
class ListCompaniesService
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
     * @param int $page Número de página (empieza en 1)
     * @param int $perPage Elementos por página
     * @param bool|null $onlyActive Filtrar solo activas (null = todas)
     * @return array{data: array, meta: array}
     */
    public function execute(
        int $page = 1,
        int $perPage = 15,
        ?bool $onlyActive = null
    ): array {
        // 1. Obtener empresas del repositorio
        $companies = $this->companyRepository->findAll($page, $perPage, $onlyActive);
        
        // Obtener total para metadata
        $total = $this->companyRepository->count($onlyActive);

        // 2. Convertir a DTOs
        $companiesData = array_map(
            fn($company) => CompanyResponseDTO::fromEntity($company)->toArray(),
            $companies
        );

        // 3. Retornar con metadata de paginación
        return [
            'data' => $companiesData,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total),
            ],
        ];
    }
}