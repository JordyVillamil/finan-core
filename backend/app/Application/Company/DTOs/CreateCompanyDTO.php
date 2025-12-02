<?php

namespace App\Application\Company\DTOs;

/**
 * DTO: CreateCompanyDTO
 * 
 * Datos necesarios para crear una nueva empresa.
 * 
 * PROPÓSITO:
 * - Transferir datos desde la capa de presentación a la aplicación
 * - Validación ya realizada en Form Request
 * - Objeto simple sin lógica de negocio
 */
final readonly class CreateCompanyDTO
{
    /**
     * Constructor
     * 
     * @param string $name Nombre comercial
     * @param string $legalName Razón social
     * @param string $taxId RFC o NIT
     * @param string $email Email de contacto
     * @param string $addressStreet Calle y número
     * @param string $addressCity Ciudad
     * @param string $addressState Estado/Provincia
     * @param string $addressCountry País
     * @param string $addressPostalCode Código postal
     * @param string|null $phone Teléfono (opcional)
     * @param string|null $taxRegime Régimen fiscal (opcional)
     * @param string $invoiceSeries Serie de facturación (default: 'A')
     */
    public function __construct(
        public string $name,
        public string $legalName,
        public string $taxId,
        public string $email,
        public string $addressStreet,
        public string $addressCity,
        public string $addressState,
        public string $addressCountry,
        public string $addressPostalCode,
        public ?string $phone = null,
        public ?string $taxRegime = null,
        public string $invoiceSeries = 'A',
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
            name: $data['name'],
            legalName: $data['legal_name'],
            taxId: $data['tax_id'],
            email: $data['email'],
            addressStreet: $data['address_street'],
            addressCity: $data['address_city'],
            addressState: $data['address_state'],
            addressCountry: $data['address_country'] ?? 'México',
            addressPostalCode: $data['address_postal_code'] ?? '',
            phone: $data['phone'] ?? null,
            taxRegime: $data['tax_regime'] ?? null,
            invoiceSeries: $data['invoice_series'] ?? 'A',
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
            'name' => $this->name,
            'legal_name' => $this->legalName,
            'tax_id' => $this->taxId,
            'email' => $this->email,
            'address_street' => $this->addressStreet,
            'address_city' => $this->addressCity,
            'address_state' => $this->addressState,
            'address_country' => $this->addressCountry,
            'address_postal_code' => $this->addressPostalCode,
            'phone' => $this->phone,
            'tax_regime' => $this->taxRegime,
            'invoice_series' => $this->invoiceSeries,
        ];
    }
}