<?php

namespace Tests\Unit\Domain\Auth\Entities;

use PHPUnit\Framework\TestCase;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;

/**
 * Tests para User Entity
 * 
 * PROPÓSITO:
 * Verificar que la entidad User:
 * - Se crea correctamente
 * - Valida datos
 * - Hashea passwords
 * - Verifica passwords
 * - Emite eventos de dominio
 */
class UserTest extends TestCase
{
    /**
     * Test: Registrar usuario válido
     * 
     * @test
     */
    public function it_registers_user_with_valid_data(): void
    {
        // Arrange
        $userId = UserId::fromInt(1);
        $name = 'Juan Pérez';
        $email = Email::fromString('juan@example.com');
        $password = 'password123';

        // Act
        $user = User::register($userId, $name, $email, $password);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Juan Pérez', $user->name());
        $this->assertEquals('juan@example.com', $user->email()->value());
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isEmailVerified());
    }

    /**
     * Test: Password se hashea automáticamente
     * 
     * @test
     */
    public function it_hashes_password_on_register(): void
    {
        // Arrange
        $userId = UserId::fromInt(1);
        $email = Email::fromString('test@example.com');
        $plainPassword = 'password123';

        // Act
        $user = User::register($userId, 'Test User', $email, $plainPassword);

        // Assert
        $hashedPassword = $user->hashedPassword();
        $this->assertNotEquals($plainPassword, $hashedPassword); // No es texto plano
        $this->assertStringStartsWith('$2y$', $hashedPassword); // Es bcrypt
    }

    /**
     * Test: Verificar password correcto
     * 
     * @test
     */
    public function it_verifies_correct_password(): void
    {
        // Arrange
        $userId = UserId::fromInt(1);
        $email = Email::fromString('test@example.com');
        $password = 'password123';
        $user = User::register($userId, 'Test', $email, $password);

        // Act
        $isValid = $user->verifyPassword('password123');

        // Assert
        $this->assertTrue($isValid);
    }

    /**
     * Test: Rechazar password incorrecto
     * 
     * @test
     */
    public function it_rejects_incorrect_password(): void
    {
        // Arrange
        $userId = UserId::fromInt(1);
        $email = Email::fromString('test@example.com');
        $user = User::register($userId, 'Test', $email, 'password123');

        // Act
        $isValid = $user->verifyPassword('wrong-password');

        // Assert
        $this->assertFalse($isValid);
    }

    /**
     * Test: Rechazar nombre vacío
     * 
     * @test
     */
    public function it_rejects_empty_name(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre no puede estar vacío');

        // Act
        User::register(
            UserId::fromInt(1),
            '', // Nombre vacío
            Email::fromString('test@example.com'),
            'password123'
        );
    }

    /**
     * Test: Rechazar nombre muy corto
     * 
     * @test
     */
    public function it_rejects_short_name(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre debe tener al menos 2 caracteres');

        // Act
        User::register(
            UserId::fromInt(1),
            'J', // Solo 1 carácter
            Email::fromString('test@example.com'),
            'password123'
        );
    }

    /**
     * Test: Rechazar password muy corto
     * 
     * @test
     */
    public function it_rejects_short_password(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La contraseña debe tener al menos 8 caracteres');

        // Act
        User::register(
            UserId::fromInt(1),
            'Test User',
            Email::fromString('test@example.com'),
            'pass' // Solo 4 caracteres
        );
    }

    /**
     * Test: Verificar email marca como verificado
     * 
     * @test
     */
    public function it_verifies_email(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->assertFalse($user->isEmailVerified());

        // Act
        $user->verifyEmail();

        // Assert
        $this->assertTrue($user->isEmailVerified());
        $this->assertNotNull($user->emailVerifiedAt());
    }

    /**
     * Test: Activar/desactivar usuario
     * 
     * @test
     */
    public function it_activates_and_deactivates_user(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->assertTrue($user->isActive());

        // Act - Desactivar
        $user->deactivate();

        // Assert
        $this->assertFalse($user->isActive());

        // Act - Activar de nuevo
        $user->activate();

        // Assert
        $this->assertTrue($user->isActive());
    }

    /**
     * Test: Cambiar password con password actual correcto
     * 
     * @test
     */
    public function it_changes_password_with_correct_current_password(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'oldpassword123'
        );

        // Act
        $user->changePassword('oldpassword123', 'newpassword456');

        // Assert
        $this->assertTrue($user->verifyPassword('newpassword456'));
        $this->assertFalse($user->verifyPassword('oldpassword123'));
    }

    /**
     * Test: Rechazar cambio de password con password actual incorrecto
     * 
     * @test
     */
    public function it_rejects_password_change_with_wrong_current_password(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'password123'
        );

        // Assert
        $this->expectException(InvalidCredentialsException::class);

        // Act
        $user->changePassword('wrong-password', 'newpassword456');
    }

    /**
     * Test: Emitir evento de dominio al registrar
     * 
     * @test
     */
    public function it_emits_user_registered_event(): void
    {
        // Arrange & Act
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'password123'
        );

        // Assert
        $events = $user->pullDomainEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('UserRegistered', $events[0]['event']);
        $this->assertEquals(1, $events[0]['userId']);
    }

    /**
     * Test: Eventos se limpian después de pull
     * 
     * @test
     */
    public function it_clears_events_after_pull(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'password123'
        );

        // Act
        $events1 = $user->pullDomainEvents();
        $events2 = $user->pullDomainEvents();

        // Assert
        $this->assertCount(1, $events1); // Primer pull tiene eventos
        $this->assertCount(0, $events2); // Segundo pull está vacío
    }

    /**
     * Test: Comparar usuarios por ID
     * 
     * @test
     */
    public function it_compares_users_by_id(): void
    {
        // Arrange
        $user1 = User::register(
            UserId::fromInt(1),
            'User 1',
            Email::fromString('user1@example.com'),
            'password123'
        );

        $user2 = User::register(
            UserId::fromInt(1), // Mismo ID
            'User 2',
            Email::fromString('user2@example.com'),
            'password123'
        );

        $user3 = User::register(
            UserId::fromInt(2), // Diferente ID
            'User 3',
            Email::fromString('user3@example.com'),
            'password123'
        );

        // Assert
        $this->assertTrue($user1->equals($user2)); // Mismo ID = iguales
        $this->assertFalse($user1->equals($user3)); // Diferente ID = diferentes
    }
}