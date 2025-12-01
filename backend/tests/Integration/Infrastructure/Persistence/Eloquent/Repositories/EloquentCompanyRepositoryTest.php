<?php

namespace Tests\Integration\Infrastructure\Persistence\Eloquent\Repositories;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\Company\Entities\Company;
use App\Domain\Company\ValueObjects\CompanyId;
use App\Domain\Company\ValueObjects\TaxId;
use App\Domain\Company\ValueObjects\Address;
use App\Domain\Company\ValueObjects\PhoneNumber;
use App\Domain\Shared\ValueObjects\Email;
use App\Domain\Company\Repositories\CompanyRepositoryInterface;
use App\Domain\Company\Exceptions\CompanyNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\CompanyModel;

/**
 * Tests de integración para EloquentCompanyRepository
 * 
 * PROPÓSITO:
 * Verificar que el repositorio:
 * - Guarda correctamente en BD
 * - Lee correctamente de BD
 * - Convierte entre Model y Entity correctamente
 * - Maneja errores apropiadamente
 */
class EloquentCompanyRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CompanyRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(CompanyRepositoryInterface::class);
    }

    /**
     * @test
     */
    public function it_saves_new_company(): void
    {
        // Arrange
        $company = $this->createTestCompany();

        // Act
        $this->repository->save($company);

        // Assert
        $this->assertDatabaseHas('companies', [
            'id' => $company->id()->value(),
            'name' => 'Test Company',
            'tax_id' => 'TST010101AAA',
        ]);
    }

    /**
     * @test
     */
    public function it_finds_company_by_id(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->repository->save($company);

        // Act
        $foundCompany = $this->repository->findById($company->id());

        // Assert
        $this->assertTrue($company->id()->equals($foundCompany->id()));
        $this->assertEquals('Test Company', $foundCompany->name());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_company_not_found_by_id(): void
    {
        // Assert
        $this->expectException(CompanyNotFoundException::class);

        // Act
        $this->repository->findById(CompanyId::fromInt(999));
    }

    /**
     * @test
     */
    public function it_finds_company_by_tax_id(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->repository->save($company);

        // Act
        $foundCompany = $this->repository->findByTaxId($company->taxId());

        // Assert
        $this->assertEquals('TST010101AAA', $foundCompany->taxId()->value());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_company_not_found_by_tax_id(): void
    {
        // Assert
        $this->expectException(CompanyNotFoundException::class);

        // Act
        $this->repository->findByTaxId(TaxId::fromString('XXX999999XXX'));
    }

    /**
     * @test
     */
    public function it_checks_if_tax_id_exists(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->repository->save($company);

        // Act & Assert
        $this->assertTrue($this->repository->existsTaxId($company->taxId()));
        $this->assertFalse($this->repository->existsTaxId(TaxId::fromString('XXX999999XXX')));
    }

    /**
     * @test
     */
    public function it_updates_existing_company(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->repository->save($company);

        // Modificar empresa
        $company->updateInfo(
            'Updated Name',
            'Updated Legal Name',
            Email::fromString('updated@test.com'),
            Address::create('New Street', 'New City', 'New State'),
            null,
            'New Regime'
        );

        // Act
        $this->repository->save($company);

        // Assert
        $this->assertDatabaseHas('companies', [
            'id' => $company->id()->value(),
            'name' => 'Updated Name',
            'legal_name' => 'Updated Legal Name',
            'email' => 'updated@test.com',
        ]);
    }

    /**
     * @test
     */
    public function it_deletes_company(): void
    {
        // Arrange
        $company = $this->createTestCompany();
        $this->repository->save($company);

        // Act
        $this->repository->delete($company->id());

        // Assert - Soft delete
        $this->assertSoftDeleted('companies', [
            'id' => $company->id()->value(),
        ]);
    }

    /**
     * @test
     */
    public function it_finds_all_companies(): void
    {
        // Arrange
        $company1 = $this->createTestCompany(1, 'Company A', 'AAA010101AAA');
        $company2 = $this->createTestCompany(2, 'Company B', 'BBB020202BBB');
        $company3 = $this->createTestCompany(3, 'Company C', 'CCC030303CCC');

        $this->repository->save($company1);
        $this->repository->save($company2);
        $this->repository->save($company3);

        // Act
        $companies = $this->repository->findAll(page: 1, perPage: 10);

        // Assert
        $this->assertCount(3, $companies);
    }

    /**
     * @test
     */
    public function it_finds_only_active_companies(): void
    {
        // Arrange
        $activeCompany = $this->createTestCompany(1, 'Active', 'AAA010101AAA');
        $inactiveCompany = $this->createTestCompany(2, 'Inactive', 'BBB020202BBB');
        
        $this->repository->save($activeCompany);
        $this->repository->save($inactiveCompany);
        
        $inactiveCompany->deactivate();
        $this->repository->save($inactiveCompany);

        // Act
        $companies = $this->repository->findAll(page: 1, perPage: 10, onlyActive: true);

        // Assert
        $this->assertCount(1, $companies);
        $this->assertEquals('Active', $companies[0]->name());
    }

    /**
     * @test
     */
    public function it_counts_companies(): void
    {
        // Arrange
        $this->repository->save($this->createTestCompany(1, 'A', 'AAA010101AAA'));
        $this->repository->save($this->createTestCompany(2, 'B', 'BBB020202BBB'));
        $this->repository->save($this->createTestCompany(3, 'C', 'CCC030303CCC'));

        // Act
        $count = $this->repository->count();

        // Assert
        $this->assertEquals(3, $count);
    }

    /**
     * @test
     */
    public function it_searches_companies_by_name(): void
    {
        // Arrange
        $this->repository->save($this->createTestCompany(1, 'Tech Solutions', 'AAA010101AAA'));
        $this->repository->save($this->createTestCompany(2, 'Tech Innovations', 'BBB020202BBB'));
        $this->repository->save($this->createTestCompany(3, 'Food Company', 'CCC030303CCC'));

        // Act
        $companies = $this->repository->searchByName('Tech');

        // Assert
        $this->assertCount(2, $companies);
    }

    /**
     * @test
     */
    public function it_generates_next_identity(): void
    {
        // Act
        $id1 = $this->repository->nextIdentity();
        $id2 = $this->repository->nextIdentity();

        // Assert
        $this->assertInstanceOf(CompanyId::class, $id1);
        $this->assertInstanceOf(CompanyId::class, $id2);
        $this->assertNotEquals($id1->value(), $id2->value());
    }

    /**
     * @test
     */
    public function it_maps_between_model_and_entity_correctly(): void
    {
        // Arrange
        $originalCompany = Company::register(
            CompanyId::fromInt(1),
            'Tech Solutions',
            'Tech Solutions SA de CV',
            TaxId::fromString('TST010101AAA'),
            Email::fromString('info@tech.com'),
            Address::create('Av. Principal 123', 'CDMX', 'CDMX', 'México', '01000'),
            PhoneNumber::fromString('5512345678'),
            'General de Ley',
            'A'
        );

        // Act - Guardar y recuperar
        $this->repository->save($originalCompany);
        $retrievedCompany = $this->repository->findById($originalCompany->id());

        // Assert - Todos los campos deben coincidir
        $this->assertEquals($originalCompany->id()->value(), $retrievedCompany->id()->value());
        $this->assertEquals($originalCompany->name(), $retrievedCompany->name());
        $this->assertEquals($originalCompany->legalName(), $retrievedCompany->legalName());
        $this->assertEquals($originalCompany->taxId()->value(), $retrievedCompany->taxId()->value());
        $this->assertEquals($originalCompany->email()->value(), $retrievedCompany->email()->value());
        $this->assertEquals($originalCompany->phone()->value(), $retrievedCompany->phone()->value());
        $this->assertEquals($originalCompany->address()->street(), $retrievedCompany->address()->street());
        $this->assertEquals($originalCompany->taxRegime(), $retrievedCompany->taxRegime());
        $this->assertEquals($originalCompany->invoiceSeries(), $retrievedCompany->invoiceSeries());
        $this->assertEquals($originalCompany->isActive(), $retrievedCompany->isActive());
    }

    /**
     * Helper: Crear empresa de prueba
     */
    private function createTestCompany(
        int $id = 1,
        string $name = 'Test Company',
        string $taxId = 'TST010101AAA'
    ): Company {
        return Company::register(
            CompanyId::fromInt($id),
            $name,
            $name . ' SA de CV',
            TaxId::fromString($taxId),
            Email::fromString('test@company.com'),
            Address::create('Test Street 123', 'Test City', 'Test State', 'México', '12345'),
            PhoneNumber::fromString('5512345678'),
            'Test Tax Regime',
            'A'
        );
    }
}