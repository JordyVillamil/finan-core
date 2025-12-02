<?php

namespace App\Application\Company\DTOs;

/**
 * DTO: UpdateCompanyDTO
 * 
 * Datos para actualizar una empresa existente.
 * 
 * PROPÓSITO:
 * - Transferir datos de actualización
 * - Permitir actualizaciones parciales (campos opcionales)
 */
final readonly class UpdateCompanyDTO
{
    /**
     * Constructor
     * 
     * @param int $id ID de la empresa a actualizar
     * @param string $name
     * @param string $legalName
     * @param string $email
     * @param string $addressStreet
     * @param string $addressCity
     * @param string $addressState
     * @param string $addressCountry
     * @param string $addressPostalCode
     * @param string|null $phone
     * @param string|null $taxRegime
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $legalName,
        public string $email,
        public string $addressStreet,
        public string $addressCity,
        public string $addressState,
        public string $addressCountry,
        public string $addressPostalCode,
        public ?string $phone = null,
        public ?string $taxRegime = null,
    ) {}

    /**
     * Crear desde array
     * 
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            legalName: $data['legal_name'],
            email: $data['email'],
            addressStreet: $data['address_street'],
            addressCity: $data['address_city'],
            addressState: $data['address_state'],
            addressCountry: $data['address_country'] ?? 'México',
            addressPostalCode: $data['address_postal_code'] ?? '',
            phone: $data['phone'] ?? null,
            taxRegime: $data['tax_regime'] ?? null,
        );
    }

    /**
     * Convertir a array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legalName,
            'email' => $this->email,
            'address_street' => $this->addressStreet,
            'address_city' => $this->addressCity,
            'address_state' => $this->addressState,
            'address_country' => $this->addressCountry,
            'address_postal_code' => $this->addressPostalCode,
            'phone' => $this->phone,
            'tax_regime' => $this->taxRegime,
        ];
    }
}