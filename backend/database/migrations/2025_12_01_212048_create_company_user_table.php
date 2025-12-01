<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla company_user (pivot)
 * 
 * PROPÓSITO:
 * Relacionar usuarios con empresas (muchos a muchos).
 * Un usuario puede pertenecer a múltiples empresas.
 * Una empresa puede tener múltiples usuarios.
 * 
 * EJEMPLO:
 * - Juan puede ser Admin de Empresa A
 * - Juan puede ser Contador de Empresa B
 * - María puede ser Contadora de Empresa A
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            // ============================================
            // IDENTIFICACIÓN
            // ============================================
            
            $table->id();

            // ============================================
            // RELACIONES (FOREIGN KEYS)
            // ============================================
            
            /**
             * company_id - ID de la empresa
             */
            $table->foreignId('company_id')
                ->constrained('companies')
                ->onDelete('cascade'); // Si se elimina la empresa, se eliminan las relaciones

            /**
             * user_id - ID del usuario
             */
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Si se elimina el usuario, se eliminan las relaciones

            // ============================================
            // DATOS ADICIONALES
            // ============================================
            
            /**
             * role - Rol del usuario EN ESTA EMPRESA específica
             * 
             * Esto es ADICIONAL al sistema de roles global (Spatie).
             * Permite que un usuario tenga diferentes roles en diferentes empresas.
             * 
             * Valores posibles:
             * - 'owner' - Dueño de la empresa
             * - 'admin' - Administrador en esta empresa
             * - 'accountant' - Contador en esta empresa
             * - 'employee' - Empleado
             * - 'viewer' - Solo visualización
             */
            $table->string('role', 50)->default('employee');

            // ============================================
            // TIMESTAMPS
            // ============================================
            
            $table->timestamp('created_at')->useCurrent();

            // ============================================
            // RESTRICCIONES
            // ============================================
            
            /**
             * Un usuario solo puede estar una vez en cada empresa
             * No puede haber duplicados de company_id + user_id
             */
            $table->unique(['company_id', 'user_id']);

            // ============================================
            // ÍNDICES
            // ============================================
            
            $table->index('company_id');
            $table->index('user_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};