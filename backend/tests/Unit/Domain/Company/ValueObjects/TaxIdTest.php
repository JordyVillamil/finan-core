<?php

namespace Tests\Unit\Domain\Company\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\Company\ValueObjects\TaxId;

/**
 * Tests para TaxId Value Object
 * 
 * PROPÓSITO:
 * Verificar que TaxId:
 * - Valida RFC mexicano (12 y 13 caracteres)
 * - Valida NIT colombiano con dígito verificador
 * - Normaliza formato (uppercase, sin espacios)
 * - Identifica tipo correctamente
 */
class TaxIdTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_rfc_persona_moral(): void
    {
        // RFC Persona Moral: 12 caracteres
        // Arrange & Act
        $taxId = TaxId::fromString('AAA010101AAA');

        // Assert
        $this->assertInstanceOf(TaxId::class, $taxId);
        $this->assertEquals('AAA010101AAA', $taxId->value());
        $this->assertTrue($taxId->isRFC());
        $this->assertFalse($taxId->isNIT());
        $this->assertEquals('RFC', $taxId->type());
    }

    /**
     * @test
     */
    public function it_creates_rfc_persona_fisica(): void
    {
        // RFC Persona Física: 13 caracteres
        // Arrange & Act
        $taxId = TaxId::fromString('AAAA010101AAA');

        // Assert
        $this->assertEquals('AAAA010101AAA', $taxId->value());
        $this->assertTrue($taxId->isRFC());
    }

    /**
     * @test
     */
    public function it_normalizes_rfc_to_uppercase(): void
    {
        // Arrange & Act
        $taxId = TaxId::fromString('aaa010101aaa');

        // Assert
        $this->assertEquals('AAA010101AAA', $taxId->value());
    }

    /**
     * @test
     */
    public function it_removes_spaces_from_rfc(): void
    {
        // Arrange & Act
        $taxId = TaxId::fromString('AAA 010101 AAA');

        // Assert
        $this->assertEquals('AAA010101AAA', $taxId->value());
    }

    /**
     * @test
     */
    public function it_creates_nit_with_valid_check_digit(): void
    {
        // NIT válido de Colombia con dígito verificador correcto
        // 900123456: Sum=586, Remainder=3, Check=11-3=8
        // Arrange & Act
        $taxId = TaxId::fromString('900123456-8');

        // Assert
        $this->assertInstanceOf(TaxId::class, $taxId);
        $this->assertEquals('900123456-8', $taxId->value());
        $this->assertTrue($taxId->isNIT());
        $this->assertFalse($taxId->isRFC());
        $this->assertEquals('NIT', $taxId->type());
    }

    /**
     * @test
     */
    public function it_rejects_nit_with_invalid_check_digit(): void
    {
        // NIT con dígito verificador incorrecto
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        TaxId::fromString('900123456-9'); // Dígito verificador incorrecto
    }

    /**
     * @test
     */
    public function it_rejects_rfc_with_invalid_length(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        TaxId::fromString('AAA01010'); // Solo 8 caracteres
    }

    /**
     * @test
     */
    public function it_rejects_rfc_with_invalid_format(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        TaxId::fromString('123456789012'); // Solo números, no letras
    }

    /**
     * @test
     */
    public function it_rejects_invalid_tax_id(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El identificador fiscal no es válido');

        // Act
        TaxId::fromString('INVALID');
    }

    /**
     * @test
     */
    public function it_compares_equal_tax_ids(): void
    {
        // Arrange
        $taxId1 = TaxId::fromString('AAA010101AAA');
        $taxId2 = TaxId::fromString('AAA010101AAA');

        // Act & Assert
        $this->assertTrue($taxId1->equals($taxId2));
    }

    /**
     * @test
     */
    public function it_compares_different_tax_ids(): void
    {
        // Arrange
        $taxId1 = TaxId::fromString('AAA010101AAA');
        $taxId2 = TaxId::fromString('BBB020202BBB');

        // Act & Assert
        $this->assertFalse($taxId1->equals($taxId2));
    }

    /**
     * @test
     */
    public function it_formats_rfc_with_dashes(): void
    {
        // Arrange
        $taxId = TaxId::fromString('AAA010101AAA');

        // Act
        $formatted = $taxId->formatted();

        // Assert
        $this->assertEquals('AAA-010101-AAA', $formatted);
    }

    /**
     * @test
     */
    public function it_formats_nit_keeping_dash(): void
    {
        // Arrange
        $taxId = TaxId::fromString('900123456-8');

        // Act
        $formatted = $taxId->formatted();

        // Assert
        $this->assertEquals('900123456-8', $formatted);
    }

    /**
     * @test
     */
    public function it_converts_to_string(): void
    {
        // Arrange
        $taxId = TaxId::fromString('AAA010101AAA');

        // Act & Assert
        $this->assertEquals('AAA010101AAA', (string) $taxId);
    }

    /**
     * @test
     * @dataProvider validRFCProvider
     */
    public function it_accepts_valid_rfcs(string $rfc): void
    {
        // Act
        $taxId = TaxId::fromString($rfc);

        // Assert
        $this->assertTrue($taxId->isRFC());
    }

    /**
     * Data provider con RFCs válidos
     */
    public static function validRFCProvider(): array
    {
        return [
            'RFC Persona Moral' => ['AAA010101AAA'],
            'RFC Persona Física' => ['AAAA010101AAA'],
            'Con Ñ' => ['AÑA010101AAA'],
            'Con &' => ['A&A010101AAA'],
            'Con números en homoclave' => ['AAA010101A12'],
        ];
    }

    /**
     * @test
     * @dataProvider invalidRFCProvider
     */
    public function it_rejects_invalid_rfcs(string $rfc): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        TaxId::fromString($rfc);
    }

    /**
     * Data provider con RFCs inválidos
     */
    public static function invalidRFCProvider(): array
    {
        return [
            'Muy corto' => ['AAA01010'],
            'Muy largo' => ['AAAA010101AAAA'],
            'Solo números' => ['123456789012'],
            'Caracteres inválidos' => ['AAA@10101AAA'],
        ];
    }
}