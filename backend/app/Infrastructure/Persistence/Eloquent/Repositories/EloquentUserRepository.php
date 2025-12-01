<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use DateTimeImmutable;

/**
 * Implementación del UserRepository usando Eloquent
 * 
 * RESPONSABILIDADES:
 * 1. Implementar todos los métodos de UserRepositoryInterface
 * 2. Convertir User Entity -> UserModel (toDomain)
 * 3. Convertir UserModel -> User Entity (toEloquent)
 * 4. Manejar la persistencia en la base de datos
 * 
 * IMPORTANTE:
 * - Esta clase SÍ depende de Laravel (Eloquent)
 * - El dominio (User Entity) NO conoce esta clase
 * - Esta clase SÍ conoce el dominio (User Entity)
 */
class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Constructor
     * 
     * Inyección de dependencias: Recibimos el Model de Eloquent
     * Esto hace que sea más fácil de testear (podemos inyectar un mock)
     */
    public function __construct(
        private UserModel $model
    ) {}

    // ============================================
    // MÉTODOS DE BÚSQUEDA (LECTURA)
    // ============================================

    /**
     * Encontrar usuario por ID
     * 
     * @param UserId $id ID del usuario
     * @return User Entidad del usuario
     * @throws UserNotFoundException Si no existe
     */
    public function findById(UserId $id): User
    {
        // Buscar en la BD usando Eloquent
        $userModel = $this->model->find($id->value());

        // Si no existe, lanzar excepción del dominio
        if (!$userModel) {
            throw UserNotFoundException::withId($id->value());
        }

        // Convertir UserModel -> User Entity
        return $this->toDomain($userModel);
    }

    /**
     * Encontrar usuario por email
     * 
     * @param Email $email Email del usuario
     * @return User Entidad del usuario
     * @throws UserNotFoundException Si no existe
     */
    public function findByEmail(Email $email): User
    {
        // Buscar por email usando el scope que definimos
        // Equivalente a: SELECT * FROM users WHERE email = ? LIMIT 1
        $userModel = $this->model->byEmail($email->value())->first();

        // Si no existe, lanzar excepción
        if (!$userModel) {
            throw UserNotFoundException::withEmail($email->value());
        }

        // Convertir a entidad
        return $this->toDomain($userModel);
    }

    /**
     * Verificar si existe un email
     * 
     * @param Email $email Email a verificar
     * @return bool True si existe
     */
    public function existsEmail(Email $email): bool
    {
        // Verificar existencia sin traer el registro completo (más eficiente)
        // Equivalente a: SELECT EXISTS(SELECT 1 FROM users WHERE email = ?)
        return $this->model
            ->where('email', $email->value())
            ->exists();
    }

    /**
     * Obtener todos los usuarios con paginación
     * 
     * @param int $page Página actual
     * @param int $perPage Usuarios por página
     * @return array Array de entidades User
     */
    public function findAll(int $page = 1, int $perPage = 15): array
    {
        // Calcular offset para la paginación
        $offset = ($page - 1) * $perPage;

        // Obtener usuarios con LIMIT y OFFSET
        $userModels = $this->model
            ->orderBy('created_at', 'desc') // Más recientes primero
            ->skip($offset)
            ->take($perPage)
            ->get();

        // Convertir cada UserModel a User Entity
        return $userModels->map(function ($userModel) {
            return $this->toDomain($userModel);
        })->toArray();
    }

    /**
     * Contar total de usuarios
     * 
     * @return int Total de usuarios
     */
    public function count(): int
    {
        return $this->model->count();
    }

    // ============================================
    // MÉTODOS DE ESCRITURA (PERSISTENCIA)
    // ============================================

    /**
     * Guardar un usuario (crear o actualizar)
     * 
     * Este método detecta automáticamente si debe hacer INSERT o UPDATE
     * 
     * @param User $user Entidad del usuario
     * @return void
     */
    public function save(User $user): void
    {
        // Buscar si existe el usuario en la BD
        $userModel = $this->model->find($user->id()->value());

        if ($userModel) {
            // UPDATE - Usuario ya existe, actualizar
            $this->updateEloquentModel($userModel, $user);
        } else {
            // INSERT - Usuario no existe, crear nuevo
            $userModel = $this->toEloquent($user);
        }

        // Guardar en la base de datos
        $userModel->save();
    }

    /**
     * Eliminar un usuario (soft delete)
     * 
     * Como usamos SoftDeletes en el Model, esto NO elimina el registro,
     * solo marca deleted_at con la fecha actual.
     * 
     * @param UserId $id ID del usuario
     * @return void
     */
    public function delete(UserId $id): void
    {
        $userModel = $this->model->find($id->value());

        if ($userModel) {
            // Soft delete: marca deleted_at, no elimina realmente
            $userModel->delete();
        }
    }

    /**
     * Obtener próximo ID disponible
     * 
     * En PostgreSQL con auto-increment, esto no es estrictamente necesario
     * ya que la BD genera el ID automáticamente.
     * 
     * Pero lo implementamos por si en el futuro usamos UUIDs o queremos
     * generar el ID antes de guardar.
     * 
     * @return UserId Próximo ID
     */
    public function nextIdentity(): UserId
    {
        // Obtener el MAX(id) + 1
        $maxId = $this->model->max('id') ?? 0;
        
        return UserId::fromInt($maxId + 1);
    }

    // ============================================
    // MAPPERS (CONVERSIÓN ENTRE CAPAS)
    // ============================================

    /**
     * Convertir UserModel (Eloquent) -> User Entity (Dominio)
     * 
     * Este método "reconstituye" la entidad desde la base de datos.
     * Usa el método reconstitute() que NO ejecuta validaciones ni emite eventos.
     * 
     * @param UserModel $model Model de Eloquent
     * @return User Entidad del dominio
     */
    private function toDomain(UserModel $model): User
    {
        return User::reconstitute(
            id: UserId::fromInt($model->id),
            name: $model->name,
            email: Email::fromString($model->email),
            hashedPassword: $model->password,
            isActive: $model->is_active,
            emailVerified: $model->email_verified_at !== null,
            emailVerifiedAt: $model->email_verified_at 
                ? DateTimeImmutable::createFromMutable($model->email_verified_at)
                : null,
            createdAt: DateTimeImmutable::createFromMutable($model->created_at),
            updatedAt: DateTimeImmutable::createFromMutable($model->updated_at)
        );
    }

    /**
     * Convertir User Entity (Dominio) -> UserModel (Eloquent)
     * 
     * Crea un nuevo Model de Eloquent desde la entidad.
     * Se usa cuando insertamos un usuario por primera vez.
     * 
     * @param User $user Entidad del dominio
     * @return UserModel Model de Eloquent
     */
    private function toEloquent(User $user): UserModel
    {
        $model = new UserModel();
        
        // Asignar ID manualmente (si existe)
        // En auto-increment, Eloquent lo generará automáticamente si es null
        $model->id = $user->id()->value();
        
        $this->updateEloquentModel($model, $user);
        
        return $model;
    }

    /**
     * Actualizar un UserModel existente con datos de User Entity
     * 
     * Extrae los datos de la entidad y los asigna al modelo.
     * Se usa tanto para INSERT como UPDATE.
     * 
     * @param UserModel $model Model a actualizar
     * @param User $user Entity fuente de datos
     * @return void
     */
    private function updateEloquentModel(UserModel $model, User $user): void
    {
        // Mapear datos de la Entity al Model
        $model->name = $user->name();
        $model->email = $user->email()->value();
        $model->password = $user->hashedPassword();
        $model->is_active = $user->isActive();
        
        // Convertir DateTimeImmutable a Carbon (que Eloquent espera)
        $model->email_verified_at = $user->emailVerifiedAt()
            ? \Carbon\Carbon::instance($user->emailVerifiedAt())
            : null;
        
        // Eloquent maneja automáticamente created_at y updated_at,
        // pero podemos establecerlos manualmente si es necesario
        $model->created_at = \Carbon\Carbon::instance($user->createdAt());
        $model->updated_at = \Carbon\Carbon::instance($user->updatedAt());
    }

    // ============================================
    // MÉTODOS ADICIONALES (ÚTILES)
    // ============================================

    /**
     * Buscar usuarios activos
     * 
     * @return array Array de User entities
     */
    public function findActiveUsers(): array
    {
        $models = $this->model->active()->get();
        
        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }

    /**
     * Buscar usuarios verificados
     * 
     * @return array Array de User entities
     */
    public function findVerifiedUsers(): array
    {
        $models = $this->model->verified()->get();
        
        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }

    /**
     * Buscar usuarios por nombre (búsqueda parcial)
     * 
     * @param string $name Nombre a buscar
     * @return array Array de User entities
     */
    public function searchByName(string $name): array
    {
        $models = $this->model
            ->where('name', 'ILIKE', "%{$name}%") // ILIKE = insensitive en PostgreSQL
            ->get();
        
        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }
}