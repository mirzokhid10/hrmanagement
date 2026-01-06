<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']); // Note: 'hr' not 'hr_manager'

        // --- Platform Management Company ---
        $platformAdminUser = User::firstOrCreate(
            ['email' => 'admin@platform.com'],
            [
                'name' => 'Platform Admin',
                'email' => 'admin@platform.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $platformCompany = Company::firstOrCreate(
            ['slug' => 'platform-management'],
            [
                'name' => 'Platform Management',
                'subdomain' => null,
                'is_active' => true,
                'user_id' => $platformAdminUser->id,
            ]
        );

        $platformAdminUser->update(['company_id' => $platformCompany->id]);

        // Assign 'admin' role (not super_admin)
        if (!$platformAdminUser->hasRole('admin')) {
            $platformAdminUser->assignRole('admin');
        }

        $this->command->info("Created Platform Management company with ADMIN: {$platformAdminUser->email}");

        // --- Acme Corporation ---
        $acmeUser = User::firstOrCreate(
            ['email' => 'admin@acme.com'],
            [
                'name' => 'Acme HR Manager',
                'email' => 'admin@acme.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $acmeCompany = Company::firstOrCreate(
            ['subdomain' => 'acme'],
            [
                'name' => 'Acme Corporation',
                'slug' => 'acme-corporation',
                'is_active' => true,
                'user_id' => $acmeUser->id,
            ]
        );

        $acmeUser->update(['company_id' => $acmeCompany->id]);

        // Assign 'hr' role
        if (!$acmeUser->hasRole('hr')) {
            $acmeUser->assignRole('hr');
        }

        $this->command->info("Created Acme Corporation with HR: {$acmeUser->email}");

        // --- WidgetCo Inc. ---
        $widgetCoUser = User::firstOrCreate(
            ['email' => 'admin@widgetco.com'],
            [
                'name' => 'WidgetCo HR Manager',
                'email' => 'admin@widgetco.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $widgetCoCompany = Company::firstOrCreate(
            ['subdomain' => 'widgetco'],
            [
                'name' => 'WidgetCo Inc.',
                'slug' => 'widgetco-inc',
                'is_active' => true,
                'user_id' => $widgetCoUser->id,
            ]
        );

        $widgetCoUser->update(['company_id' => $widgetCoCompany->id]);

        // Assign 'hr' role
        if (!$widgetCoUser->hasRole('hr')) {
            $widgetCoUser->assignRole('hr');
        }

        $this->command->info("Created WidgetCo Inc. with HR: {$widgetCoUser->email}");

        $this->command->info('Company seeding complete!');
        $this->command->info('Login credentials:');
        $this->command->info('Platform Admin: admin@platform.com / password → /admin/dashboard');
        $this->command->info('Acme HR: admin@acme.com / password → /hr/dashboard');
        $this->command->info('WidgetCo HR: admin@widgetco.com / password → /hr/dashboard');
    }
}
