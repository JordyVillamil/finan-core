<?php

namespace Tests\Unit\Domain\Company\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\Company\ValueObjects\PhoneNumber;

/**
 * Tests para PhoneNumber Value Object
 * 
 * PROPÓSITO:
 * Verificar que PhoneNumber:
 * - Acepta diferentes formatos
 * - Normaliza números
 * - Valida longitud
 * - Extrae código de país
 * - Formatea correctamente
 */
class PhoneNumberTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_phone_number_from_digits(): void
    {
        // Arrange & Act
        $phone = PhoneNumber::fromString('5512345678');

        // Assert
        $this->assertInstanceOf(PhoneNumber::class, $phone);
        $this->assertEquals('5512345678', $phone->number());
    }

    /**
     * @test
     */
    public function it_creates_phone_number_with_spaces(): void
    {
        // Arrange & Act
        $phone = PhoneNumber::fromString('55 1234 5678');

        // Assert
        $this->assertEquals('5512345678', $phone->number());
    }

    /**
     * @test
     */
    public function it_creates_phone_number_with_parentheses_and_dashes(): void
    {
        // Arrange & Act
        $phone = PhoneNumber::fromString('(55) 1234-5678');

        // Assert
        $this->assertEquals('5512345678', $phone->number());
    }

    /**
     * @test
     */
    public function it_creates_phone_number_with_country_code(): void
    {
        // Arrange & Act
        $phone = PhoneNumber::fromString('+52 55 1234 5678');

        // Assert
        $this->assertEquals('52', $phone->countryCode());
        $this->assertEquals('5512345678', $phone->number());
    }

    /**
     * @test
     */
    public function it_rejects_empty_phone_number(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El número telefónico no puede estar vacío');

        // Act
        PhoneNumber::fromString('');
    }

    /**
     * @test
     */
    public function it_rejects_phone_number_too_short(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El número telefónico debe tener entre 7 y 15 dígitos');

        // Act
        PhoneNumber::fromString('12345'); // Solo 5 dígitos
    }

    /**
     * @test
     */
    public function it_rejects_phone_number_too_long(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El número telefónico debe tener entre 7 y 15 dígitos');

        // Act
        PhoneNumber::fromString('12345678901234567890'); // Más de 15 dígitos
    }

    /**
     * @test
     */
    public function it_rejects_phone_number_with_letters(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El número telefónico solo puede contener dígitos');

        // Act
        PhoneNumber::fromString('+52 55 ABC4 5678');
    }

    /**
     * @test
     */
    public function it_formats_mexican_phone_number(): void
    {
        // Arrange
        $phone = PhoneNumber::fromString('5512345678');

        // Act
        $formatted = $phone->national();

        // Assert
        $this->assertEquals('55 1234 5678', $formatted);
    }

    /**
     * @test
     */
    public function it_formats_international_phone_number(): void
    {
        // Arrange
        $phone = PhoneNumber::fromString('+52 55 1234 5678');

        // Act
        $international = $phone->international();

        // Assert
        $this->assertEquals('+52 55 1234 5678', $international);
    }

    /**
     * @test
     */
    public function it_formats_international_without_country_code(): void
    {
        // Arrange
        $phone = PhoneNumber::fromString('55 1234 5678');

        // Act
        $international = $phone->international();

        // Assert - Asume México (+52) por defecto
        $this->assertEquals('+52 55 1234 5678', $international);
    }

    /**
     * @test
     */
    public function it_compares_equal_phone_numbers(): void
    {
        // Arrange
        $phone1 = PhoneNumber::fromString('55 1234 5678');
        $phone2 = PhoneNumber::fromString('5512345678');

        // Act & Assert
        $this->assertTrue($phone1->equals($phone2));
    }

    /**
     * @test
     */
    public function it_compares_different_phone_numbers(): void
    {
        // Arrange
        $phone1 = PhoneNumber::fromString('5512345678');
        $phone2 = PhoneNumber::fromString('5587654321');

        // Act & Assert
        $this->assertFalse($phone1->equals($phone2));
    }

    /**
     * @test
     */
    public function it_converts_to_string(): void
    {
        // Arrange
        $phone = PhoneNumber::fromString('5512345678');

        // Act & Assert
        $this->assertEquals('55 1234 5678', (string) $phone);
    }

    /**
     * @test
     * @dataProvider validPhoneNumberProvider
     */
    public function it_accepts_valid_phone_numbers(string $phone): void
    {
        // Act
        $phoneNumber = PhoneNumber::fromString($phone);

        // Assert
        $this->assertInstanceOf(PhoneNumber::class, $phoneNumber);
    }

    /**
     * Data provider con números válidos
     */
    public static function validPhoneNumberProvider(): array
    {
        return [
            'México sin código' => ['5512345678'],
            'México con espacios' => ['55 1234 5678'],
            'México con paréntesis' => ['(55) 1234 5678'],
            'México con guiones' => ['55-1234-5678'],
            'Internacional' => ['+52 55 1234 5678'],
            'Colombia' => ['+57 1 234 5678'],
            'USA' => ['+1 555 123 4567'],
            'Número de 7 dígitos' => ['1234567'],
            'Número de 15 dígitos' => ['123456789012345'],
        ];
    }

    /**
     * @test
     * @dataProvider invalidPhoneNumberProvider
     */
    public function it_rejects_invalid_phone_numbers(string $phone): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        PhoneNumber::fromString($phone);
    }

    /**
     * Data provider con números inválidos
     */
    public static function invalidPhoneNumberProvider(): array
    {
        return [
            'Vacío' => [''],
            'Solo espacios' => ['   '],
            'Muy corto' => ['12345'],
            'Muy largo' => ['12345678901234567890'],
            'Con letras' => ['555-ABCD'],
            'Caracteres especiales' => ['555#1234*5678'],
        ];
    }
}