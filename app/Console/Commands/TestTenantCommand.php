<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Console\Command;

class TestTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Tenant Functionality...');
        $this->newLine();

        // Test 1: List all companies
        $this->info('📋 All Companies:');
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->warn('No companies found. Creating test companies...');
            $this->createTestCompanies();
            $companies = Company::all();
        }

        foreach ($companies as $company) {
            $this->line("  - {$company->name} (subdomain: {$company->subdomain})");
        }
        $this->newLine();

        // Test 2: Test helper functions without tenant
        $this->info('🧪 Testing helpers without tenant context:');
        $this->line('  tenant() = ' . (tenant() ? tenant()->name : 'null'));
        $this->line('  tenant_id() = ' . (tenant_id() ?? 'null'));
        $this->line('  tenant_name() = ' . (tenant_name() ?? 'null'));
        $this->newLine();

        // Test 3: Make first company current
        $firstCompany = $companies->first();
        $this->info("🔄 Making '{$firstCompany->name}' current tenant...");
        $firstCompany->makeCurrent();

        $this->info('✅ Testing helpers with tenant context:');
        $this->line('  tenant() = ' . (tenant() ? tenant()->name : 'null'));
        $this->line('  tenant_id() = ' . (tenant_id() ?? 'null'));
        $this->line('  tenant_name() = ' . (tenant_name() ?? 'null'));
        $this->line('  tenant_subdomain() = ' . (tenant_subdomain() ?? 'null'));
        $this->newLine();

        // Test 4: Test employee scoping
        $employeeCount = Employee::count();
        $this->info("📊 Employees in current tenant: {$employeeCount}");

        // Test 5: Switch to another company
        if ($companies->count() > 1) {
            $secondCompany = $companies->skip(1)->first();
            $this->info("🔄 Switching to '{$secondCompany->name}'...");
            $secondCompany->makeCurrent();

            $this->line('  tenant_name() = ' . tenant_name());
            $employeeCount2 = Employee::count();
            $this->line("  Employees in this tenant: {$employeeCount2}");
            $this->newLine();
        }

        // Test 6: Test TenantScope
        $this->info('🔍 Testing TenantScope isolation:');
        foreach ($companies->take(2) as $company) {
            $company->makeCurrent();
            $count = Employee::count();
            $this->line("  {$company->name}: {$count} employees");
        }
        $this->newLine();

        $this->info('✅ All tests completed!');

        return Command::SUCCESS;
    }

    /**
     * Create test companies for demonstration
     */
    private function createTestCompanies(): void
    {
        $this->info('Creating test companies...');

        Company::create([
            'name' => 'ACME Corporation',
            'slug' => 'acme',
            'subdomain' => 'acme',
            'is_active' => true,
        ]);

        Company::create([
            'name' => 'Bamboo Technologies',
            'slug' => 'bamboo',
            'subdomain' => 'bamboo',
            'is_active' => true,
        ]);

        $this->info('✅ Test companies created!');
        $this->newLine();
    }
}
