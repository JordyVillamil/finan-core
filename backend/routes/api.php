<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Company\CompanyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí registramos todas las rutas de la API.
| Estas rutas tienen el prefijo /api automáticamente.
|
| Ejemplo: Route::post('/login', ...) -> http://localhost:8000/api/login
|
*/

// ============================================
// RUTAS PÚBLICAS (sin autenticación)
// ============================================

Route::prefix('auth')->group(function () {
    /**
     * POST /api/auth/register
     * Registrar nuevo usuario
     */
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');

    /**
     * POST /api/auth/login
     * Iniciar sesión
     */
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    /**
     * POST /api/auth/forgot-password
     * Solicitar reset de contraseña
     */
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password');

    /**
     * POST /api/auth/reset-password
     * Restablecer contraseña con token
     */
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('auth.reset-password');

    /**
     * POST /api/auth/verify-email
     * Verificar email con token
     */
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])
        ->name('auth.verify-email');

    /**
     * POST /api/auth/resend-verification
     * Reenviar email de verificación
     */
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])
        ->name('auth.resend-verification');
});

// ============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// ============================================

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    /**
     * POST /api/auth/logout
     * Cerrar sesión
     */
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    /**
     * GET /api/auth/me
     * Obtener datos del usuario autenticado
     */
    Route::get('/me', [AuthController::class, 'me'])
        ->name('auth.me');
});

// ============================================
// RUTA DE HEALTH CHECK
// ============================================

/**
 * GET /api/health
 * Verificar que la API está funcionando
 */
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now()->toDateTimeString(),
        'environment' => app()->environment(),
    ]);
})->name('health');

// ============================================
// RUTAS DE ROLES Y PERMISOS (protegidas)
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    /**
     * GET /api/roles
     * Listar todos los roles
     */
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:view-roles')
        ->name('roles.index');

    /**
     * GET /api/permissions
     * Listar todos los permisos
     */
    Route::get('/permissions', [RoleController::class, 'permissions'])
        ->middleware('permission:view-roles')
        ->name('permissions.index');

    /**
     * GET /api/auth/roles
     * Obtener mis roles y permisos
     */
    Route::get('/auth/roles', [RoleController::class, 'myRoles'])
        ->name('auth.roles');

    /**
     * POST /api/users/{userId}/roles
     * Asignar rol a usuario
     */
    Route::post('/users/{userId}/roles', [RoleController::class, 'assignRole'])
        ->middleware('permission:assign-roles')
        ->name('users.roles.assign');

    /**
     * DELETE /api/users/{userId}/roles/{role}
     * Remover rol de usuario
     */
    Route::delete('/users/{userId}/roles/{role}', [RoleController::class, 'removeRole'])
        ->middleware('permission:assign-roles')
        ->name('users.roles.remove');
});

// ============================================
// RUTAS DE EMPRESAS
// ============================================

Route::middleware(['auth:sanctum'])->group(function () {
    
    // CRUD de empresas
    Route::get('/companies', [CompanyController::class, 'index'])
        ->middleware('permission:view-companies');
    
    Route::post('/companies', [CompanyController::class, 'store'])
        ->middleware('permission:create-companies');
    
    Route::get('/companies/{id}', [CompanyController::class, 'show'])
        ->middleware('permission:view-companies');
    
    Route::put('/companies/{id}', [CompanyController::class, 'update'])
        ->middleware('permission:edit-companies');
    
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])
        ->middleware('permission:delete-companies');
    
    // Activar/Desactivar
    Route::post('/companies/{id}/activate', [CompanyController::class, 'activate'])
        ->middleware('permission:manage-companies');
    
    Route::post('/companies/{id}/deactivate', [CompanyController::class, 'deactivate'])
        ->middleware('permission:manage-companies');
});