<?php

namespace Tests\Unit\Domain\Company\Entities;

use PHPUnit\Framework\TestCase;
use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\TaxId;
use App\Domain\Company\ValueObjects\Address;
use App\Domain\Company\ValueObjects\PhoneNumber;
use App\Domain\Shared\ValueObjects\Email;

/**
 * Tests para Company Entity
 * 
 * PROPÓSITO:
 * Verificar que Company:
 * - Se registra correctamente
 * - Valida todos sus campos
 * - Ejecuta lógica de negocio
 * - Emite eventos de dominio
 * - Maneja activación/desactivación
 * - Genera números de factura correctamente
 */
class CompanyTest extends TestCase
{
    /**
     * @test
     */
    public function it_registers_company_with_valid_data(): void
    {
        // Arrange
        $id = CompanyId::fromInt(1);
        $name = 'Tech Solutions S.A.';
        $legalName = 'Tech Solutions Sociedad Anónima';
        $taxId = TaxId::fromString('TSO010101AAA');
        $email = Email::fromString('info@techsolutions.com');
        $address = Address::create('Av. Reforma 123', 'CDMX', 'CDMX', 'México', '01000');
        $phone = PhoneNumber::fromString('5512345678');

        // Act
        $company = Company::register(
            $id,
            $name,
            $legalName,
            $taxId,
            $email,
            $address,
            $phone,
            'General de Ley Personas Morales',
            'A'
        );

        // Assert
        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Tech Solutions S.A.', $company->name());
        $this->assertEquals('Tech Solutions Sociedad Anónima', $company->legalName());
        $this->assertEquals('TSO010101AAA', $company->taxId()->value());
        $this->assertEquals('info@techsolutions.com', $company->email()->value());
        $this->assertTrue($company->isActive());
        $this->assertEquals('A', $company->invoiceSeries());
        $this->assertEquals(1, $company->nextInvoiceNumber());
    }

    /**
     * @test
     */
    public function it_emits_company_registered_event(): void
    {
        // Arrange
        $id = CompanyId::fromInt(1);
        $taxId = TaxId::fromString('TSO010101AAA');
        $email = Email::fromString('info@test.com');
        $address = Address::create('Calle 1', 'Ciudad', 'Estado');

        // Act
        $company = Company::register(
            $id,
            'Test Company',
            'Test Company SA',
            $taxId,
            $email,
            $address
        );

        // Assert
        $events = $company->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('CompanyRegistered', $events[0]['event']);
        $this->assertEquals(1, $events[0]['companyId']);
    }

    /**
     * @test
     */
    public function it_rejects_empty_name(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre de la empresa es obligatorio');

        // Act
        Company::register(
            CompanyId::fromInt(1),
            '', // Nombre vacío
            'Legal Name',
            TaxId::fromString('TSO010101AAA'),
            Email::fromString('test@test.com'),
            Address::create('Calle 1', 'Ciudad', 'Estado')
        );
    }

    /**
     * @test
     */
    public function it_rejects_short_name(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre debe tener al menos 2 caracteres');

        // Act
        Company::register(
            CompanyId::fromInt(1),
            'A', // Solo 1 carácter
            'Legal Name',
            TaxId::fromString('TSO010101AAA'),
            Email::fromString('test@test.com'),
            Address::create('Calle 1', 'Ciudad', 'Estado')
        );
    }

    /**
     * @test
     */
    public function it_rejects_empty_legal_name(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La razón social es obligatoria');

        // Act
        Company::register(
            CompanyId::fromInt(1),
            'Company Name',
            '', // Razón social vacía
            TaxId::fromString('TSO010101AAA'),
            Email::fromString('test@test.com'),
            Address::create('Calle 1', 'Ciudad', 'Estado')
        );
    }

    /**
     * @test
     */
    public function it_updates_company_info(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $newEmail = Email::fromString('newemail@test.com');
        $newAddress = Address::create('Nueva Calle 456', 'Guadalajara', 'Jalisco');

        // Act
        $company->updateInfo(
            'Updated Name',
            'Updated Legal Name',
            $newEmail,
            $newAddress,
            null,
            'Nuevo Régimen'
        );

        // Assert
        $this->assertEquals('Updated Name', $company->name());
        $this->assertEquals('Updated Legal Name', $company->legalName());
        $this->assertEquals('newemail@test.com', $company->email()->value());
        $this->assertEquals('Nueva Calle 456', $company->address()->street());
        $this->assertEquals('Nuevo Régimen', $company->taxRegime());
    }

    /**
     * @test
     */
    public function it_emits_event_when_info_updated(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->pullDomainEvents(); // Limpiar eventos previos

        // Act
        $company->updateInfo(
            'New Name',
            'New Legal',
            Email::fromString('new@test.com'),
            Address::create('Calle 1', 'Ciudad', 'Estado')
        );

        // Assert
        $events = $company->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('CompanyInfoUpdated', $events[0]['event']);
    }

    /**
     * @test
     */
    public function it_activates_company(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->deactivate();
        $this->assertFalse($company->isActive());

        // Act
        $company->activate();

        // Assert
        $this->assertTrue($company->isActive());
    }

    /**
     * @test
     */
    public function it_emits_event_when_activated(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->deactivate();
        $company->pullDomainEvents(); // Limpiar eventos

        // Act
        $company->activate();

        // Assert
        $events = $company->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('CompanyActivated', $events[0]['event']);
    }

    /**
     * @test
     */
    public function it_deactivates_company(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->assertTrue($company->isActive());

        // Act
        $company->deactivate();

        // Assert
        $this->assertFalse($company->isActive());
    }

    /**
     * @test
     */
    public function it_emits_event_when_deactivated(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->pullDomainEvents(); // Limpiar eventos

        // Act
        $company->deactivate();

        // Assert
        $events = $company->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('CompanyDeactivated', $events[0]['event']);
    }

    /**
     * @test
     */
    public function it_changes_invoice_series(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->assertEquals('A', $company->invoiceSeries());

        // Act
        $company->changeInvoiceSeries('B');

        // Assert
        $this->assertEquals('B', $company->invoiceSeries());
        $this->assertEquals(1, $company->nextInvoiceNumber()); // Reinicia contador
    }

    /**
     * @test
     */
    public function it_emits_event_when_series_changed(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->pullDomainEvents();

        // Act
        $company->changeInvoiceSeries('B');

        // Assert
        $events = $company->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('InvoiceSeriesChanged', $events[0]['event']);
        $this->assertEquals('A', $events[0]['oldSeries']);
        $this->assertEquals('B', $events[0]['newSeries']);
    }

    /**
     * @test
     */
    public function it_generates_invoice_numbers_sequentially(): void
    {
        // Arrange
        $company = $this->createTestCompany();

        // Act
        $number1 = $company->getNextInvoiceNumber();
        $number2 = $company->getNextInvoiceNumber();
        $number3 = $company->getNextInvoiceNumber();

        // Assert
        $this->assertEquals('A-0001', $number1);
        $this->assertEquals('A-0002', $number2);
        $this->assertEquals('A-0003', $number3);
        $this->assertEquals(4, $company->nextInvoiceNumber());
    }

    /**
     * @test
     */
    public function it_rejects_invoice_generation_for_inactive_company(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->deactivate();

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No se pueden generar facturas para empresas inactivas');

        // Act
        $company->getNextInvoiceNumber();
    }

    /**
     * @test
     */
    public function it_sets_logo_path(): void
    {
        // Arrange
        $company = $this->createTestCompany();

        // Act
        $company->setLogo('logos/company-1.png');

        // Assert
        $this->assertEquals('logos/company-1.png', $company->logoPath());
    }

    /**
     * @test
     */
    public function it_removes_logo(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->setLogo('logos/company-1.png');

        // Act
        $company->removeLogo();

        // Assert
        $this->assertNull($company->logoPath());
    }

    /**
     * @test
     */
    public function it_can_issue_invoices_when_active(): void
    {
        // Arrange
        $company = $this->createTestCompany();

        // Act & Assert
        $this->assertTrue($company->canIssueInvoices());
    }

    /**
     * @test
     */
    public function it_cannot_issue_invoices_when_inactive(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $company->deactivate();

        // Act & Assert
        $this->assertFalse($company->canIssueInvoices());
    }

    /**
     * @test
     */
    public function it_compares_companies_by_id(): void
    {
        // Arrange
        $company1 = $this->createTestCompany(1);
        $company2 = $this->createTestCompany(1); // Mismo ID
        $company3 = $this->createTestCompany(2); // Diferente ID

        // Act & Assert
        $this->assertTrue($company1->equals($company2));
        $this->assertFalse($company1->equals($company3));
    }

    /**
     * @test
     */
    public function it_clears_domain_events_after_pull(): void
    {
        // Arrange
        $company = $this->createTestCompany();

        // Act
        $events1 = $company->pullDomainEvents();
        $events2 = $company->pullDomainEvents();

        // Assert
        $this->assertCount(1, $events1); // Primer pull tiene eventos
        $this->assertCount(0, $events2); // Segundo pull está vacío
    }

    /**
     * Helper: Crear empresa de prueba
     */
    private function createTestCompany(int $id = 1): Company
    {
        return Company::register(
            CompanyId::fromInt($id),
            'Test Company',
            'Test Company SA de CV',
            TaxId::fromString('TST010101AAA'),
            Email::fromString('test@company.com'),
            Address::create('Test Street 123', 'Test City', 'Test State', 'México', '12345'),
            PhoneNumber::fromString('5512345678'),
            'Test Tax Regime',
            'A'
        );
    }
}