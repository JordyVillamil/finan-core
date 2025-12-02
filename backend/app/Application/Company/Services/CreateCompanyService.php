<?php

namespace App\Application\Company\Services;

use App\Application\Company\DTOs\CreateCompanyDTO;
use App\Application\Company\DTOs\CompanyResponseDTO;
use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\TaxId;
use App\Domain\Company\ValueObjects\Address;
use App\Domain\Company\ValueObjects\PhoneNumber;
use App\Domain\Shared\ValueObjects\Email;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\DuplicateTaxIdException;

/**
 * Service: CreateCompanyService
 * 
 * Caso de uso: Crear nueva empresa
 * 
 * RESPONSABILIDADES:
 * - Validar que el RFC/NIT no exista
 * - Crear la entidad Company
 * - Persistir en el repositorio
 * - Retornar DTO de respuesta
 * 
 * FLUJO:
 * 1. Verificar que RFC/NIT no esté duplicado
 * 2. Generar ID único
 * 3. Crear Value Objects
 * 4. Registrar entidad Company
 * 5. Guardar en repositorio
 * 6. Retornar respuesta
 */
class CreateCompanyService
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
     * @param CreateCompanyDTO $dto
     * @return CompanyResponseDTO
     * @throws DuplicateTaxIdException Si el RFC/NIT ya existe
     * @throws \InvalidArgumentException Si algún dato es inválido
     */
    public function execute(CreateCompanyDTO $dto): CompanyResponseDTO
    {
        // 1. Validar que RFC/NIT no exista
        $taxId = TaxId::fromString($dto->taxId);
        
        if ($this->companyRepository->existsTaxId($taxId)) {
            throw DuplicateTaxIdException::with($dto->taxId);
        }

        // 2. Generar ID único
        $companyId = $this->companyRepository->nextIdentity();

        // 3. Crear Value Objects
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

        // 4. Registrar entidad Company
        $company = Company::register(
            id: $companyId,
            name: $dto->name,
            legalName: $dto->legalName,
            taxId: $taxId,
            email: $email,
            address: $address,
            phone: $phone,
            taxRegime: $dto->taxRegime,
            invoiceSeries: $dto->invoiceSeries
        );

        // 5. Guardar en repositorio
        $this->companyRepository->save($company);

        // Nota: Aquí podríamos despachar eventos de dominio
        // Event::dispatch(new CompanyCreatedEvent($company));

        // 6. Retornar respuesta
        return CompanyResponseDTO::fromEntity($company);
    }
}