<?php

namespace App\Domain\Company\Entities;

use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\TaxId;
use App\Domain\Company\ValueObjects\Address;
use App\Domain\Company\ValueObjects\PhoneNumber;
use App\Domain\Shared\ValueObjects\Email;

/**
 * Entity: Company
 * 
 * Representa una empresa en el sistema.
 * 
 * AGREGADO: Company es un agregado raíz
 * - Controla su propia consistencia
 * - Emite eventos de dominio
 * 
 * REGLAS DE NEGOCIO:
 * - Nombre y razón social obligatorios
 * - RFC/NIT único en el sistema
 * - Email válido
 * - Solo empresas activas pueden emitir facturas
 * - Serie de facturación única por empresa
 */
class Company
{
    private CompanyId $id;
    private string $name;
    private string $legalName;
    private TaxId $taxId;
    private Email $email;
    private ?PhoneNumber $phone;
    private Address $address;
    private ?string $taxRegime;
    private ?string $logoPath;
    private string $invoiceSeries;
    private int $nextInvoiceNumber;
    private bool $isActive;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private array $domainEvents = [];

    /**
     * Constructor privado para forzar uso de factory methods
     */
    private function __construct(
        CompanyId $id,
        string $name,
        string $legalName,
        TaxId $taxId,
        Email $email,
        Address $address,
        ?PhoneNumber $phone = null,
        ?string $taxRegime = null,
        ?string $logoPath = null,
        string $invoiceSeries = 'A',
        int $nextInvoiceNumber = 1,
        bool $isActive = true,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->legalName = $legalName;
        $this->taxId = $taxId;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->taxRegime = $taxRegime;
        $this->logoPath = $logoPath;
        $this->invoiceSeries = $invoiceSeries;
        $this->nextInvoiceNumber = $nextInvoiceNumber;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();

        $this->validate();
    }

    /**
     * Registrar nueva empresa
     * 
     * @param CompanyId $id
     * @param string $name Nombre comercial
     * @param string $legalName Razón social
     * @param TaxId $taxId RFC o NIT
     * @param Email $email
     * @param Address $address
     * @param PhoneNumber|null $phone
     * @param string|null $taxRegime Régimen fiscal
     * @param string $invoiceSeries Serie de facturación (default: 'A')
     * @return self
     */
    public static function register(
        CompanyId $id,
        string $name,
        string $legalName,
        TaxId $taxId,
        Email $email,
        Address $address,
        ?PhoneNumber $phone = null,
        ?string $taxRegime = null,
        string $invoiceSeries = 'A'
    ): self {
        $company = new self(
            id: $id,
            name: $name,
            legalName: $legalName,
            taxId: $taxId,
            email: $email,
            address: $address,
            phone: $phone,
            taxRegime: $taxRegime,
            invoiceSeries: $invoiceSeries
        );

        // Emitir evento de dominio
        $company->recordDomainEvent([
            'event' => 'CompanyRegistered',
            'companyId' => $id->value(),
            'name' => $name,
            'taxId' => $taxId->value(),
            'occurredAt' => new \DateTimeImmutable(),
        ]);

        return $company;
    }

    /**
     * Reconstituir empresa desde persistencia
     * 
     * Este método se usa cuando traemos la empresa de la BD.
     * NO emite eventos de dominio.
     * 
     * @param CompanyId $id
     * @param string $name
     * @param string $legalName
     * @param TaxId $taxId
     * @param Email $email
     * @param Address $address
     * @param PhoneNumber|null $phone
     * @param string|null $taxRegime
     * @param string|null $logoPath
     * @param string $invoiceSeries
     * @param int $nextInvoiceNumber
     * @param bool $isActive
     * @param \DateTimeImmutable $createdAt
     * @param \DateTimeImmutable $updatedAt
     * @return self
     */
    public static function reconstitute(
        CompanyId $id,
        string $name,
        string $legalName,
        TaxId $taxId,
        Email $email,
        Address $address,
        ?PhoneNumber $phone,
        ?string $taxRegime,
        ?string $logoPath,
        string $invoiceSeries,
        int $nextInvoiceNumber,
        bool $isActive,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            legalName: $legalName,
            taxId: $taxId,
            email: $email,
            address: $address,
            phone: $phone,
            taxRegime: $taxRegime,
            logoPath: $logoPath,
            invoiceSeries: $invoiceSeries,
            nextInvoiceNumber: $nextInvoiceNumber,
            isActive: $isActive,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    /**
     * Validar datos de la empresa
     * 
     * @throws \InvalidArgumentException Si hay datos inválidos
     */
    private function validate(): void
    {
        // Nombre comercial
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('El nombre de la empresa es obligatorio');
        }

        if (strlen($this->name) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres');
        }

        if (strlen($this->name) > 255) {
            throw new \InvalidArgumentException('El nombre no puede exceder 255 caracteres');
        }

        // Razón social
        if (empty(trim($this->legalName))) {
            throw new \InvalidArgumentException('La razón social es obligatoria');
        }

        if (strlen($this->legalName) < 2) {
            throw new \InvalidArgumentException('La razón social debe tener al menos 2 caracteres');
        }

        if (strlen($this->legalName) > 255) {
            throw new \InvalidArgumentException('La razón social no puede exceder 255 caracteres');
        }

        // Serie de facturación
        if (empty($this->invoiceSeries)) {
            throw new \InvalidArgumentException('La serie de facturación es obligatoria');
        }

        if (!preg_match('/^[A-Z0-9]{1,10}$/', $this->invoiceSeries)) {
            throw new \InvalidArgumentException(
                'La serie de facturación debe ser alfanumérica mayúscula (1-10 caracteres)'
            );
        }

        // Próximo número de factura
        if ($this->nextInvoiceNumber < 1) {
            throw new \InvalidArgumentException(
                'El próximo número de factura debe ser mayor a 0'
            );
        }

        // Régimen fiscal (si existe)
        if ($this->taxRegime !== null && strlen($this->taxRegime) > 100) {
            throw new \InvalidArgumentException(
                'El régimen fiscal no puede exceder 100 caracteres'
            );
        }
    }

    // ============================================
    // GETTERS
    // ============================================

    public function id(): CompanyId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function legalName(): string
    {
        return $this->legalName;
    }

    public function taxId(): TaxId
    {
        return $this->taxId;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function phone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function address(): Address
    {
        return $this->address;
    }

    public function taxRegime(): ?string
    {
        return $this->taxRegime;
    }

    public function logoPath(): ?string
    {
        return $this->logoPath;
    }

    public function invoiceSeries(): string
    {
        return $this->invoiceSeries;
    }

    public function nextInvoiceNumber(): int
    {
        return $this->nextInvoiceNumber;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ============================================
    // MÉTODOS DE NEGOCIO
    // ============================================

    /**
     * Actualizar información básica
     * 
     * @param string $name
     * @param string $legalName
     * @param Email $email
     * @param Address $address
     * @param PhoneNumber|null $phone
     * @param string|null $taxRegime
     * @return void
     */
    public function updateInfo(
        string $name,
        string $legalName,
        Email $email,
        Address $address,
        ?PhoneNumber $phone = null,
        ?string $taxRegime = null
    ): void {
        $this->name = $name;
        $this->legalName = $legalName;
        $this->email = $email;
        $this->address = $address;
        $this->phone = $phone;
        $this->taxRegime = $taxRegime;
        $this->updatedAt = new \DateTimeImmutable();

        $this->validate();

        $this->recordDomainEvent([
            'event' => 'CompanyInfoUpdated',
            'companyId' => $this->id->value(),
            'occurredAt' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Cambiar serie de facturación
     * 
     * @param string $newSeries
     * @return void
     */
    public function changeInvoiceSeries(string $newSeries): void
    {
        if ($this->invoiceSeries === $newSeries) {
            return; // No hay cambio
        }

        $oldSeries = $this->invoiceSeries;
        $this->invoiceSeries = $newSeries;
        $this->nextInvoiceNumber = 1; // Reiniciar contador
        $this->updatedAt = new \DateTimeImmutable();

        $this->validate();

        $this->recordDomainEvent([
            'event' => 'InvoiceSeriesChanged',
            'companyId' => $this->id->value(),
            'oldSeries' => $oldSeries,
            'newSeries' => $newSeries,
            'occurredAt' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Establecer logo
     * 
     * @param string $logoPath Ruta del archivo de logo
     * @return void
     */
    public function setLogo(string $logoPath): void
    {
        if (empty($logoPath)) {
            throw new \InvalidArgumentException('La ruta del logo no puede estar vacía');
        }

        $this->logoPath = $logoPath;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Remover logo
     * 
     * @return void
     */
    public function removeLogo(): void
    {
        $this->logoPath = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Activar empresa
     * 
     * @return void
     */
    public function activate(): void
    {
        if ($this->isActive) {
            return; // Ya está activa
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordDomainEvent([
            'event' => 'CompanyActivated',
            'companyId' => $this->id->value(),
            'occurredAt' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Desactivar empresa
     * 
     * @return void
     */
    public function deactivate(): void
    {
        if (!$this->isActive) {
            return; // Ya está desactivada
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordDomainEvent([
            'event' => 'CompanyDeactivated',
            'companyId' => $this->id->value(),
            'occurredAt' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Obtener siguiente número de factura y avanzar contador
     * 
     * @return string Número de factura completo (ej: A-0001)
     */
    public function getNextInvoiceNumber(): string
    {
        if (!$this->isActive) {
            throw new \DomainException('No se pueden generar facturas para empresas inactivas');
        }

        $invoiceNumber = $this->invoiceSeries . '-' . str_pad((string)$this->nextInvoiceNumber, 4, '0', STR_PAD_LEFT);
        
        $this->nextInvoiceNumber++;
        $this->updatedAt = new \DateTimeImmutable();

        return $invoiceNumber;
    }

    /**
     * Verificar si puede emitir facturas
     * 
     * @return bool
     */
    public function canIssueInvoices(): bool
    {
        return $this->isActive;
    }

    // ============================================
    // DOMAIN EVENTS
    // ============================================

    /**
     * Registrar evento de dominio
     * 
     * @param array $event
     * @return void
     */
    private function recordDomainEvent(array $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Obtener y limpiar eventos de dominio
     * 
     * @return array
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    /**
     * Limpiar eventos de dominio sin obtenerlos
     * 
     * @return void
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    // ============================================
    // COMPARACIÓN
    // ============================================

    /**
     * Comparar con otra empresa por ID
     * 
     * @param Company $other
     * @return bool
     */
    public function equals(Company $other): bool
    {
        return $this->id->equals($other->id);
    }
}