<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de tokens para reset de contraseña
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            /**
             * email - Email del usuario
             * PRIMARY KEY
             */
            $table->string('email')->primary();

            /**
             * token - Token hasheado (SHA-256)
             * NO guardamos el token en texto plano
             */
            $table->string('token');

            /**
             * created_at - Cuándo se creó el token
             * Para verificar expiración (60 minutos)
             */
            $table->timestamp('created_at');

            // Índice para búsqueda rápida
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};