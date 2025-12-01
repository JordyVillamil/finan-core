<?php

namespace Tests\Unit\Domain\Auth\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\Auth\ValueObjects\Email;

/**
 * Tests para Email Value Object
 * 
 * PROPÓSITO:
 * Verificar que el Value Object Email funciona correctamente:
 * - Valida emails correctos
 * - Rechaza emails inválidos
 * - Normaliza emails (lowercase, trim)
 * - Compara emails correctamente
 */
class EmailTest extends TestCase
{
    /**
     * Test: Crear email válido
     * 
     * @test
     */
    public function it_creates_valid_email(): void
    {
        // Arrange (Preparar)
        $emailString = 'test@example.com';

        // Act (Actuar)
        $email = Email::fromString($emailString);

        // Assert (Verificar)
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('test@example.com', $email->value());
    }

    /**
     * Test: Email se normaliza a lowercase
     * 
     * @test
     */
    public function it_normalizes_email_to_lowercase(): void
    {
        // Arrange
        $emailString = 'TEST@EXAMPLE.COM';

        // Act
        $email = Email::fromString($emailString);

        // Assert
        $this->assertEquals('test@example.com', $email->value());
    }

    /**
     * Test: Email elimina espacios
     * 
     * @test
     */
    public function it_trims_whitespace(): void
    {
        // Arrange
        $emailString = '  test@example.com  ';

        // Act
        $email = Email::fromString($emailString);

        // Assert
        $this->assertEquals('test@example.com', $email->value());
    }

    /**
     * Test: Rechaza email vacío
     * 
     * @test
     */
    public function it_rejects_empty_email(): void
    {
        // Assert que se lanza excepción
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El email no puede estar vacío');

        // Act
        Email::fromString('');
    }

    /**
     * Test: Rechaza email inválido
     * 
     * @test
     */
    public function it_rejects_invalid_email(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El email no es válido');

        // Act
        Email::fromString('not-an-email');
    }

    /**
     * Test: Compara emails correctamente
     * 
     * @test
     */
    public function it_compares_emails_correctly(): void
    {
        // Arrange
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        $email3 = Email::fromString('other@example.com');

        // Assert
        $this->assertTrue($email1->equals($email2)); // Iguales
        $this->assertFalse($email1->equals($email3)); // Diferentes
    }

    /**
     * Test: Emails con diferentes case son iguales
     * 
     * @test
     */
    public function it_treats_different_case_as_equal(): void
    {
        // Arrange
        $email1 = Email::fromString('Test@Example.COM');
        $email2 = Email::fromString('test@example.com');

        // Assert
        $this->assertTrue($email1->equals($email2));
    }

    /**
     * Test: Ejemplos de emails válidos
     * 
     * @test
     * @dataProvider validEmailProvider
     */
    public function it_accepts_valid_emails(string $emailString): void
    {
        // Act
        $email = Email::fromString($emailString);

        // Assert
        $this->assertInstanceOf(Email::class, $email);
    }

    /**
     * Test: Ejemplos de emails inválidos
     * 
     * @test
     * @dataProvider invalidEmailProvider
     */
    public function it_rejects_invalid_emails(string $emailString): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        Email::fromString($emailString);
    }

    /**
     * Data Provider: Emails válidos
     */
    public static function validEmailProvider(): array
    {
        return [
            ['test@example.com'],
            ['user.name@example.com'],
            ['user+tag@example.co.uk'],
            ['123@example.com'],
            ['test_email@sub.example.com'],
        ];
    }

    /**
     * Data Provider: Emails inválidos
     */
    public static function invalidEmailProvider(): array
    {
        return [
            [''],
            ['not-an-email'],
            ['@example.com'],
            ['test@'],
            ['test @example.com'], // Espacio
            ['test..email@example.com'], // Doble punto
        ];
    }
}