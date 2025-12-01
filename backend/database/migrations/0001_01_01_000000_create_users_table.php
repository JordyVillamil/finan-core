<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Crear tabla users
 * 
 * Las migraciones son como "commits" de Git pero para tu base de datos.
 * Permiten crear, modificar o eliminar tablas de forma controlada.
 * 
 * COMANDOS:
 * - php artisan migrate          -> Ejecuta migraciones pendientes
 * - php artisan migrate:rollback -> Deshace la última migración
 * - php artisan migrate:fresh    -> Elimina TODO y vuelve a crear
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Este método se ejecuta cuando haces: php artisan migrate
     * Aquí defines CÓMO crear la tabla.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // ============================================
            // COLUMNAS PRINCIPALES
            // ============================================
            
            /**
             * id - Identificador único autoincremental
             * PRIMARY KEY, AUTO_INCREMENT
             */
            $table->id();

            /**
             * name - Nombre completo del usuario
             * VARCHAR(255), NOT NULL
             */
            $table->string('name');

            /**
             * email - Email del usuario
             * VARCHAR(255), NOT NULL, UNIQUE
             * 
             * UNIQUE = No puede haber dos usuarios con el mismo email
             */
            $table->string('email')->unique();

            /**
             * password - Contraseña hasheada
             * VARCHAR(255), NOT NULL
             * 
             * NUNCA guardamos contraseñas en texto plano
             * Laravel usa bcrypt por defecto (60 caracteres)
             */
            $table->string('password');

            /**
             * is_active - Si el usuario está activo
             * BOOLEAN, NOT NULL, DEFAULT TRUE
             * 
             * Permite desactivar usuarios sin eliminarlos
             */
            $table->boolean('is_active')->default(true);

            /**
             * email_verified_at - Cuándo se verificó el email
             * TIMESTAMP, NULLABLE
             * 
             * NULL = No verificado
             * Fecha = Verificado en esa fecha
             */
            $table->timestamp('email_verified_at')->nullable();

            // ============================================
            // REMEMBER TOKEN (para "Recordarme")
            // ============================================
            
            /**
             * remember_token - Token para "recordar sesión"
             * VARCHAR(100), NULLABLE
             * 
             * Se usa cuando el usuario marca "Recordarme"
             * Laravel lo maneja automáticamente
             */
            $table->rememberToken();

            // ============================================
            // TIMESTAMPS AUTOMÁTICOS
            // ============================================
            
            /**
             * created_at y updated_at
             * TIMESTAMP, NOT NULL
             * 
             * Laravel los actualiza automáticamente:
             * - created_at: Cuando se crea el registro
             * - updated_at: Cuando se actualiza el registro
             */
            $table->timestamps();

            // ============================================
            // SOFT DELETES (Eliminación suave)
            // ============================================
            
            /**
             * deleted_at
             * TIMESTAMP, NULLABLE
             * 
             * Permite "eliminar" sin borrar realmente.
             * NULL = No eliminado
             * Fecha = Eliminado en esa fecha
             * 
             * Ventajas:
             * - Puedes recuperar usuarios eliminados
             * - Mantiene integridad referencial
             * - Permite auditoría
             */
            $table->softDeletes();

            // ============================================
            // ÍNDICES PARA PERFORMANCE
            // ============================================
            
            /**
             * Índice en email para búsquedas rápidas
             * 
             * Sin índice: SELECT * FROM users WHERE email = 'x' -> Escanea TODA la tabla
             * Con índice: SELECT * FROM users WHERE email = 'x' -> Busca directo (mucho más rápido)
             */
            $table->index('email');

            /**
             * Índice en is_active
             * Útil para queries como: WHERE is_active = true
             */
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Este método se ejecuta cuando haces: php artisan migrate:rollback
     * Aquí defines CÓMO deshacer la migración (usualmente eliminar la tabla).
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};