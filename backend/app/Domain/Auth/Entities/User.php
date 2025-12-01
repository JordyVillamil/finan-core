<?php

namespace App\Domain\Auth\Entities;

use App\Domain\Auth\ValueObjects\Email;
use App\Domain\Auth\ValueObjects\UserId;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use DateTimeImmutable;
use Illuminate\Support\Facades\Hash;

/**
 * Entity User - Representa un Usuario en el dominio
 * 
 * PRINCIPIOS IMPORTANTES:
 * 1. La entidad es la "fuente de verdad" de las reglas de negocio
 * 2. NO tiene dependencias de infraestructura (Eloquent, DB, etc.)
 * 3. Todos los cambios de estado se hacen con métodos públicos que validan
 * 4. Emite eventos de dominio cuando pasan cosas importantes
 */
class User
{
    // ============================================
    // PROPIEDADES PRIVADAS
    // ============================================
    
    /**
     * Identificador único del usuario
     */
    private UserId $id;

    /**
     * Nombre completo del usuario
     */
    private string $name;

    /**
     * Email del usuario (Value Object que garantiza validez)
     */
    private Email $email;

    /**
     * Contraseña hasheada (NUNCA guardamos contraseña en texto plano)
     */
    private string $hashedPassword;

    /**
     * Si el usuario está activo o no
     */
    private bool $isActive;

    /**
     * Si el email ha sido verificado
     */
    private bool $emailVerified;

    /**
     * Cuándo se verificó el email
     */
    private ?DateTimeImmutable $emailVerifiedAt;

    /**
     * Cuándo se creó el usuario
     */
    private DateTimeImmutable $createdAt;

    /**
     * Cuándo se actualizó por última vez
     */
    private DateTimeImmutable $updatedAt;

    /**
     * Eventos de dominio que han ocurrido
     * (ej: "usuario registrado", "email verificado", etc.)
     */
    private array $domainEvents = [];

    // ============================================
    // CONSTRUCTOR PRIVADO
    // ============================================
    
    /**
     * Constructor privado - usar métodos estáticos para crear
     * 
     * ¿Por qué privado?
     * - Forzamos a usar métodos como register() o reconstitute()
     * - Cada método tiene su semántica clara
     * - register() = nuevo usuario
     * - reconstitute() = usuario desde la BD
     */
    private function __construct(
        UserId $id,
        string $name,
        Email $email,
        string $hashedPassword,
        bool $isActive,
        bool $emailVerified,
        ?DateTimeImmutable $emailVerifiedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
        $this->isActive = $isActive;
        $this->emailVerified = $emailVerified;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // ============================================
    // MÉTODOS DE CREACIÓN (FACTORY METHODS)
    // ============================================

    /**
     * Registrar un nuevo usuario
     * 
     * Este método representa el CASO DE USO de registro:
     * - Crea el usuario
     * - Hashea la contraseña
     * - Lo marca como no verificado
     * - Emite evento "UserRegistered"
     * 
     * @param UserId $id ID generado previamente
     * @param string $name Nombre del usuario
     * @param Email $email Email del usuario
     * @param string $plainPassword Contraseña en texto plano
     * @return self Nueva instancia de User
     */
    public static function register(
        UserId $id,
        string $name,
        Email $email,
        string $plainPassword
    ): self {
        // Validar nombre
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }

        if (strlen($name) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres');
        }

        // Validar contraseña
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }

        // Hashear contraseña usando el método helper que respeta la configuración
        $hashedPassword = self::hashPassword($plainPassword);

        $now = new DateTimeImmutable();

        // Crear usuario
        $user = new self(
            id: $id,
            name: $name,
            email: $email,
            hashedPassword: $hashedPassword,
            isActive: true,
            emailVerified: false,
            emailVerifiedAt: null,
            createdAt: $now,
            updatedAt: $now
        );

        // Emitir evento de dominio
        $user->recordDomainEvent([
            'event' => 'UserRegistered',
            'userId' => $id->value(),
            'email' => $email->value(),
            'occurredAt' => $now->format('Y-m-d H:i:s'),
        ]);

        return $user;
    }

    /**
     * Reconstituir usuario desde la base de datos
     * 
     * Usamos este método cuando traemos un usuario de la BD.
     * NO emite eventos porque ya existía.
     * 
     * @param UserId $id
     * @param string $name
     * @param Email $email
     * @param string $hashedPassword Ya hasheada de la BD
     * @param bool $isActive
     * @param bool $emailVerified
     * @param DateTimeImmutable|null $emailVerifiedAt
     * @param DateTimeImmutable $createdAt
     * @param DateTimeImmutable $updatedAt
     * @return self
     */
    public static function reconstitute(
        UserId $id,
        string $name,
        Email $email,
        string $hashedPassword,
        bool $isActive,
        bool $emailVerified,
        ?DateTimeImmutable $emailVerifiedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            hashedPassword: $hashedPassword,
            isActive: $isActive,
            emailVerified: $emailVerified,
            emailVerifiedAt: $emailVerifiedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    // ============================================
    // GETTERS (Acceso a propiedades)
    // ============================================

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function emailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ============================================
    // MÉTODOS DE NEGOCIO (Comportamiento)
    // ============================================

    /**
     * Verificar contraseña
     * 
     * Compara la contraseña ingresada con el hash almacenado.
     * Usa password_verify() que es resistente a timing attacks.
     * 
     * @param string $plainPassword Contraseña en texto plano
     * @return bool True si coincide
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hashedPassword);
    }

    /**
     * Cambiar contraseña
     * 
     * Reglas:
     * - Mínimo 8 caracteres
     * - Debe ser diferente a la actual
     * - Actualiza el timestamp
     * 
     * @param string $currentPassword Contraseña actual
     * @param string $newPassword Nueva contraseña
     * @throws InvalidCredentialsException Si la contraseña actual es incorrecta
     */
    public function changePassword(string $currentPassword, string $newPassword): void
    {
        // Verificar contraseña actual
        if (!$this->verifyPassword($currentPassword)) {
            throw new InvalidCredentialsException('La contraseña actual es incorrecta');
        }

        // Validar nueva contraseña
        if (strlen($newPassword) < 8) {
            throw new \InvalidArgumentException('La nueva contraseña debe tener al menos 8 caracteres');
        }

        // No puede ser igual a la actual
        if ($currentPassword === $newPassword) {
            throw new \InvalidArgumentException('La nueva contraseña debe ser diferente a la actual');
        }

        // Hashear y actualizar usando el método helper
        $this->hashedPassword = self::hashPassword($newPassword);
        $this->updatedAt = new DateTimeImmutable();

        // Emitir evento
        $this->recordDomainEvent([
            'event' => 'PasswordChanged',
            'userId' => $this->id->value(),
            'occurredAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Verificar email del usuario
     * 
     * Marca el email como verificado y registra cuándo ocurrió.
     */
    public function verifyEmail(): void
    {
        // Si ya está verificado, no hacer nada
        if ($this->emailVerified) {
            return;
        }

        $now = new DateTimeImmutable();
        
        $this->emailVerified = true;
        $this->emailVerifiedAt = $now;
        $this->updatedAt = $now;

        // Emitir evento
        $this->recordDomainEvent([
            'event' => 'EmailVerified',
            'userId' => $this->id->value(),
            'email' => $this->email->value(),
            'occurredAt' => $now->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Activar usuario
     */
    public function activate(): void
    {
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();

        $this->recordDomainEvent([
            'event' => 'UserActivated',
            'userId' => $this->id->value(),
            'occurredAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Desactivar usuario
     */
    public function deactivate(): void
    {
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();

        $this->recordDomainEvent([
            'event' => 'UserDeactivated',
            'userId' => $this->id->value(),
            'occurredAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Actualizar nombre
     * 
     * @param string $newName Nuevo nombre
     */
    public function updateName(string $newName): void
    {
        if (empty(trim($newName))) {
            throw new \InvalidArgumentException('El nombre no puede estar vacío');
        }

        if (strlen($newName) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres');
        }

        $this->name = $newName;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Actualizar email
     * 
     * @param Email $newEmail Nuevo email
     */
    public function updateEmail(Email $newEmail): void
    {
        // Si es el mismo email, no hacer nada
        if ($this->email->equals($newEmail)) {
            return;
        }

        $oldEmail = $this->email;
        $this->email = $newEmail;
        
        // Al cambiar el email, debe verificarse de nuevo
        $this->emailVerified = false;
        $this->emailVerifiedAt = null;
        $this->updatedAt = new DateTimeImmutable();

        $this->recordDomainEvent([
            'event' => 'EmailChanged',
            'userId' => $this->id->value(),
            'oldEmail' => $oldEmail->value(),
            'newEmail' => $newEmail->value(),
            'occurredAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ]);
    }

    // ============================================
    // DOMAIN EVENTS (Eventos de Dominio)
    // ============================================

    /**
     * Registrar un evento de dominio
     * 
     * Los eventos se acumulan y se procesan después.
     * Ejemplo: enviar email de bienvenida cuando se registra.
     */
    private function recordDomainEvent(array $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Obtener eventos pendientes
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    /**
     * Limpiar eventos (sin procesarlos)
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    // ============================================
    // MÉTODOS DE COMPARACIÓN
    // ============================================

    /**
     * Comparar dos usuarios
     * 
     * Dos usuarios son iguales si tienen el mismo ID.
     */
    public function equals(User $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * Convertir a array (para debugging)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => $this->name,
            'email' => $this->email->value(),
            'isActive' => $this->isActive,
            'emailVerified' => $this->emailVerified,
            'emailVerifiedAt' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    // ============================================
    // MÉTODOS PRIVADOS DE UTILIDAD
    // ============================================

    /**
     * Hashear contraseña usando Laravel Hash cuando está disponible
     * 
     * En Feature tests y producción usa Hash::make() que respeta BCRYPT_ROUNDS
     * En Unit tests puros (sin container) usa password_hash() con cost 10
     * 
     * @param string $plainPassword Contraseña en texto plano
     * @return string Contraseña hasheada
     */
    private static function hashPassword(string $plainPassword): string
    {
        // Intentar usar Laravel Hash facade si el container está disponible
        try {
            if (app()->bound('hash')) {
                return Hash::make($plainPassword);
            }
        } catch (\Throwable) {
            // Container no disponible (unit tests puros), usar fallback
        }

        // Fallback: usar password_hash con cost 10 (más rápido para tests)
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    }
}