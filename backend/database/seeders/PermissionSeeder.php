<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Permission groups with their actions.
     * Format: 'group_name' => ['action1', 'action2', ...]
     * Full permission name = "{action} {group}"
     */
    public static array $groups = [
        'dashboard'  => ['view'],
        'products'   => ['view', 'create', 'edit', 'delete'],
        'categories' => ['view', 'create', 'edit', 'delete'],
        'orders'     => ['view', 'edit'],
        'users'      => ['view', 'edit'],
        'settings'   => ['view', 'edit'],
        'roles'      => ['view', 'create', 'edit', 'delete'],
        'permissions'=> ['view', 'create', 'edit', 'delete'],
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::$groups as $group => $actions) {
            foreach ($actions as $action) {
                $name = "{$action} {$group}";
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['name' => $name, 'guard_name' => 'web', 'group_name' => $group]
                );
            }
        }

        // Back-fill group_name for any existing permissions that don't have it yet
        Permission::whereNull('group_name')->get()->each(function ($perm) {
            $parts = explode(' ', $perm->name, 2);
            if (isset($parts[1])) {
                $perm->update(['group_name' => $parts[1]]);
            }
        });

        // Admin role gets ALL permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Customer role exists but has no admin permissions
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Assign admin role to all admin users
        User::where('role', 'admin')->each(function ($user) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
        });

        $this->command->info('✓ ' . Permission::count() . ' permissions across ' . count(self::$groups) . ' groups.');
    }
}
