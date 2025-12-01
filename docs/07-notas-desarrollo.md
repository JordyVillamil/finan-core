# 📝 Notas de Desarrollo

Este documento contiene anotaciones importantes descubiertas durante el desarrollo del proyecto.

---

## 🔧 Validación de Email

### Problema
La validación `email:rfc,dns` en Laravel intenta verificar que el dominio del email exista realmente mediante consulta DNS. Esto causa que emails con dominios de prueba como `example.com` fallen la validación.

### Error típico
```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": {
    "email": ["El email debe ser una dirección válida."]
  }
}
```

### Solución

**Desarrollo** - Usar solo validación de formato:
```php
'email' => [
    'required',
    'string',
    'email:rfc',       // Solo valida formato RFC
    'max:255',
    'unique:users,email',
],
```

**Producción** - Agregar validación DNS:
```php
'email' => [
    'required',
    'string',
    'email:rfc,dns',   // Valida formato + verifica DNS
    'max:255',
    'unique:users,email',
],
```

### Ubicación
`app/Http/Requests/Auth/RegisterRequest.php`

---

## 🌐 Puertos del Proyecto

| Servicio | URL | Puerto |
|----------|-----|--------|
| Backend (Laravel/NGINX) | http://localhost | 80 |
| Frontend (Vite/React) | http://localhost:3000 | 3000 |
| Mailhog UI | http://localhost:8025 | 8025 |
| PostgreSQL | localhost:5432 | 5432 |
| Redis | localhost:6379 | 6379 |

> ⚠️ **Nota:** El backend NO usa el puerto 8000 (como el servidor de desarrollo de Laravel). Usa NGINX en el puerto 80.

---

## 🛣️ Rutas API en Laravel 12

### Problema
En Laravel 12, las rutas de API (`routes/api.php`) no se cargan automáticamente por defecto.

### Solución
Agregar la configuración en `bootstrap/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',    // ← Agregar esta línea
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
```

---

## 🗄️ Base de Datos - SoftDeletes

### Problema
Si el modelo usa `SoftDeletes` pero la migración no se ejecutó con `$table->softDeletes()`, aparece el error:

```
SQLSTATE[42703]: Undefined column: column users.deleted_at does not exist
```

### Solución
**Opción 1 (Desarrollo):** Recrear la base de datos
```powershell
docker-compose exec php php artisan migrate:fresh
```

**Opción 2 (Producción):** Crear migración para agregar la columna
```powershell
docker-compose exec php php artisan make:migration add_deleted_at_to_users_table --table=users
```

---

## 🐘 PHP 8.5 y Compatibilidad

### Herramientas compatibles
- ✅ PHPUnit 11.x
- ✅ PHPStan 2.x
- ✅ PHP-CS-Fixer 3.x
- ✅ Laravel 12.x

### Herramientas NO compatibles
- ❌ Pest (requiere PHP 8.2-8.4)

### Solución para Pest
Si necesitas Pest, instálalo dentro del contenedor Docker (PHP 8.2):
```powershell
docker-compose exec php composer require --dev pestphp/pest pestphp/pest-plugin-laravel
```

---

## 📦 Node.js y Vite 7

### Requisito
Vite 7 requiere **Node.js 20.19+** o **22.12+**

### Error típico
```
Vite requires Node.js version 20.19+, 22.12+, or 24+
```

### Solución
El Dockerfile del frontend ya usa `node:22-alpine`:
```dockerfile
FROM node:22-alpine
```

---

## 🎨 Tailwind CSS v4

### Cambio importante
Tailwind v4 usa `@tailwindcss/postcss` en lugar del plugin tradicional `tailwindcss`.

### Configuración correcta

**postcss.config.js:**
```javascript
// ✅ CORRECTO (Tailwind v4)
export default {
  plugins: {
    '@tailwindcss/postcss': {},
  },
}

// ❌ INCORRECTO (Tailwind v3)
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

---

## 🐳 Docker php.ini

### Problema
El archivo `docker/php/php.ini` NO debe contener paths de Windows, ya que se monta en un contenedor Linux.

### Configuración incorrecta ❌
```ini
extension_dir = "C:\php\ext"
extension=php_fileinfo.dll
extension=php_zip.dll
```

### Configuración correcta ✅
```ini
; Solo configuraciones generales
max_execution_time = 300
memory_limit = 256M
date.timezone = America/Bogota
```

Las extensiones ya están instaladas en el Dockerfile.

---

## 🔐 Facades de Laravel

### Problema
El IDE/PHPStan puede mostrar errores con helpers como `auth()` y `\Log`.

### Solución
Usar Facades con imports explícitos:

```php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// En lugar de:
$user = auth()->user();
\Log::error('mensaje');

// Usar:
$user = Auth::user();
Log::error('mensaje');
```

---

## 📋 Comandos Útiles

### Ejecutar comandos Artisan en Docker
```powershell
docker-compose exec php php artisan <comando>
```

### Ver rutas de la API
```powershell
docker-compose exec php php artisan route:list --path=api
```

### Recrear base de datos
```powershell
docker-compose exec php php artisan migrate:fresh
```

### Ver logs del contenedor
```powershell
docker-compose logs -f php
```

### Probar endpoint de health
```powershell
Invoke-WebRequest -Uri "http://localhost/api/health" -UseBasicParsing
```

---

## 🧪 Probar API con PowerShell

### Registrar usuario
```powershell
$body = '{"name":"Juan Perez","email":"juan@example.com","password":"password123","password_confirmation":"password123"}'
Invoke-RestMethod -Uri "http://localhost/api/auth/register" -Method POST -Body $body -ContentType "application/json"
```

### Login y obtener token
```powershell
$body = '{"email":"juan@example.com","password":"password123"}'
$response = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method POST -Body $body -ContentType "application/json"
$token = $response.data.token
Write-Host "Token: $token"
```

### Usar token para endpoints protegidos
```powershell
# Primero hacer login (ver arriba) para obtener $token
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

# Obtener datos del usuario autenticado
Invoke-RestMethod -Uri "http://localhost/api/auth/me" -Method GET -Headers $headers

# Obtener mis roles y permisos
Invoke-RestMethod -Uri "http://localhost/api/auth/roles" -Method GET -Headers $headers

# Logout
Invoke-RestMethod -Uri "http://localhost/api/auth/logout" -Method POST -Headers $headers
```

### Script completo de prueba
```powershell
# Login + consultar roles en un solo comando
$loginBody = '{"email":"juan@example.com","password":"password123"}'
$loginResponse = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method POST -Body $loginBody -ContentType "application/json"
$token = $loginResponse.data.token
$headers = @{"Authorization" = "Bearer $token"; "Accept" = "application/json"}
Invoke-RestMethod -Uri "http://localhost/api/auth/roles" -Method GET -Headers $headers
```

> ⚠️ **IMPORTANTE:** El backend usa el puerto **80** (http://localhost), NO el puerto 8000.

---

## 📅 Historial de Cambios

| Fecha | Nota |
|-------|------|
| 2025-11-28 | Documentación inicial creada |
| 2025-11-28 | Agregada nota sobre validación de email |
| 2025-11-28 | Agregada nota sobre puertos |
| 2025-11-28 | Agregada nota sobre rutas API en Laravel 12 |
| 2025-11-28 | Agregada nota sobre password hashing y BCRYPT_ROUNDS |
| 2025-11-30 | Agregada nota sobre configuración de Sanctum y autenticación |
| 2025-11-30 | Agregada nota sobre Spatie Permission y guards |
| 2025-11-30 | Agregada nota sobre seeder idempotente de roles |
| 2025-11-30 | Agregada nota sobre sistema de emails con Mailhog |
| 2025-11-30 | Implementado reset de password completo |
| 2025-11-30 | Implementado verificación de email completo |
| 2025-11-30 | Agregados scripts de prueba para tokens |

---

## 📧 Sistema de Emails - Mailhog

### Configuración
El proyecto usa **Mailhog** como servidor de correo local para desarrollo:

**Configuración en `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@finan-core.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Acceso
- **UI de Mailhog:** http://localhost:8025
- Todos los emails enviados aparecen ahí (no se envían a internet)

### Probar envío de emails
```powershell
docker-compose exec php php test-mail.php
```

### Estructura de emails
```
backend/
├── app/Mail/
│   └── VerifyEmailMail.php          # Mailable de verificación
├── resources/views/emails/
│   └── verify-email.blade.php       # Template HTML del email
└── test-mail.php                    # Script de prueba
```

### Crear nuevo Mailable
```powershell
docker-compose exec php php artisan make:mail NombreDelMail
```

### Flujo de Verificación de Email

```
┌─────────────────────────────────────────────────────────┐
│  1. USUARIO SE REGISTRA                                  │
│     POST /api/auth/register                             │
│     { name, email, password }                           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  2. SISTEMA CREA USUARIO                                │
│     - email_verified_at = NULL (no verificado)          │
│     - Genera token de verificación                      │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  3. ENVÍA EMAIL CON LINK                                │
│     To: usuario@example.com                             │
│     Link: https://app.com/verify?token=abc123           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  4. USUARIO HACE CLIC EN LINK                           │
│     GET /api/auth/verify?token=abc123                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  5. SISTEMA VERIFICA TOKEN                              │
│     - Busca token en BD                                 │
│     - Verifica que no haya expirado                     │
│     - Marca email_verified_at = NOW()                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  6. USUARIO VERIFICADO ✅                               │
│     Puede acceder a todas las funciones                 │
└─────────────────────────────────────────────────────────┘
```

### Verificar manualmente en BD
```powershell
# Ver estado de verificación de un usuario
docker-compose exec postgres psql -U proyecto_user -d proyecto_contable -c "SELECT id, name, email, email_verified_at FROM users;"

# Marcar usuario como verificado manualmente
docker-compose exec postgres psql -U proyecto_user -d proyecto_contable -c "UPDATE users SET email_verified_at = NOW() WHERE email = 'usuario@example.com';"
```

---

## 🔑 Reset de Password

### Flujo Completo

```
┌─────────────────────────────────────────────────────────┐
│  1. USUARIO OLVIDÓ SU CONTRASEÑA                         │
│     POST /api/auth/forgot-password                      │
│     { email: "usuario@example.com" }                    │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  2. SISTEMA GENERA TOKEN                                │
│     - Crea token único                                  │
│     - Guarda hash en tabla password_reset_tokens        │
│     - Expira en 60 minutos                              │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  3. ENVÍA EMAIL CON LINK                                │
│     To: usuario@example.com                             │
│     Link: http://localhost/reset?token=abc123           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  4. USUARIO HACE CLIC Y ENVÍA NUEVA CONTRASEÑA          │
│     POST /api/auth/reset-password                       │
│     { token, email, password, password_confirmation }   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  5. SISTEMA VALIDA Y CAMBIA CONTRASEÑA                  │
│     - Verifica token                                    │
│     - Verifica que no expiró                            │
│     - Cambia contraseña                                 │
│     - Elimina token usado                               │
└─────────────────────────────────────────────────────────┘
```

### Probar sin Frontend

Como el frontend aún no está implementado, usar el script de prueba:

```powershell
# 1. Generar token de prueba (muestra el token en consola)
docker-compose exec php php test-reset-password.php

# 2. Usar el comando que genera el script para hacer el reset
$body = @{
    token = "TOKEN_GENERADO"
    email = "testuser@example.com"
    password = "newpassword123"
    password_confirmation = "newpassword123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/api/auth/reset-password" -Method POST -Body $body -ContentType "application/json"

# 3. Probar login con nueva contraseña
$body = '{"email":"testuser@example.com","password":"newpassword123"}'
Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method POST -Body $body -ContentType "application/json"
```

### Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/forgot-password` | Solicitar reset (envía email) |
| POST | `/api/auth/reset-password` | Cambiar contraseña con token |
| POST | `/api/auth/verify-email` | Verificar email con token |
| POST | `/api/auth/resend-verification` | Reenviar email de verificación |

---

## 🔄 Seeder Idempotente de Roles

### Problema
El seeder de roles fallaba si se ejecutaba más de una vez:
```
A `view-users` permission already exists for guard `api`.
```

### Solución
Usar `firstOrCreate` en lugar de `create` y `syncPermissions` en lugar de `givePermissionTo`:

```php
// ❌ INCORRECTO - Falla si ya existe
Permission::create(['name' => 'view-users', 'guard_name' => 'api']);
$role->givePermissionTo(['view-users']);

// ✅ CORRECTO - Idempotente
Permission::firstOrCreate(['name' => 'view-users', 'guard_name' => 'api']);
$role->syncPermissions(['view-users']);
```

### Ejecutar seeder
```powershell
# Se puede ejecutar múltiples veces sin error
docker-compose exec php php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Ubicación
`database/seeders/RolesAndPermissionsSeeder.php`

---

## 🔐 Laravel Sanctum - Configuración para API

### Problema
Los tokens generados en login no funcionaban para autenticación (401 Unauthorized).

### Causas encontradas

1. **UserModel extendía `Model` en lugar de `Authenticatable`**
   - Sanctum requiere que el modelo extienda `Illuminate\Foundation\Auth\User`

2. **Provider de auth apuntaba al modelo incorrecto**
   - `config/auth.php` usaba `App\Models\User`
   - Debía usar `App\Infrastructure\Persistence\Eloquent\Models\UserModel`

3. **Token no se generaba con Sanctum**
   - `LoginUserService` usaba `base64_encode()` (implementación temporal)
   - Debía usar `$userModel->createToken('web')->plainTextToken`

### Soluciones aplicadas

**1. UserModel debe extender Authenticatable:**
```php
// ❌ INCORRECTO
use Illuminate\Database\Eloquent\Model;
class UserModel extends Model

// ✅ CORRECTO
use Illuminate\Foundation\Auth\User as Authenticatable;
class UserModel extends Authenticatable
```

**2. config/auth.php - Provider correcto:**
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Infrastructure\Persistence\Eloquent\Models\UserModel::class,
    ],
],
```

**3. LoginUserService - Usar Sanctum:**
```php
// Obtener el UserModel de Eloquent
$userModel = UserModel::find($user->id()->value());

// Generar token con Sanctum
$token = $userModel->createToken('web')->plainTextToken;
```

### Ubicaciones
- `app/Infrastructure/Persistence/Eloquent/Models/UserModel.php`
- `config/auth.php`
- `app/Application/Auth/Services/LoginUserService.php`

---

## 🛡️ Spatie Permission - Guards

### Problema
Al registrar usuario aparecía el error:
```
There is no role named `Usuario` for guard `web`.
```

### Causa
- Los roles se crearon con `guard_name = 'api'` en el seeder
- El UserModel no especificaba el guard y usaba `web` por defecto

### Solución
Agregar `$guard_name` al UserModel:

```php
class UserModel extends Authenticatable
{
    use HasRoles;

    /**
     * Guard para Spatie Permission
     */
    protected string $guard_name = 'api';
    
    // ...
}
```

### Ubicación
`app/Infrastructure/Persistence/Eloquent/Models/UserModel.php`

---

## 🔑 Password Hashing y BCRYPT_ROUNDS

### Problema
Los Feature tests fallaban con el error:
```
Could not verify the hashed value's configuration
```

### Causa
- `phpunit.xml` define `BCRYPT_ROUNDS=4` para tests más rápidos
- La entidad `User.php` usaba `password_hash(..., ['cost' => 12])`
- Laravel 12 con `bcrypt.verify=true` verifica que el cost del hash coincida con la configuración

### Solución
Crear un método helper en la entidad que use `Hash::make()` cuando Laravel está disponible:

```php
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
```

### Ventajas de esta solución
1. **Feature tests**: Usan `Hash::make()` que respeta `BCRYPT_ROUNDS=4`
2. **Unit tests puros**: Usan fallback con `password_hash()` sin container
3. **Producción**: Usa `Hash::make()` con `BCRYPT_ROUNDS=12` (por defecto)
4. **La entidad sigue siendo independiente** del framework cuando se necesita

### Ubicación
`app/Domain/Auth/Entities/User.php`

# Ejecutar solo tests unitarios
php artisan test --testsuite=Unit

# Ejecutar solo tests de feature
php artisan test --testsuite=Feature

# Ejecutar un test específico
php artisan test --filter=EmailTest

# Ejecutar con cobertura (requiere Xdebug)
php artisan test --coverage

# Ejecutar con más detalles
php artisan test --verbose

# Ejecutar tests en paralelo (más rápido)
php artisan test --parallel
```

---

## ✅ Estado Actual del Proyecto (Noviembre 2025)

### Contenedores Docker
```
6 contenedores corriendo:
- nginx (puerto 80)
- php (backend Laravel)
- postgres (base de datos)
- redis (cache)
- mailhog (email testing - puerto 8025)
- frontend (Vite dev server - puerto 3000)
```

### Autenticación Implementada
- ✅ Laravel Sanctum para tokens API
- ✅ Login, Registro, Logout funcionando
- ✅ Rutas protegidas con middleware `auth:sanctum`
- ✅ Spatie Permission para roles y permisos
- ✅ Guard 'api' configurado correctamente
- ✅ Reset de password (forgot-password + reset-password)
- ✅ Verificación de email (verify-email + resend-verification)

### Tests
- ✅ 43 tests pasando
- ✅ Feature tests con setUp() para seedear roles
- ✅ Unit tests de entidades
- ✅ Tests de API endpoints

### Sistema de Emails
- ✅ Mailhog configurado para desarrollo
- ✅ VerifyEmailMail mailable creado
- ✅ ResetPasswordMail mailable creado
- ✅ Templates Blade para emails
- ✅ Tokens de verificación y reset funcionando

### Roles y Permisos
- Roles: admin, user, accountant, viewer
- Permisos: view-users, create-users, edit-users, delete-users
- Seeder idempotente (se puede ejecutar múltiples veces)

### Scripts de Prueba
- `test-mail.php` - Probar envío de emails
- `test-reset-password.php` - Generar token de reset y mostrar comando de prueba

### Próximos Pasos
- [ ] Implementar módulo de facturación
- [ ] Conectar frontend con API
- [ ] Agregar tests para reset/verify email