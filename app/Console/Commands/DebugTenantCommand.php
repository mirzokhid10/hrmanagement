<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\TimeOffType;
use Illuminate\Console\Command;

class DebugTenantCommand extends Command
{
    protected $signature = 'tenant:debug {company_id?}';
    protected $description = 'Debug tenant data to see what exists for each company';

    public function handle()
    {
        $companyId = $this->argument('company_id');

        if ($companyId) {
            $this->debugSingleCompany($companyId);
        } else {
            $this->debugAllCompanies();
        }

        return Command::SUCCESS;
    }

    private function debugAllCompanies()
    {
        $this->info('=== All Companies ===');
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->line('');
            $this->info("Company ID: {$company->id} - {$company->name} (subdomain: {$company->subdomain})");

            // Count related data
            $departmentCount = Department::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('company_id', $company->id)
                ->count();

            $employeeCount = Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('company_id', $company->id)
                ->count();

            $typeCount = TimeOffType::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('company_id', $company->id)
                ->count();

            $this->line("  - Departments: {$departmentCount}");
            $this->line("  - Employees: {$employeeCount}");
            $this->line("  - Time-off Types: {$typeCount}");
        }
    }

    private function debugSingleCompany($companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            $this->error("Company with ID {$companyId} not found!");
            return;
        }

        $this->info("=== Company: {$company->name} (ID: {$company->id}) ===");
        $this->line('');

        // Departments
        $this->info('Departments:');
        $departments = Department::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('company_id', $companyId)
            ->get();

        if ($departments->isEmpty()) {
            $this->warn('  No departments found!');
        } else {
            foreach ($departments as $dept) {
                $this->line("  - {$dept->id}: {$dept->name}");
            }
        }
        $this->line('');

        // Employees
        $this->info('Employees:');
        $employees = Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('company_id', $companyId)
            ->get();

        if ($employees->isEmpty()) {
            $this->warn('  No employees found!');
        } else {
            foreach ($employees as $emp) {
                $this->line("  - {$emp->id}: {$emp->first_name} {$emp->last_name} (Dept: {$emp->department_id})");
            }
        }
        $this->line('');

        // Time-off Types
        $this->info('Time-off Types:');
        $types = TimeOffType::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('company_id', $companyId)
            ->get();

        if ($types->isEmpty()) {
            $this->warn('  No time-off types found!');
        } else {
            foreach ($types as $type) {
                $this->line("  - {$type->id}: {$type->name} (Paid: " . ($type->is_paid ? 'Yes' : 'No') . ")");
            }
        }
    }
}
