<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch companies
        $platformCompany = Company::where('slug', 'platform-management')->first();
        $acmeCompany = Company::where('subdomain', 'acme')->first();
        $widgetCoCompany = Company::where('subdomain', 'widgetco')->first();

        if (!$platformCompany || !$acmeCompany || !$widgetCoCompany) {
            $this->command->error('One or more required companies not found. Please run CompanySeeder first.');
            return;
        }

        // Standard departments for consistency
        $standardDepartments = [
            'Human Resources',
            'Finance',
            'Marketing',
            'Sales',
            'Engineering',
            'Customer Support',
            'Operations',
            'Product Management',
            'Research & Development',
            'Legal'
        ];

        // --- 1. Platform Management Company Departments ---
        $platformDepartments = ['Platform Operations', 'Global Support'];
        foreach ($platformDepartments as $name) {
            Department::firstOrCreate(
                ['company_id' => $platformCompany->id, 'name' => $name],
                ['description' => "Department for {$name}"]
            );
        }
        $this->command->info("Created departments for Platform Management");

        // --- 2. Acme Corporation Departments ---
        $acmeDepartments = array_slice($standardDepartments, 0, 7);
        foreach ($acmeDepartments as $name) {
            Department::firstOrCreate(
                ['company_id' => $acmeCompany->id, 'name' => $name],
                ['description' => "Acme {$name} Department"]
            );
        }
        $this->command->info("Created departments for Acme Corporation");

        // --- 3. WidgetCo Inc. Departments ---
        $widgetCoDepartments = array_slice($standardDepartments, 0, 5);
        foreach ($widgetCoDepartments as $name) {
            Department::firstOrCreate(
                ['company_id' => $widgetCoCompany->id, 'name' => $name],
                ['description' => "WidgetCo {$name} Department"]
            );
        }
        $this->command->info("Created departments for WidgetCo Inc.");

        $this->command->info('Department seeding complete!');
    }
}
