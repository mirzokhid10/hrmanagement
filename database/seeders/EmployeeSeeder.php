<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Faker\Factory as FakerFactory;

class EmployeeSeeder extends Seeder
{

    public function run(): void
    {

        // Ensure roles exist
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);

        // Fetch companies
        $platformCompany = Company::where('slug', 'platform-management')->first();
        $acmeCompany = Company::where('subdomain', 'acme')->first();
        $widgetCoCompany = Company::where('subdomain', 'widgetco')->first();

        if (!$platformCompany || !$acmeCompany || !$widgetCoCompany) {
            $this->command->error('Companies not found. Please run CompanySeeder first.');
            return;
        }

        // Fetch departments
        $platformDepartments = Department::where('company_id', $platformCompany->id)->get();
        $acmeDepartments = Department::where('company_id', $acmeCompany->id)->get();
        $widgetCoDepartments = Department::where('company_id', $widgetCoCompany->id)->get();

        if ($platformDepartments->isEmpty() || $acmeDepartments->isEmpty() || $widgetCoDepartments->isEmpty()) {
            $this->command->error('No departments found. Please run DepartmentSeeder first.');
            return;
        }

        // --- ACME CORPORATION ---
        $this->command->info('Creating employees for Acme Corporation...');

        $acmeUser = User::where('email', 'admin@acme.com')->first();
        $acmeHrEmployee = null; // INITIALIZE TO NULL

        if ($acmeUser) {
            $acmeUser->assignRole('hr');

            $acmeHrEmployee = Employee::firstOrCreate(
                ['user_id' => $acmeUser->id, 'company_id' => $acmeCompany->id],
                [
                    'first_name' => 'Sarah',
                    'last_name' => 'Johnson',
                    'email' => $acmeUser->email,
                    'job_title' => 'HR Manager',
                    'department_id' => $acmeDepartments->where('name', 'Human Resources')->first()?->id ?? $acmeDepartments->first()->id,
                    'salary' => 95000,
                    'status' => 'active', // Use lowercase 'active'
                    'hire_date' => now()->subYears(2)->format('Y-m-d'),
                ]
            );
            $this->command->info("Created Acme HR Manager employee record");
        }

        // Create managers
        $acmeManagers = collect([]);
        if ($acmeHrEmployee) {
            $acmeManagers->push($acmeHrEmployee);
        }

        $managerDepartments = ['Engineering', 'Sales', 'Marketing', 'Finance'];
        foreach ($managerDepartments as $deptName) {
            $department = $acmeDepartments->where('name', $deptName)->first();
            if ($department) {
                $manager = Employee::create([
                    'company_id' => $acmeCompany->id,
                    'user_id' => null,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone_number' => fake()->phoneNumber(),
                    'job_title' => $deptName . ' Manager',
                    'department_id' => $department->id,
                    'salary' => fake()->numberBetween(80000, 120000),
                    'status' => 'active', // Use lowercase
                    'hire_date' => fake()->dateTimeBetween('-3 years', '-1 year')->format('Y-m-d'),
                    'reports_to' => $acmeHrEmployee?->id, // SAFE ACCESS
                ]);
                $acmeManagers->push($manager);
            }
        }

        // Only create regular employees if we have managers
        if ($acmeManagers->isNotEmpty()) {
            for ($i = 0; $i < 20; $i++) {
                Employee::create([
                    'company_id' => $acmeCompany->id,
                    'user_id' => null,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone_number' => fake()->optional(0.8)->phoneNumber(),
                    'address' => fake()->optional(0.7)->address(),
                    'date_of_birth' => fake()->optional(0.9)->dateTimeBetween('-50 years', '-22 years')?->format('Y-m-d'),
                    'job_title' => fake()->jobTitle(),
                    'department_id' => $acmeDepartments->random()->id,
                    'salary' => fake()->numberBetween(35000, 75000),
                    'status' => 'active', // Use lowercase consistently
                    'hire_date' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
                    'reports_to' => $acmeManagers->random()->id,
                ]);
            }
        }

        // --- Widget Corporation ---
        $this->command->info('Creating employees for Widget Corporation...');

        $widgetUser = User::where('email', 'admin@widgetco.com')->first();
        $widgetHrEmployee = null; // INITIALIZE TO NULL

        if ($widgetUser) {
            $widgetUser->assignRole('hr');

            $widgetHrEmployee = Employee::firstOrCreate(
                ['user_id' => $widgetUser->id, 'company_id' => $widgetCoCompany->id],
                [
                    'first_name' => 'John',
                    'last_name' => 'Miller',
                    'email' => $widgetUser->email,
                    'job_title' => 'HR Manager',
                    'department_id' => $widgetCoDepartments->where('name', 'Human Resources')->first()?->id ?? $widgetCoDepartments->first()->id,
                    'salary' => 95000,
                    'status' => 'active', // Use lowercase 'active'
                    'hire_date' => now()->subYears(2)->format('Y-m-d'),
                ]
            );
            $this->command->info("Created Acme HR Manager employee record");
        }

        $widgetManagers = collect([]);
        if ($widgetHrEmployee) {
            $widgetManagers->push($widgetHrEmployee);
        }

        $managerDepartments = ['Engineering', 'Sales', 'Marketing', 'Finance'];
        foreach ($managerDepartments as $deptName) {
            $department = $widgetCoDepartments->where('name', $deptName)->first();
            if ($department) {
                $manager = Employee::create([
                    'company_id' => $widgetCoCompany->id,
                    'user_id' => null,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone_number' => fake()->phoneNumber(),
                    'job_title' => $deptName . ' Manager',
                    'department_id' => $department->id,
                    'salary' => fake()->numberBetween(80000, 120000),
                    'status' => 'active', // Use lowercase
                    'hire_date' => fake()->dateTimeBetween('-3 years', '-1 year')->format('Y-m-d'),
                    'reports_to' => $widgetHrEmployee?->id, // SAFE ACCESS
                ]);
                $widgetManagers->push($manager);
            }
        }

        // Only create regular employees if we have managers
        if ($widgetManagers->isNotEmpty()) {
            for ($i = 0; $i < 20; $i++) {
                Employee::create([
                    'company_id' => $widgetCoCompany->id,
                    'user_id' => null,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone_number' => fake()->optional(0.8)->phoneNumber(),
                    'address' => fake()->optional(0.7)->address(),
                    'date_of_birth' => fake()->optional(0.9)->dateTimeBetween('-50 years', '-22 years')?->format('Y-m-d'),
                    'job_title' => fake()->jobTitle(),
                    'department_id' => $widgetCoDepartments->random()->id,
                    'salary' => fake()->numberBetween(35000, 75000),
                    'status' => 'active', // Use lowercase consistently
                    'hire_date' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
                    'reports_to' => $widgetManagers->random()->id,
                ]);
            }
        }

        $this->command->info('Employee seeding complete!');
    }
}
