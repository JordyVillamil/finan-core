<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Eloquent Model: CompanyModel
 * 
 * Mapea la tabla 'companies' en la base de datos.
 * 
 * PROPÓSITO:
 * - Interactuar con la tabla companies
 * - Definir relaciones con otras tablas
 * - Configurar casts, fillable, etc.
 * 
 * IMPORTANTE:
 * Este es el Model de INFRAESTRUCTURA, NO el Entity del DOMINIO.
 * - Model: Para persistencia (Eloquent)
 * - Entity: Para lógica de negocio (Domain)
 * 
 * @property int $id
 * @property string $name
 * @property string $legal_name
 * @property string $tax_id
 * @property string $email
 * @property string|null $phone
 * @property string|null $address_street
 * @property string|null $address_city
 * @property string|null $address_state
 * @property string $address_country
 * @property string|null $address_postal_code
 * @property string|null $tax_regime
 * @property string|null $logo_path
 * @property string $invoice_series
 * @property int $next_invoice_number
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class CompanyModel extends Model
{
    use HasFactory;     // Para factories de testing
    use SoftDeletes;    // Para soft delete (deleted_at)

    /**
     * Nombre de la tabla
     * 
     * @var string
     */
    protected $table = 'companies';

    /**
     * Atributos asignables en masa (mass assignment)
     * 
     * SEGURIDAD:
     * Solo estos campos pueden asignarse con fill() o create()
     * 
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'legal_name',
        'tax_id',
        'email',
        'phone',
        'address_street',
        'address_city',
        'address_state',
        'address_country',
        'address_postal_code',
        'tax_regime',
        'logo_path',
        'invoice_series',
        'next_invoice_number',
        'is_active',
    ];

    /**
     * Atributos que deben ser casteados
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'next_invoice_number' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ============================================
    // RELACIONES
    // ============================================

    /**
     * Relación: Usuarios de la empresa (muchos a muchos)
     * 
     * Una empresa puede tener múltiples usuarios.
     * La tabla pivot es 'company_user'.
     * 
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            UserModel::class,
            'company_user',      // Tabla pivot
            'company_id',        // FK de esta tabla
            'user_id'            // FK de la otra tabla
        )
        ->withPivot('role')      // Incluir columna 'role' del pivot
        ->withTimestamps();      // Incluir created_at del pivot
    }

    // ============================================
    // SCOPES (Query Builders)
    // ============================================

    /**
     * Scope: Solo empresas activas
     * 
     * Uso: CompanyModel::active()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Solo empresas inactivas
     * 
     * Uso: CompanyModel::inactive()->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Buscar por RFC/NIT
     * 
     * Uso: CompanyModel::byTaxId('ABC123')->first()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $taxId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTaxId($query, string $taxId)
    {
        return $query->where('tax_id', $taxId);
    }

    /**
     * Scope: Buscar por nombre (LIKE)
     * 
     * Uso: CompanyModel::searchByName('Tech')->get()
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByName($query, string $searchTerm)
    {
        return $query->where('name', 'ILIKE', "%{$searchTerm}%")
                    ->orWhere('legal_name', 'ILIKE', "%{$searchTerm}%");
    }

    // ============================================
    // MÉTODOS HELPER
    // ============================================

    /**
     * Verificar si la empresa está activa
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Verificar si la empresa está inactiva
     * 
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->is_active === false;
    }

    /**
     * Obtener dirección completa en una línea
     * 
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_street,
            $this->address_city,
            $this->address_state,
            $this->address_postal_code,
            $this->address_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Obtener próximo número de factura formateado
     * 
     * @return string Ej: "A-0001"
     */
    public function getNextInvoiceNumberFormatted(): string
    {
        return $this->invoice_series . '-' . 
               str_pad((string)$this->next_invoice_number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Incrementar contador de facturas
     * 
     * @return void
     */
    public function incrementInvoiceNumber(): void
    {
        $this->increment('next_invoice_number');
    }

    /**
     * Activar empresa
     * 
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Desactivar empresa
     * 
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Verificar si un usuario pertenece a esta empresa
     * 
     * @param int $userId
     * @return bool
     */
    public function hasUser(int $userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    /**
     * Agregar usuario a la empresa
     * 
     * @param int $userId
     * @param string $role Role en la empresa (owner, admin, accountant, employee, viewer)
     * @return void
     */
    public function addUser(int $userId, string $role = 'employee'): void
    {
        if (!$this->hasUser($userId)) {
            $this->users()->attach($userId, ['role' => $role]);
        }
    }

    /**
     * Remover usuario de la empresa
     * 
     * @param int $userId
     * @return void
     */
    public function removeUser(int $userId): void
    {
        $this->users()->detach($userId);
    }

    /**
     * Cambiar rol de usuario en la empresa
     * 
     * @param int $userId
     * @param string $newRole
     * @return void
     */
    public function updateUserRole(int $userId, string $newRole): void
    {
        $this->users()->updateExistingPivot($userId, ['role' => $newRole]);
    }

    /**
     * Obtener rol del usuario en esta empresa
     * 
     * @param int $userId
     * @return string|null
     */
    public function getUserRole(int $userId): ?string
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user?->pivot->role;
    }
}