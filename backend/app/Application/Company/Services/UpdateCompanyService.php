<?php

namespace App\Application\Company\Services;

use App\Application\Company\DTOs\UpdateCompanyDTO;
use App\Application\Company\DTOs\CompanyResponseDTO;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\Address;
use App\Domain\Company\ValueObjects\PhoneNumber;
use App\Domain\Shared\ValueObjects\Email;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;

/**
 * Service: UpdateCompanyService
 * 
 * Caso de uso: Actualizar información de empresa existente
 * 
 * RESPONSABILIDADES:
 * - Buscar empresa existente
 * - Actualizar información
 * - Persistir cambios
 * - Retornar respuesta
 * 
 * FLUJO:
 * 1. Buscar empresa por ID
 * 2. Crear Value Objects actualizados
 * 3. Actualizar información en la entidad
 * 4. Guardar cambios
 * 5. Retornar respuesta
 */
class UpdateCompanyService
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
     * @param UpdateCompanyDTO $dto
     * @return CompanyResponseDTO
     * @throws CompanyNotFoundException Si la empresa no existe
     * @throws \InvalidArgumentException Si algún dato es inválido
     */
    public function execute(UpdateCompanyDTO $dto): CompanyResponseDTO
    {
        // 1. Buscar empresa existente
        $companyId = CompanyId::fromInt($dto->id);
        $company = $this->companyRepository->findById($companyId);

        // 2. Crear Value Objects actualizados
        $email = Email::fromString($dto->email);
        
        $address = Address::create(
            $dto->addressStreet,
            $dto->addressCity,
            $dto->addressState,
            $dto->addressCountry,
            $dto->addressPostalCode
        );

        $phone = null;
        if ($dto->phone) {
            $phone = PhoneNumber::fromString($dto->phone);
        }

        // 3. Actualizar información en la entidad
        $company->updateInfo(
            name: $dto->name,
            legalName: $dto->legalName,
            email: $email,
            address: $address,
            phone: $phone,
            taxRegime: $dto->taxRegime
        );

        // 4. Guardar cambios
        $this->companyRepository->save($company);

        // 5. Retornar respuesta
        return CompanyResponseDTO::fromEntity($company);
    }
}