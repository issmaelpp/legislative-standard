<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect([
            // Paneles
            'view_admin_panel',
            
            // User Management
            'view_any_user', // Puede ver la lista de registros
            'create_user',
            'update_user',
            'view_user',
            'delete_user',
            'delete_any_user', // Puede eliminar múltiples registros simultáneamente (bulk delete)
            'force_delete_user', // Puede eliminar permanentemente un registro que se eliminado suavemente
            'force_delete_any_user', // bulk force delete
            'restore_user', // Puede restaurar un registro que se elimino suavemente
            'restore_any_user', // bulk restore
            'reorder_user', // Puede reordenar un registro
        ]);

        // Create permissions if they don't exist
        $permissions->each(function (string $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        });

        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['guard_name' => 'web']
        );
        $superAdminRole->syncPermissions($permissions->toArray());

        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['guard_name' => 'web']
        );
        $adminPermissions = $permissions->filter(function (string $permission) {
            return in_array($permission, [
                'view_any_user',
            ]);
        });
        $adminRole->syncPermissions($adminPermissions->toArray());

        $this->command->info('✅ Roles and permissions seeded successfully!');
        $this->command->info("📊 Created {$permissions->count()} permissions");
    }
}
