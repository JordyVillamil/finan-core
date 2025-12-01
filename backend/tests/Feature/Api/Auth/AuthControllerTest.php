<?php

namespace Tests\Feature\Api\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Tests Feature para autenticación
 * 
 * PROPÓSITO:
 * Probar el flujo COMPLETO de autenticación:
 * HTTP Request → Controller → Service → Repository → BD
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup para cada test
     * Ejecuta el seeder de roles y permisos necesario para autenticación
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test: Registrar usuario exitosamente
     * 
     * @test
     */
    public function it_registers_user_successfully(): void
    {
        // Arrange
        $userData = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act - POST /api/auth/register
        $response = $this->postJson('/api/auth/register', $userData);

        // Assert - Respuesta HTTP
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'is_active',
                        'is_email_verified',
                        'created_at',
                    ],
                ],
            ]);

        // Assert - Base de datos
        $this->assertDatabaseHas('users', [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Rechazar registro con email duplicado
     * 
     * @test
     */
    public function it_rejects_duplicate_email(): void
    {
        // Arrange - Crear usuario
        $this->postJson('/api/auth/register', [
            'name' => 'Usuario 1',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Act - Intentar registrar con mismo email
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Usuario 2',
            'email' => 'test@example.com', // Email duplicado
            'password' => 'password456',
            'password_confirmation' => 'password456',
        ]);

        // Assert
        $response->assertStatus(422) // 422 = Validation Error
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test: Rechazar registro con datos inválidos
     * 
     * @test
     */
    public function it_rejects_invalid_registration_data(): void
    {
        // Act
        $response = $this->postJson('/api/auth/register', [
            'name' => 'A', // Muy corto
            'email' => 'invalid-email', // Email inválido
            'password' => 'short', // Muy corto
            'password_confirmation' => 'different', // No coincide
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test: Login exitoso
     * 
     * @test
     */
    public function it_logs_in_successfully(): void
    {
        // Arrange - Registrar usuario
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Act - Login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login exitoso',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                ],
            ]);

        // Verificar que viene un token
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test: Rechazar login con credenciales incorrectas
     * 
     * @test
     */
    public function it_rejects_invalid_credentials(): void
    {
        // Arrange - Registrar usuario
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Act - Login con password incorrecto
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertStatus(401) // 401 = Unauthorized
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test: Rechazar login con email no registrado
     * 
     * @test
     */
    public function it_rejects_unregistered_email(): void
    {
        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Test: Health check funciona
     * 
     * @test
     */
    public function it_returns_health_check(): void
    {
        // Act
        $response = $this->getJson('/api/health');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'API is running',
            ]);
    }

    /**
     * Test: Validación de campos requeridos
     * 
     * @test
     */
    public function it_validates_required_fields_on_register(): void
    {
        // Act - Enviar request vacío
        $response = $this->postJson('/api/auth/register', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test: Password debe tener confirmación
     * 
     * @test
     */
    public function it_requires_password_confirmation(): void
    {
        // Act
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            // Sin password_confirmation
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}