<?php

namespace App\Application\Company\DTOs;

use App\Domain\Company\Entities\Company;

/**
 * DTO: CompanyResponseDTO
 * 
 * Formato de respuesta para enviar al frontend.
 * 
 * PROPÓSITO:
 * - Serializar Entity a formato JSON
 * - Controlar qué datos se exponen al cliente
 * - Formato consistente de respuesta
 */
final readonly class CompanyResponseDTO
{
    /**
     * Constructor
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $legalName,
        public string $taxId,
        public string $taxIdFormatted,
        public string $email,
        public ?string $phone,
        public array $address,
        public ?string $taxRegime,
        public ?string $logoPath,
        public string $invoiceSeries,
        public int $nextInvoiceNumber,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    /**
     * Crear desde Entity
     * 
     * @param Company $company
     * @return self
     */
    public static function fromEntity(Company $company): self
    {
        return new self(
            id: $company->id()->value(),
            name: $company->name(),
            legalName: $company->legalName(),
            taxId: $company->taxId()->value(),
            taxIdFormatted: $company->taxId()->formatted(),
            email: $company->email()->value(),
            phone: $company->phone()?->value(),
            address: [
                'street' => $company->address()->street(),
                'city' => $company->address()->city(),
                'state' => $company->address()->state(),
                'country' => $company->address()->country(),
                'postal_code' => $company->address()->postalCode(),
                'full_address' => $company->address()->oneLine(),
            ],
            taxRegime: $company->taxRegime(),
            logoPath: $company->logoPath(),
            invoiceSeries: $company->invoiceSeries(),
            nextInvoiceNumber: $company->nextInvoiceNumber(),
            isActive: $company->isActive(),
            createdAt: $company->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $company->updatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Crear desde múltiples entities
     * 
     * @param array<Company> $companies
     * @return array<self>
     */
    public static function fromEntities(array $companies): array
    {
        return array_map(
            fn(Company $company) => self::fromEntity($company),
            $companies
        );
    }

    /**
     * Convertir a array (para JSON)
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legalName,
            'tax_id' => $this->taxId,
            'tax_id_formatted' => $this->taxIdFormatted,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'tax_regime' => $this->taxRegime,
            'logo_path' => $this->logoPath,
            'invoice_series' => $this->invoiceSeries,
            'next_invoice_number' => $this->nextInvoiceNumber,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Convertir a JSON
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}