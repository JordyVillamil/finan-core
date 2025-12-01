<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla companies
 * 
 * PROPÓSITO:
 * Almacenar información de empresas del sistema.
 * 
 * RELACIONES:
 * - 1:N con invoices (una empresa tiene muchas facturas)
 * - 1:N con clients (una empresa tiene muchos clientes)
 * - 1:N con products (una empresa tiene muchos productos)
 * - N:M con users (empresa-usuario, tabla pivot: company_user)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            // ============================================
            // IDENTIFICACIÓN
            // ============================================
            
            $table->id(); // bigint auto-increment primary key

            // ============================================
            // INFORMACIÓN BÁSICA
            // ============================================
            
            /**
             * name - Nombre comercial
             * El nombre con el que se conoce la empresa
             */
            $table->string('name', 255);

            /**
             * legal_name - Razón social
             * Nombre legal completo de la empresa
             */
            $table->string('legal_name', 255);

            /**
             * tax_id - RFC (México) o NIT (Colombia)
             * Identificador fiscal ÚNICO
             */
            $table->string('tax_id', 50)->unique();

            /**
             * email - Email de contacto
             */
            $table->string('email', 255);

            /**
             * phone - Teléfono (opcional)
             */
            $table->string('phone', 20)->nullable();

            // ============================================
            // DIRECCIÓN
            // ============================================
            
            $table->string('address_street', 255)->nullable();
            $table->string('address_city', 100)->nullable();
            $table->string('address_state', 100)->nullable();
            $table->string('address_country', 100)->default('México');
            $table->string('address_postal_code', 10)->nullable();

            // ============================================
            // CONFIGURACIÓN FISCAL
            // ============================================
            
            /**
             * tax_regime - Régimen fiscal
             * Ej: "General de Ley Personas Morales", "RIF", etc.
             */
            $table->string('tax_regime', 100)->nullable();

            /**
             * logo_path - Ruta del logo de la empresa
             * Ej: "logos/company-1.png"
             */
            $table->string('logo_path', 255)->nullable();

            // ============================================
            // FACTURACIÓN
            // ============================================
            
            /**
             * invoice_series - Serie de facturación
             * Ej: "A", "B", "FAC", etc.
             */
            $table->string('invoice_series', 10)->default('A');

            /**
             * next_invoice_number - Próximo folio a usar
             * Se incrementa automáticamente al generar facturas
             */
            $table->integer('next_invoice_number')->default(1);

            // ============================================
            // ESTADO
            // ============================================
            
            /**
             * is_active - Si la empresa está activa
             * Solo empresas activas pueden emitir facturas
             */
            $table->boolean('is_active')->default(true);

            // ============================================
            // TIMESTAMPS
            // ============================================
            
            $table->timestamps();      // created_at, updated_at
            $table->softDeletes();     // deleted_at (soft delete)

            // ============================================
            // ÍNDICES
            // ============================================
            
            /**
             * Índices para mejorar performance de búsquedas
             */
            $table->index('tax_id');        // Búsqueda por RFC/NIT
            $table->index('is_active');     // Filtrar activas/inactivas
            $table->index('name');          // Búsqueda por nombre
            $table->index('created_at');    // Ordenar por fecha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};