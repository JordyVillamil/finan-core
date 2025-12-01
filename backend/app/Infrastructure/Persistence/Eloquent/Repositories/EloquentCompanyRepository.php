<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\TaxId;
use App\Domain\Company\ValueObjects\Address;
use App\Domain\Company\ValueObjects\PhoneNumber;
use App\Domain\Shared\ValueObjects\Email;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\CompanyModel;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Company Repository
 * 
 * Implementación del CompanyRepositoryInterface usando Eloquent ORM.
 * 
 * RESPONSABILIDADES:
 * - Convertir entre Entity (Domain) y Model (Eloquent)
 * - Ejecutar queries usando Eloquent
 * - Manejar persistencia en PostgreSQL
 * 
 * PATRÓN: Repository Pattern + Mapper Pattern
 */
class EloquentCompanyRepository implements CompanyRepositoryInterface
{
    /**
     * Constructor
     */
    public function __construct(
        private CompanyModel $model
    ) {}

    /**
     * Buscar empresa por ID
     * 
     * @param CompanyId $id
     * @return Company
     * @throws CompanyNotFoundException
     */
    public function findById(CompanyId $id): Company
    {
        $companyModel = $this->model->find($id->value());

        if (!$companyModel) {
            throw CompanyNotFoundException::withId($id->value());
        }

        return $this->mapToDomain($companyModel);
    }

    /**
     * Buscar empresa por RFC/NIT
     * 
     * @param TaxId $taxId
     * @return Company
     * @throws CompanyNotFoundException
     */
    public function findByTaxId(TaxId $taxId): Company
    {
        $companyModel = $this->model
            ->where('tax_id', $taxId->value())
            ->first();

        if (!$companyModel) {
            throw CompanyNotFoundException::withTaxId($taxId->value());
        }

        return $this->mapToDomain($companyModel);
    }

    /**
     * Verificar si existe empresa con ese RFC/NIT
     * 
     * @param TaxId $taxId
     * @return bool
     */
    public function existsTaxId(TaxId $taxId): bool
    {
        return $this->model
            ->where('tax_id', $taxId->value())
            ->exists();
    }

    /**
     * Guardar empresa (crear o actualizar)
     * 
     * @param Company $company
     * @return void
     */
    public function save(Company $company): void
    {
        $companyModel = $this->model->find($company->id()->value());

        if ($companyModel) {
            // Actualizar existente
            $this->updateModel($companyModel, $company);
        } else {
            // Crear nuevo
            $this->createModel($company);
        }
    }

    /**
     * Eliminar empresa (soft delete)
     * 
     * @param CompanyId $id
     * @return void
     * @throws CompanyNotFoundException
     */
    public function delete(CompanyId $id): void
    {
        $companyModel = $this->model->find($id->value());

        if (!$companyModel) {
            throw CompanyNotFoundException::withId($id->value());
        }

        $companyModel->delete(); // Soft delete
    }

    /**
     * Obtener todas las empresas (con paginación)
     * 
     * @param int $page
     * @param int $perPage
     * @param bool|null $onlyActive
     * @return array<Company>
     */
    public function findAll(int $page = 1, int $perPage = 15, ?bool $onlyActive = null): array
    {
        $query = $this->model->newQuery();

        // Filtrar por estado activo/inactivo
        if ($onlyActive === true) {
            $query->where('is_active', true);
        } elseif ($onlyActive === false) {
            $query->where('is_active', false);
        }

        $companyModels = $query
            ->orderBy('name', 'asc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $companyModels->map(function ($companyModel) {
            return $this->mapToDomain($companyModel);
        })->toArray();
    }

    /**
     * Contar empresas
     * 
     * @param bool|null $onlyActive
     * @return int
     */
    public function count(?bool $onlyActive = null): int
    {
        $query = $this->model->newQuery();

        if ($onlyActive === true) {
            $query->where('is_active', true);
        } elseif ($onlyActive === false) {
            $query->where('is_active', false);
        }

        return $query->count();
    }

    /**
     * Generar siguiente ID disponible
     * 
     * @return CompanyId
     */
    public function nextIdentity(): CompanyId
    {
        // En PostgreSQL, el ID se genera automáticamente (SERIAL)
        // Obtenemos el próximo valor de la secuencia
        $result = DB::selectOne("SELECT nextval('companies_id_seq') as next_id");
        
        return CompanyId::fromInt((int) $result->next_id);
    }

    /**
     * Buscar empresas activas
     * 
     * @param int $page
     * @param int $perPage
     * @return array<Company>
     */
    public function findActiveCompanies(int $page = 1, int $perPage = 15): array
    {
        return $this->findAll($page, $perPage, true);
    }

    /**
     * Buscar empresas por nombre (búsqueda parcial)
     * 
     * @param string $searchTerm
     * @param int $page
     * @param int $perPage
     * @return array<Company>
     */
    public function searchByName(string $searchTerm, int $page = 1, int $perPage = 15): array
    {
        $companyModels = $this->model
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('legal_name', 'ILIKE', "%{$searchTerm}%");
            })
            ->orderBy('name', 'asc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $companyModels->map(function ($companyModel) {
            return $this->mapToDomain($companyModel);
        })->toArray();
    }

    /**
     * Obtener empresas por usuario
     * 
     * @param int $userId
     * @return array<Company>
     */
    public function findByUserId(int $userId): array
    {
        $companyModels = $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('name', 'asc')
            ->get();

        return $companyModels->map(function ($companyModel) {
            return $this->mapToDomain($companyModel);
        })->toArray();
    }

    // ============================================
    // MAPPERS (Model ↔ Entity)
    // ============================================

    /**
     * Convertir CompanyModel (Eloquent) a Company (Entity)
     * 
     * @param CompanyModel $model
     * @return Company
     */
    private function mapToDomain(CompanyModel $model): Company
    {
        // Crear Value Objects
        $address = Address::create(
            $model->address_street ?? '',
            $model->address_city ?? '',
            $model->address_state ?? '',
            $model->address_country,
            $model->address_postal_code ?? ''
        );

        $phone = null;
        if ($model->phone) {
            try {
                $phone = PhoneNumber::fromString($model->phone);
            } catch (\InvalidArgumentException $e) {
                // Si el teléfono en BD es inválido, lo ignoramos
                $phone = null;
            }
        }

        // Reconstituir la entidad
        return Company::reconstitute(
            id: CompanyId::fromInt($model->id),
            name: $model->name,
            legalName: $model->legal_name,
            taxId: TaxId::fromString($model->tax_id),
            email: Email::fromString($model->email),
            address: $address,
            phone: $phone,
            taxRegime: $model->tax_regime,
            logoPath: $model->logo_path,
            invoiceSeries: $model->invoice_series,
            nextInvoiceNumber: $model->next_invoice_number,
            isActive: $model->is_active,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at)
        );
    }

    /**
     * Crear nuevo CompanyModel desde Company Entity
     * 
     * @param Company $company
     * @return CompanyModel
     */
    private function createModel(Company $company): CompanyModel
    {
        $companyModel = new CompanyModel();
        $companyModel->id = $company->id()->value();
        
        $this->fillModelFromEntity($companyModel, $company);
        
        $companyModel->save();

        return $companyModel;
    }

    /**
     * Actualizar CompanyModel existente desde Company Entity
     * 
     * @param CompanyModel $model
     * @param Company $company
     * @return void
     */
    private function updateModel(CompanyModel $model, Company $company): void
    {
        $this->fillModelFromEntity($model, $company);
        $model->save();
    }

    /**
     * Llenar modelo con datos de la entidad
     * 
     * @param CompanyModel $model
     * @param Company $company
     * @return void
     */
    private function fillModelFromEntity(CompanyModel $model, Company $company): void
    {
        $model->name = $company->name();
        $model->legal_name = $company->legalName();
        $model->tax_id = $company->taxId()->value();
        $model->email = $company->email()->value();
        $model->phone = $company->phone()?->value();
        
        // Dirección
        $model->address_street = $company->address()->street();
        $model->address_city = $company->address()->city();
        $model->address_state = $company->address()->state();
        $model->address_country = $company->address()->country();
        $model->address_postal_code = $company->address()->postalCode();
        
        // Configuración fiscal
        $model->tax_regime = $company->taxRegime();
        $model->logo_path = $company->logoPath();
        $model->invoice_series = $company->invoiceSeries();
        $model->next_invoice_number = $company->nextInvoiceNumber();
        
        // Estado
        $model->is_active = $company->isActive();
    }
}