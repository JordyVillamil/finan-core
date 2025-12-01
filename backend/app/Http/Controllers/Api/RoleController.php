<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Controller para gestión de roles y permisos
 * 
 * RESPONSABILIDADES:
 * - Listar roles y permisos
 * - Asignar/remover roles a usuarios
 * - Verificar permisos
 */
class RoleController extends Controller
{
    /**
     * Listar todos los roles
     * 
     * @return JsonResponse
     * 
     * ENDPOINT: GET /api/roles
     * PERMISSION: view-roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
            ],
        ]);
    }

    /**
     * Listar todos los permisos
     * 
     * @return JsonResponse
     * 
     * ENDPOINT: GET /api/permissions
     * PERMISSION: view-roles
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json([
            'success' => true,
            'data' => [
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * Asignar rol a usuario
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * ENDPOINT: POST /api/users/{userId}/roles
     * PERMISSION: assign-roles
     * 
     * BODY:
     * {
     *   "role": "Contador"
     * }
     */
    public function assignRole(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = UserModel::findOrFail($userId);
        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => "Rol '{$request->role}' asignado exitosamente",
            'data' => [
                'user' => $user->load('roles'),
            ],
        ]);
    }

    /**
     * Remover rol de usuario
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * ENDPOINT: DELETE /api/users/{userId}/roles/{role}
     * PERMISSION: assign-roles
     */
    public function removeRole(int $userId, string $role): JsonResponse
    {
        $user = UserModel::findOrFail($userId);
        $user->removeRole($role);

        return response()->json([
            'success' => true,
            'message' => "Rol '{$role}' removido exitosamente",
            'data' => [
                'user' => $user->load('roles'),
            ],
        ]);
    }

    /**
     * Obtener roles del usuario autenticado
     * 
     * @return JsonResponse
     * 
     * ENDPOINT: GET /api/auth/roles
     */
    public function myRoles(): JsonResponse
    {
        /** @var UserModel $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $user->roles,
                'permissions' => $user->getAllPermissions(),
            ],
        ]);
    }
}