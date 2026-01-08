<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create permissions for super admin
        $permissions = [
            'view all tenants',
            'manage tenants',
            'view all employees',
            'manage all employees',
            'view all departments',
            'manage all departments',
            'view all time-offs',
            'manage all time-offs',
            'access system settings',
            'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to super admin
        $adminRole->syncPermissions(Permission::all());

        // Create Super Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@onboard.uz'],
            [
                'name' => 'Oboard Administrator',
                'password' => Hash::make('admin123'),
                'company_id' => null,
            ]
        );

        // Assign super admin role
        $admin->assignRole('admin');

        $this->command->info('✅ Admin created successfully!');
        $this->command->info('📧 Email: admin@onboard.uz');
        $this->command->info('🔑 Password: admin123 (Change this in production!)');
    }
}
