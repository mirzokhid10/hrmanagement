<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Define Permissions ---
        $permissions = [
            // User & Role Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'view audit logs',

            // Employee Management
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee sensitive data',
            'edit employee sensitive data',

            // Department Management
            'manage departments',

            // Time-Off Management
            'view all time-off requests',
            'approve time-off requests',
            'reject time-off requests',
            'manage time-off types',

            // Add more permissions as needed...
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Create Roles ---
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $hrRole = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']); // Changed from 'hr_manager'

        // --- Assign Permissions ---

        // Admin: Full control
        $adminPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'view audit logs',
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee sensitive data',
            'edit employee sensitive data',
            'manage departments',
            'view all time-off requests',
            'approve time-off requests',
            'reject time-off requests',
            'manage time-off types',
        ];
        $adminRole->givePermissionTo($adminPermissions);

        // HR: Same as admin (since they're the only user for the company)
        $hrRole->givePermissionTo($adminPermissions);

        $this->command->info('Roles and permissions created successfully.');
        $this->command->info('Created roles: admin, hr');
    }
}
