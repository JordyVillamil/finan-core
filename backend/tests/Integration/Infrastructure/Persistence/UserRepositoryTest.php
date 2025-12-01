<?php

namespace Tests\Integration\Infrastructure\Persistence;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\Auth\Entities\User;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Exceptions\UserNotFoundException;

/**
 * Tests de integración para UserRepository
 * 
 * PROPÓSITO:
 * Verificar que el Repository funciona correctamente con la BD:
 * - Guardar usuarios
 * - Buscar usuarios
 * - Actualizar usuarios
 * - Eliminar usuarios
 */
class UserRepositoryTest extends TestCase
{
    use RefreshDatabase; // Resetea BD entre tests

    private UserRepositoryInterface $repository;

    /**
     * Setup - Se ejecuta antes de cada test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar migraciones en BD de prueba
        $this->artisan('migrate');

        // Obtener repository del container
        $this->repository = app(UserRepositoryInterface::class);
    }

    /**
     * Test: Guardar usuario nuevo
     * 
     * @test
     */
    public function it_saves_new_user(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test User',
            Email::fromString('test@example.com'),
            'password123'
        );

        // Act
        $this->repository->save($user);

        // Assert
        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test: Buscar usuario por ID
     * 
     * @test
     */
    public function it_finds_user_by_id(): void
    {
        // Arrange - Guardar usuario
        $user = User::register(
            UserId::fromInt(1),
            'Test User',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->repository->save($user);

        // Act - Buscar
        $foundUser = $this->repository->findById(UserId::fromInt(1));

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals(1, $foundUser->id()->value());
        $this->assertEquals('Test User', $foundUser->name());
        $this->assertEquals('test@example.com', $foundUser->email()->value());
    }

    /**
     * Test: Lanzar excepción si usuario no existe
     * 
     * @test
     */
    public function it_throws_exception_when_user_not_found_by_id(): void
    {
        // Assert
        $this->expectException(UserNotFoundException::class);

        // Act
        $this->repository->findById(UserId::fromInt(999));
    }

    /**
     * Test: Buscar usuario por email
     * 
     * @test
     */
    public function it_finds_user_by_email(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test User',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->repository->save($user);

        // Act
        $foundUser = $this->repository->findByEmail(
            Email::fromString('test@example.com')
        );

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals('test@example.com', $foundUser->email()->value());
    }

    /**
     * Test: Verificar si email existe
     * 
     * @test
     */
    public function it_checks_if_email_exists(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test User',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->repository->save($user);

        // Act & Assert
        $this->assertTrue(
            $this->repository->existsEmail(Email::fromString('test@example.com'))
        );
        $this->assertFalse(
            $this->repository->existsEmail(Email::fromString('other@example.com'))
        );
    }

    /**
     * Test: Actualizar usuario existente
     * 
     * @test
     */
    public function it_updates_existing_user(): void
    {
        // Arrange - Crear y guardar
        $user = User::register(
            UserId::fromInt(1),
            'Original Name',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->repository->save($user);

        // Act - Modificar y guardar de nuevo
        $user->updateName('Updated Name');
        $this->repository->save($user);

        // Assert
        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test: Eliminar usuario (soft delete)
     * 
     * @test
     */
    public function it_deletes_user(): void
    {
        // Arrange
        $user = User::register(
            UserId::fromInt(1),
            'Test User',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->repository->save($user);

        // Act
        $this->repository->delete(UserId::fromInt(1));

        // Assert - Soft delete: registro existe pero deleted_at no es NULL
        $this->assertDatabaseHas('users', [
            'id' => 1,
        ]);
        $this->assertSoftDeleted('users', [
            'id' => 1,
        ]);
    }

    /**
     * Test: Contar usuarios
     * 
     * @test
     */
    public function it_counts_users(): void
    {
        // Arrange - Crear 3 usuarios
        for ($i = 1; $i <= 3; $i++) {
            $user = User::register(
                UserId::fromInt($i),
                "User {$i}",
                Email::fromString("user{$i}@example.com"),
                'password123'
            );
            $this->repository->save($user);
        }

        // Act
        $count = $this->repository->count();

        // Assert
        $this->assertEquals(3, $count);
    }

    /**
     * Test: Obtener todos los usuarios con paginación
     * 
     * @test
     */
    public function it_gets_all_users_with_pagination(): void
    {
        // Arrange - Crear 5 usuarios
        for ($i = 1; $i <= 5; $i++) {
            $user = User::register(
                UserId::fromInt($i),
                "User {$i}",
                Email::fromString("user{$i}@example.com"),
                'password123'
            );
            $this->repository->save($user);
        }

        // Act - Página 1, 2 por página
        $page1 = $this->repository->findAll(page: 1, perPage: 2);

        // Assert
        $this->assertCount(2, $page1);
        $this->assertInstanceOf(User::class, $page1[0]);

        // Act - Página 2
        $page2 = $this->repository->findAll(page: 2, perPage: 2);

        // Assert
        $this->assertCount(2, $page2);
    }

    /**
     * Test: NextIdentity genera IDs incrementales
     * 
     * @test
     */
    public function it_generates_next_identity(): void
    {
        // Act - Sin usuarios, debería ser 1
        $id1 = $this->repository->nextIdentity();
        $this->assertEquals(1, $id1->value());

        // Arrange - Crear un usuario
        $user = User::register(
            UserId::fromInt(1),
            'Test',
            Email::fromString('test@example.com'),
            'password123'
        );
        $this->repository->save($user);

        // Act - Con 1 usuario, debería ser 2
        $id2 = $this->repository->nextIdentity();
        $this->assertEquals(2, $id2->value());
    }
}