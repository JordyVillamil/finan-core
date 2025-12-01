<?php

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Exceptions\UserNotFoundException;

/**
 * Repository Interface para User
 * 
 * CONCEPTO CLAVE: Interface (Contrato)
 * - Define QUÉ operaciones existen
 * - NO define CÓMO se implementan
 * - El dominio solo conoce esta interface
 * - La infraestructura implementa el CÓMO (Eloquent, API, etc.)
 * 
 * VENTAJAS:
 * 1. Desacoplamiento: El dominio no sabe si usas MySQL, PostgreSQL, API, etc.
 * 2. Testeable: Puedes crear un InMemoryUserRepository para tests
 * 3. Intercambiable: Cambiar implementación sin tocar dominio
 */
interface UserRepositoryInterface
{
    /**
     * Encontrar usuario por ID
     * 
     * @param UserId $id ID del usuario
     * @return User Entidad del usuario
     * @throws UserNotFoundException Si no existe
     */
    public function findById(UserId $id): User;

    /**
     * Encontrar usuario por email
     * 
     * @param Email $email Email del usuario
     * @return User Entidad del usuario
     * @throws UserNotFoundException Si no existe
     */
    public function findByEmail(Email $email): User;

    /**
     * Verificar si existe un email
     * 
     * Útil para validar duplicados antes de registrar
     * 
     * @param Email $email Email a verificar
     * @return bool True si existe
     */
    public function existsEmail(Email $email): bool;

    /**
     * Guardar un usuario (crear o actualizar)
     * 
     * @param User $user Entidad del usuario
     * @return void
     */
    public function save(User $user): void;

    /**
     * Eliminar un usuario
     * 
     * @param UserId $id ID del usuario
     * @return void
     */
    public function delete(UserId $id): void;

    /**
     * Obtener próximo ID disponible
     * 
     * Útil para crear un nuevo usuario antes de guardarlo.
     * En auto-increment no es necesario, pero en UUID sí.
     * 
     * @return UserId Próximo ID
     */
    public function nextIdentity(): UserId;

    /**
     * Obtener todos los usuarios (con paginación)
     * 
     * @param int $page Página actual
     * @param int $perPage Usuarios por página
     * @return array Array de entidades User
     */
    public function findAll(int $page = 1, int $perPage = 15): array;

    /**
     * Contar total de usuarios
     * 
     * @return int Total de usuarios
     */
    public function count(): int;
}