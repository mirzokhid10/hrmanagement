<?php

namespace Database\Seeders;

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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 1. Define all Permissions ---
        $permissions = [
            // User & Role Management (within a company)
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles', // Assign/revoke roles to users (e.g., admin can assign hr_manager, manager, employee roles)
            'view audit logs', // Admin specific

            // Employee Management
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee sensitive data', // E.g., salaries, bank details
            'edit employee sensitive data',
            'manage employee profiles', // General profile updates
            'view employee attendance', // For /attendance bot feature

            // Self-Service Employee (These are usually granted by default to all authenticated users or via 'employee' role)
            'view own profile',
            'edit own profile',
            'request time-off',
            'view own time-offs',
            'view own documents',
            'upload own documents',
            'view own goals',
            'update own goals',
            'participate in performance reviews',

            // Department & Position Management
            'manage departments',
            'manage positions',

            // Time-Off Management
            'view all time-off requests',
            'approve time-off requests',
            'reject time-off requests',
            'manage time-off types',
            'view time-off balances',

            // Document Management
            'view all documents',
            'upload documents',
            'manage document templates',
            'require document signatures',
            'sign documents', // For HR/Managers to sign documents

            // Recruitment (HH.ru integration)
            'view vacancies',
            'create vacancies',
            'edit vacancies',
            'delete vacancies',
            'post vacancies to hh.ru', // Specific HH.ru API action
            'view candidates',
            'manage candidates', // Shortlist, reject, move stages
            'screen candidates',
            'schedule interviews',
            'send offer letters',
            'reject candidates',

            // Onboarding & Offboarding
            'manage onboarding processes',
            'manage offboarding processes',

            // Announcements
            'view all announcements',
            'create announcements',
            'edit announcements',
            'delete announcements',

            // Goals & Performance
            'view all goals',
            'create goals',
            'edit goals',
            'delete goals',
            'view all performance reviews',
            'create performance reviews',
            'edit performance reviews',
            'conduct performance reviews',
            'view performance review results',

            // 1-on-1 Meetings
            'schedule 1-on-1s',
            'view all 1-on-1s',
            'manage 1-on-1s',

            // Payroll Management (Viewing only for HR/Admin, actual processing might be a separate role later)
            'view payroll data',

            // Reporting & Analytics
            'view all reports',
            'access hr analytics',

            // Emergency Contacts
            'view emergency contacts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Ensure cache is cleared after creating permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 2. Create Roles ---
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $hrManagerRole = Role::firstOrCreate(['name' => 'hr_manager']);
        // You'll likely add 'manager' and 'employee' roles later as per your roadmap

        // --- 3. Assign Permissions to Roles ---

        // Admin: Company-level full control
        $adminPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles', // Ability to assign other roles
            'view audit logs',
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee sensitive data',
            'edit employee sensitive data',
            'manage employee profiles',
            'view employee attendance',
            'manage departments',
            'manage positions',
            'view all time-off requests',
            'approve time-off requests',
            'reject time-off requests',
            'manage time-off types',
            'view time-off balances',
            'view all documents',
            'upload documents',
            'manage document templates',
            'require document signatures',
            'sign documents',
            'view vacancies',
            'create vacancies',
            'edit vacancies',
            'delete vacancies',
            'post vacancies to hh.ru',
            'view candidates',
            'manage candidates',
            'screen candidates',
            'schedule interviews',
            'send offer letters',
            'reject candidates',
            'manage onboarding processes',
            'manage offboarding processes',
            'view all announcements',
            'create announcements',
            'edit announcements',
            'delete announcements',
            'view all goals',
            'create goals',
            'edit goals',
            'delete goals',
            'view all performance reviews',
            'create performance reviews',
            'edit performance reviews',
            'conduct performance reviews',
            'view performance review results',
            'schedule 1-on-1s',
            'view all 1-on-1s',
            'manage 1-on-1s',
            'view payroll data',
            'view all reports',
            'access hr analytics',
            'view emergency contacts',
            'view own profile',
            'edit own profile', // Admins are also employees
            'request time-off',
            'view own time-offs',
            'view own documents',
            'upload own documents',
            'view own goals',
            'update own goals',
            'participate in performance reviews',
        ];
        $adminRole->givePermissionTo($adminPermissions);


        $hrManagerPermissions = [
            'view users',
            'create users',
            'edit users', // Can manage basic user accounts, but not assign roles
            'view employees',
            'create employees',
            'edit employees',
            'delete employees',
            'view employee sensitive data',
            'edit employee sensitive data',
            'manage employee profiles',
            'view employee attendance',
            'manage departments',
            'manage positions',
            'view all time-off requests',
            'approve time-off requests',
            'reject time-off requests',
            'manage time-off types',
            'view time-off balances',
            'view all documents',
            'upload documents',
            'manage document templates',
            'require document signatures',
            'sign documents',
            'view vacancies',
            'create vacancies',
            'edit vacancies',
            'delete vacancies',
            'post vacancies to hh.ru',
            'view candidates',
            'manage candidates',
            'screen candidates',
            'schedule interviews',
            'send offer letters',
            'reject candidates',
            'manage onboarding processes',
            'manage offboarding processes',
            'view all announcements',
            'create announcements',
            'edit announcements',
            'delete announcements',
            'view all goals',
            'create goals',
            'edit goals',
            'delete goals',
            'view all performance reviews',
            'create performance reviews',
            'edit performance reviews',
            'conduct performance reviews',
            'view performance review results',
            'schedule 1-on-1s',
            'view all 1-on-1s',
            'manage 1-on-1s',
            'view payroll data',
            'view all reports',
            'access hr analytics',
            'view emergency contacts',
            'view own profile',
            'edit own profile', // HR Managers are also employees
            'request time-off',
            'view own time-offs',
            'view own documents',
            'upload own documents',
            'view own goals',
            'update own goals',
            'participate in performance reviews',
        ];
        $hrManagerRole->givePermissionTo($hrManagerPermissions);


        // --- 4. Assign a default role to the first user for initial testing ---
        // This assumes the first user created will be your initial company 'admin'.
        // In a real multi-tenant setup, this would happen during the company creation/onboarding flow.
        $user = \App\Models\User::first();
        if ($user && !$user->hasAnyRole(Role::all())) {
            $user->assignRole('admin');
            $this->command->info("Assigned 'admin' role to the first user: {$user->email}");
        } else if ($user) {
            $this->command->info("First user already has roles. No changes made.");
        } else {
            $this->command->warn("No users found. Please create a user first (e.g., register via web).");
        }
    }
}
