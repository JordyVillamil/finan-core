<?php

namespace App\Domain\Company\Repositories;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\TaxId;
use App\Domain\Company\Exceptions\CompanyNotFoundException;

/**
 * Interface: CompanyRepositoryInterface
 * 
 * Define el contrato para persistencia de empresas.
 * 
 * PROPÓSITO:
 * - Desacoplar el dominio de la infraestructura
 * - Permitir cambiar la implementación sin afectar el dominio
 * - Facilitar testing (mocks)
 * 
 * PATRÓN: Repository Pattern
 * 
 * IMPLEMENTACIONES POSIBLES:
 * - EloquentCompanyRepository (Laravel/PostgreSQL)
 * - InMemoryCompanyRepository (Testing)
 * - ApiCompanyRepository (Microservicios)
 */
interface CompanyRepositoryInterface
{
    /**
     * Buscar empresa por ID
     * 
     * @param CompanyId $id
     * @return Company
     * @throws CompanyNotFoundException Si no existe
     */
    public function findById(CompanyId $id): Company;

    /**
     * Buscar empresa por RFC/NIT
     * 
     * @param TaxId $taxId
     * @return Company
     * @throws CompanyNotFoundException Si no existe
     */
    public function findByTaxId(TaxId $taxId): Company;

    /**
     * Verificar si existe empresa con ese RFC/NIT
     * 
     * @param TaxId $taxId
     * @return bool
     */
    public function existsTaxId(TaxId $taxId): bool;

    /**
     * Guardar empresa (crear o actualizar)
     * 
     * @param Company $company
     * @return void
     */
    public function save(Company $company): void;

    /**
     * Eliminar empresa (soft delete)
     * 
     * @param CompanyId $id
     * @return void
     * @throws CompanyNotFoundException Si no existe
     */
    public function delete(CompanyId $id): void;

    /**
     * Obtener todas las empresas (con paginación)
     * 
     * @param int $page Número de página (empieza en 1)
     * @param int $perPage Elementos por página
     * @param bool|null $onlyActive Solo empresas activas (null = todas)
     * @return array<Company> Array de empresas
     */
    public function findAll(int $page = 1, int $perPage = 15, ?bool $onlyActive = null): array;

    /**
     * Contar empresas
     * 
     * @param bool|null $onlyActive Solo contar activas (null = todas)
     * @return int
     */
    public function count(?bool $onlyActive = null): int;

    /**
     * Generar siguiente ID disponible
     * 
     * @return CompanyId
     */
    public function nextIdentity(): CompanyId;

    /**
     * Buscar empresas activas
     * 
     * @param int $page
     * @param int $perPage
     * @return array<Company>
     */
    public function findActiveCompanies(int $page = 1, int $perPage = 15): array;

    /**
     * Buscar empresas por nombre (búsqueda parcial)
     * 
     * @param string $searchTerm Término de búsqueda
     * @param int $page
     * @param int $perPage
     * @return array<Company>
     */
    public function searchByName(string $searchTerm, int $page = 1, int $perPage = 15): array;

    /**
     * Obtener empresas por usuario
     * 
     * Las empresas a las que pertenece un usuario específico.
     * 
     * @param int $userId ID del usuario
     * @return array<Company>
     */
    public function findByUserId(int $userId): array;
}