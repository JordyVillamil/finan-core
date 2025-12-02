<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Eloquent Model para la tabla users
 * 
 * IMPORTANTE: Este NO es la Entity del dominio.
 * Este es el ORM de Laravel para acceder a la base de datos.
 * 
 * RESPONSABILIDADES:
 * - Mapear la tabla 'users' de la BD
 * - Definir relaciones con otras tablas
 * - Definir qué campos son "fillable" (rellenables)
 * - Definir casts (conversiones automáticas)
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class UserModel extends Authenticatable
{
    use HasFactory;    // Permite crear factories para testing
    use SoftDeletes;   // Permite eliminación suave (soft delete)
    use HasApiTokens;  // Permite generar tokens de Sanctum
    use HasRoles;      // Permite roles y permisos de Spatie

    /**
     * Guard para Spatie Permission
     * 
     * Sanctum usa el guard 'web' por defecto
     */
    protected string $guard_name = 'web';

    /**
     * Nombre de la tabla en la base de datos
     * 
     * Por defecto Laravel busca 'user_models' (plural del nombre de la clase)
     * Como nuestra tabla se llama 'users', debemos especificarlo.
     */
    protected $table = 'users';

    /**
     * Campos que pueden ser asignados masivamente
     * 
     * ¿Qué significa "mass assignment"?
     * Poder hacer: UserModel::create(['name' => 'Juan', 'email' => 'juan@example.com'])
     * 
     * Sin $fillable, esto lanzaría error de seguridad (previene ataques)
     * Con $fillable, defines qué campos SÍ se pueden llenar así.
     * 
     * REGLA: NUNCA pongas campos sensibles como 'is_admin' aquí
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'email_verified_at',
    ];

    /**
     * Campos que deben estar ocultos en arrays/JSON
     * 
     * Cuando conviertes el modelo a JSON (ej: en una API),
     * estos campos NO se incluyen.
     * 
     * SEGURIDAD: Nunca expongas passwords o tokens
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversiones de tipos (casts)
     * 
     * Eloquent convierte automáticamente estos campos:
     * 
     * - 'email_verified_at' => 'datetime'
     *   La BD guarda string '2024-01-15 10:30:00'
     *   PHP recibe objeto Carbon (fácil de manipular fechas)
     *   Puedes hacer: $user->email_verified_at->format('d/m/Y')
     * 
     * - 'password' => 'hashed'
     *   Al asignar: $user->password = 'texto'
     *   Automáticamente lo hashea con bcrypt
     * 
     * - 'is_active' => 'boolean'
     *   La BD guarda 0 o 1
     *   PHP recibe true o false
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ============================================
    // RELACIONES (las agregaremos después)
    // ============================================
    
    /**
     * Relación: Un usuario tiene muchos roles
     * 
     * Ejemplo de uso:
     * $user = UserModel::find(1);
     * $roles = $user->roles; // Trae todos los roles del usuario
     */
    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class);
    // }

    // ============================================
    // SCOPES (filtros reutilizables)
    // ============================================
    
    /**
     * Scope: Solo usuarios activos
     * 
     * Uso: UserModel::active()->get()
     * SQL: SELECT * FROM users WHERE is_active = 1
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Solo usuarios verificados
     * 
     * Uso: UserModel::verified()->get()
     * SQL: SELECT * FROM users WHERE email_verified_at IS NOT NULL
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope: Buscar por email
     * 
     * Uso: UserModel::byEmail('juan@example.com')->first()
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    // ============================================
    // ACCESSORS & MUTATORS (getters/setters)
    // ============================================
    
    /**
     * Accessor: Obtener nombre en mayúsculas
     * 
     * Uso: $user->name_uppercase
     * 
     * Nota: Esto es solo un ejemplo didáctico
     */
    // public function getNameUppercaseAttribute(): string
    // {
    //     return strtoupper($this->name);
    // }

    // ============================================
    // MÉTODOS HELPER
    // ============================================

    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Verificar si el email está verificado
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Marcar email como verificado
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
}