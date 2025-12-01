<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces del dominio
use App\Domain\Auth\Repositories\UserRepositoryInterface;

// Implementaciones de infraestructura
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Service Provider para el Dominio
 * 
 * RESPONSABILIDAD:
 * Registrar bindings (enlaces) entre interfaces y sus implementaciones.
 * 
 * ¿QUÉ ES UN BINDING?
 * Le decimos a Laravel: "Cuando alguien pida UserRepositoryInterface,
 * dale una instancia de EloquentUserRepository"
 * 
 * VENTAJA:
 * Desacoplamiento total. El código que usa UserRepositoryInterface
 * NO sabe (ni le importa) qué implementación recibe.
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * 
     * Este método se ejecuta PRIMERO cuando Laravel inicia.
     * Aquí registramos los bindings (enlaces).
     */
    public function register(): void
    {
        // ============================================
        // REPOSITORIES
        // ============================================
        
        /**
         * Binding: UserRepositoryInterface -> EloquentUserRepository
         * 
         * CÓMO FUNCIONA:
         * 
         * 1. Alguien pide UserRepositoryInterface en el constructor:
         *    public function __construct(UserRepositoryInterface $repo) {}
         * 
         * 2. Laravel ve "UserRepositoryInterface"
         * 
         * 3. Laravel busca en los bindings registrados
         * 
         * 4. Encuentra este binding y ejecuta la función
         * 
         * 5. La función crea y retorna EloquentUserRepository
         * 
         * 6. Laravel inyecta esa instancia
         * 
         * TIPOS DE BINDINGS:
         * - bind(): Crea nueva instancia cada vez
         * - singleton(): Una sola instancia compartida
         */
        $this->app->bind(
            UserRepositoryInterface::class, // La interface (lo que se pide)
            function ($app) {                // La implementación (lo que se da)
                // Crear instancia de EloquentUserRepository
                // pasándole una instancia de UserModel
                return new EloquentUserRepository(
                    new UserModel()
                );
            }
        );
        
        // ALTERNATIVA MÁS SIMPLE (si no necesitas lógica extra):
        // $this->app->bind(
        //     UserRepositoryInterface::class,
        //     EloquentUserRepository::class
        // );
        
        /**
         * ¿Cuándo usar bind() vs singleton()?
         * 
         * bind() - Nueva instancia cada vez:
         * - Usa cuando el objeto tiene estado que puede cambiar
         * - Usa cuando necesitas objetos independientes
         * - Ejemplo: Repositories (cada petición HTTP es independiente)
         * 
         * singleton() - Una sola instancia compartida:
         * - Usa cuando el objeto es stateless (sin estado)
         * - Usa cuando quieres compartir la misma instancia
         * - Ejemplo: Logger, Cache, Config
         */
        
        // Ejemplo con singleton (comentado, no lo uses para repositories):
        // $this->app->singleton(
        //     UserRepositoryInterface::class,
        //     EloquentUserRepository::class
        // );
    }

    /**
     * Bootstrap services.
     * 
     * Este método se ejecuta DESPUÉS de register().
     * Aquí puedes hacer configuraciones adicionales que requieran
     * que otros services ya estén registrados.
     */
    public function boot(): void
    {
        // Por ahora no necesitamos nada aquí
        
        // Ejemplo de cosas que irían aquí:
        // - Publicar configuraciones
        // - Registrar middleware
        // - Registrar event listeners
        // - Configurar validaciones personalizadas
    }
    
    /**
     * Get the services provided by the provider.
     * 
     * Este método OPCIONAL le dice a Laravel qué servicios provee este provider.
     * Laravel lo usa para optimización (puede cargar el provider solo cuando se necesite).
     * 
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            UserRepositoryInterface::class,
        ];
    }
}