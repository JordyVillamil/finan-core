<?php

namespace Tests\Unit\Domain\Company\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\Company\ValueObjects\CompanyId;

/**
 * Tests para CompanyId Value Object
 * 
 * PROPÓSITO:
 * Verificar que CompanyId:
 * - Se crea correctamente desde int y string
 * - Valida IDs positivos
 * - Rechaza IDs inválidos
 * - Compara correctamente
 */
class CompanyIdTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_company_id_from_int(): void
    {
        // Arrange & Act
        $companyId = CompanyId::fromInt(1);

        // Assert
        $this->assertInstanceOf(CompanyId::class, $companyId);
        $this->assertEquals(1, $companyId->value());
    }

    /**
     * @test
     */
    public function it_creates_company_id_from_string(): void
    {
        // Arrange & Act
        $companyId = CompanyId::fromString('42');

        // Assert
        $this->assertInstanceOf(CompanyId::class, $companyId);
        $this->assertEquals(42, $companyId->value());
    }

    /**
     * @test
     */
    public function it_rejects_zero(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El ID de empresa debe ser un número positivo');

        // Act
        CompanyId::fromInt(0);
    }

    /**
     * @test
     */
    public function it_rejects_negative_numbers(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        CompanyId::fromInt(-1);
    }

    /**
     * @test
     */
    public function it_rejects_non_numeric_strings(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El ID de empresa debe ser numérico');

        // Act
        CompanyId::fromString('abc');
    }

    /**
     * @test
     */
    public function it_compares_equal_ids(): void
    {
        // Arrange
        $id1 = CompanyId::fromInt(1);
        $id2 = CompanyId::fromInt(1);

        // Act & Assert
        $this->assertTrue($id1->equals($id2));
    }

    /**
     * @test
     */
    public function it_compares_different_ids(): void
    {
        // Arrange
        $id1 = CompanyId::fromInt(1);
        $id2 = CompanyId::fromInt(2);

        // Act & Assert
        $this->assertFalse($id1->equals($id2));
    }

    /**
     * @test
     */
    public function it_converts_to_string(): void
    {
        // Arrange
        $companyId = CompanyId::fromInt(123);

        // Act & Assert
        $this->assertEquals('123', (string) $companyId);
    }
}