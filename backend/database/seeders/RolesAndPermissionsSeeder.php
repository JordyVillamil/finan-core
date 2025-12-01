<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder para Roles y Permisos
 * 
 * PROPÓSITO:
 * Crear los roles y permisos iniciales del sistema.
 * 
 * EJECUTAR:
 * php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================================
        // 1. RESETEAR CACHE DE PERMISOS
        // ============================================
        
        /**
         * Spatie cachea permisos para performance.
         * Reseteamos antes de crear nuevos.
         */
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // 2. CREAR PERMISOS
        // ============================================
        
        $this->createPermissions();

        // ============================================
        // 3. CREAR ROLES Y ASIGNAR PERMISOS
        // ============================================
        
        $this->createRoles();
    }

    /**
     * Crear permisos del sistema
     */
    private function createPermissions(): void
    {
        /**
         * ESTRUCTURA DE PERMISOS:
         * Seguimos el patrón: {acción}-{recurso}
         * 
         * Acciones: view, create, edit, delete, manage
         * Recursos: users, roles, invoices, reports, etc.
         */
        
        $permissions = [
            // ============================================
            // PERMISOS DE USUARIOS
            // ============================================
            'view-users',          // Ver listado de usuarios
            'create-users',        // Crear nuevos usuarios
            'edit-users',          // Editar usuarios existentes
            'delete-users',        // Eliminar usuarios
            'manage-users',        // Gestión completa de usuarios

            // ============================================
            // PERMISOS DE ROLES Y PERMISOS
            // ============================================
            'view-roles',          // Ver roles
            'create-roles',        // Crear roles
            'edit-roles',          // Editar roles
            'delete-roles',        // Eliminar roles
            'assign-roles',        // Asignar roles a usuarios

            // ============================================
            // PERMISOS DE FACTURAS
            // ============================================
            'view-invoices',       // Ver facturas
            'create-invoices',     // Crear facturas
            'edit-invoices',       // Editar facturas
            'delete-invoices',     // Eliminar facturas
            'approve-invoices',    // Aprobar facturas
            'cancel-invoices',     // Cancelar facturas

            // ============================================
            // PERMISOS DE REPORTES
            // ============================================
            'view-reports',        // Ver reportes
            'export-reports',      // Exportar reportes
            'view-financial-reports', // Ver reportes financieros

            // ============================================
            // PERMISOS DE EMPRESAS
            // ============================================
            'view-companies',      // Ver empresas
            'create-companies',    // Crear empresas
            'edit-companies',      // Editar empresas
            'delete-companies',    // Eliminar empresas

            // ============================================
            // PERMISOS DE PRODUCTOS
            // ============================================
            'view-products',       // Ver productos
            'create-products',     // Crear productos
            'edit-products',       // Editar productos
            'delete-products',     // Eliminar productos

            // ============================================
            // PERMISOS DE CLIENTES
            // ============================================
            'view-clients',        // Ver clientes
            'create-clients',      // Crear clientes
            'edit-clients',        // Editar clientes
            'delete-clients',      // Eliminar clientes

            // ============================================
            // PERMISOS DE CONFIGURACIÓN
            // ============================================
            'view-settings',       // Ver configuración
            'edit-settings',       // Editar configuración
        ];

        // Crear cada permiso
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api', // Para API con Sanctum
            ]);
        }

        $this->command->info('✅ Permisos creados: ' . count($permissions));
    }

    /**
     * Crear roles y asignar permisos
     */
    private function createRoles(): void
    {
        // ============================================
        // ROL: SUPER ADMIN
        // ============================================
        
        /**
         * Super Admin tiene TODOS los permisos
         * Es el usuario principal del sistema
         */
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'api',
        ]);
        
        // Asignar TODOS los permisos
        $superAdmin->syncPermissions(Permission::all());
        
        $this->command->info('✅ Rol creado: Super Admin (todos los permisos)');

        // ============================================
        // ROL: ADMIN
        // ============================================
        
        /**
         * Admin puede gestionar usuarios, empresas, clientes
         * NO puede editar configuración del sistema
         */
        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'api',
        ]);
        
        $admin->syncPermissions([
            'manage-users',
            'view-roles',
            'assign-roles',
            'view-companies',
            'create-companies',
            'edit-companies',
            'view-clients',
            'create-clients',
            'edit-clients',
            'view-invoices',
            'view-reports',
        ]);
        
        $this->command->info('✅ Rol creado: Admin');

        // ============================================
        // ROL: CONTADOR (ACCOUNTANT)
        // ============================================
        
        /**
         * Contador puede crear facturas, productos, clientes
         * Puede ver reportes financieros
         */
        $accountant = Role::firstOrCreate([
            'name' => 'Contador',
            'guard_name' => 'api',
        ]);
        
        $accountant->syncPermissions([
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'view-products',
            'create-products',
            'edit-products',
            'view-clients',
            'create-clients',
            'edit-clients',
            'view-reports',
            'view-financial-reports',
            'export-reports',
        ]);
        
        $this->command->info('✅ Rol creado: Contador');

        // ============================================
        // ROL: AUDITOR
        // ============================================
        
        /**
         * Auditor solo puede VER
         * No puede crear, editar ni eliminar
         */
        $auditor = Role::firstOrCreate([
            'name' => 'Auditor',
            'guard_name' => 'api',
        ]);
        
        $auditor->syncPermissions([
            'view-users',
            'view-invoices',
            'view-products',
            'view-clients',
            'view-companies',
            'view-reports',
            'view-financial-reports',
            'export-reports',
        ]);
        
        $this->command->info('✅ Rol creado: Auditor');

        // ============================================
        // ROL: VENDEDOR (SALESPERSON)
        // ============================================
        
        /**
         * Vendedor puede crear facturas y ver clientes
         * No puede ver reportes financieros
         */
        $salesperson = Role::firstOrCreate([
            'name' => 'Vendedor',
            'guard_name' => 'api',
        ]);
        
        $salesperson->syncPermissions([
            'view-invoices',
            'create-invoices',
            'view-clients',
            'create-clients',
            'view-products',
        ]);
        
        $this->command->info('✅ Rol creado: Vendedor');

        // ============================================
        // ROL: USUARIO (USER)
        // ============================================
        
        /**
         * Usuario básico con permisos mínimos
         * Solo puede ver su propia información
         */
        $user = Role::firstOrCreate([
            'name' => 'Usuario',
            'guard_name' => 'api',
        ]);
        
        $user->syncPermissions([
            'view-invoices',
            'view-products',
        ]);
        
        $this->command->info('✅ Rol creado: Usuario');
    }
}