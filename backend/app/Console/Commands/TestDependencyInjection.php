<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Entities\User;

/**
 * Comando para probar Dependency Injection
 * 
 * PROPÓSITO:
 * Verificar que el Service Provider está funcionando correctamente
 * y que Laravel inyecta la implementación correcta.
 */
class TestDependencyInjection extends Command
{
    /**
     * Nombre del comando
     */
    protected $signature = 'test:di';

    /**
     * Descripción del comando
     */
    protected $description = 'Probar Dependency Injection del UserRepository';

    /**
     * Constructor - Laravel inyecta automáticamente UserRepositoryInterface
     */
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
        parent::__construct();
    }

    /**
     * Ejecutar el comando
     */
    public function handle(): int
    {
        $this->info('🧪 Probando Dependency Injection...');
        $this->newLine();

        // ============================================
        // 1. VERIFICAR INYECCIÓN
        // ============================================
        
        $this->info('1. Verificando que se inyectó el repository...');
        $className = get_class($this->userRepository);
        $this->line("   ✅ Clase inyectada: {$className}");
        $this->newLine();

        // ============================================
        // 2. CREAR USUARIO DE PRUEBA
        // ============================================
        
        $this->info('2. Creando usuario de prueba...');
        
        try {
            // Generar ID
            $userId = $this->userRepository->nextIdentity();
            $this->line("   📝 ID generado: {$userId->value()}");
            
            // Crear usuario
            $user = User::register(
                id: $userId,
                name: 'Usuario de Prueba',
                email: Email::fromString('test@example.com'),
                plainPassword: 'password123'
            );
            
            $this->line('   ✅ Usuario creado en memoria');
            $this->line("   📧 Email: {$user->email()->value()}");
            $this->line("   👤 Nombre: {$user->name()}");
            $this->newLine();
            
            // ============================================
            // 3. GUARDAR EN BASE DE DATOS
            // ============================================
            
            $this->info('3. Guardando en base de datos...');
            
            // Verificar si ya existe
            if ($this->userRepository->existsEmail($user->email())) {
                $this->warn('   ⚠️  El email ya existe, no se guardará');
                
                // Buscar usuario existente
                $existingUser = $this->userRepository->findByEmail($user->email());
                $this->line("   📋 Usuario existente ID: {$existingUser->id()->value()}");
            } else {
                // Guardar
                $this->userRepository->save($user);
                $this->line('   ✅ Usuario guardado en BD');
            }
            
            $this->newLine();
            
            // ============================================
            // 4. BUSCAR USUARIO
            // ============================================
            
            $this->info('4. Buscando usuario por email...');
            
            $foundUser = $this->userRepository->findByEmail(
                Email::fromString('test@example.com')
            );
            
            $this->line("   ✅ Usuario encontrado:");
            $this->line("   📋 ID: {$foundUser->id()->value()}");
            $this->line("   👤 Nombre: {$foundUser->name()}");
            $this->line("   📧 Email: {$foundUser->email()->value()}");
            $this->line("   ✓ Activo: " . ($foundUser->isActive() ? 'Sí' : 'No'));
            $this->line("   ✓ Verificado: " . ($foundUser->isEmailVerified() ? 'Sí' : 'No'));
            
            $this->newLine();
            
            // ============================================
            // 5. CONTAR USUARIOS
            // ============================================
            
            $this->info('5. Estadísticas...');
            
            $totalUsers = $this->userRepository->count();
            $this->line("   👥 Total de usuarios: {$totalUsers}");
            
            $this->newLine();
            $this->info('✅ Todas las pruebas pasaron correctamente!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('📍 Archivo: ' . $e->getFile() . ':' . $e->getLine());
            
            return Command::FAILURE;
        }
    }
}