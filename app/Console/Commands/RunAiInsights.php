<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Services\Analytics\TurnoverRiskService;

class RunAiInsights extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:insights {--company= : ID of specific company}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate turnover risk for employees';

    /**
     * Execute the console command.
     */
    public function handle(TurnoverRiskService $service)
    {
        $companyId = $this->option('company');

        $query = Company::query();
        if ($companyId) {
            $query->where('id', $companyId);
        }

        $companies = $query->get();

        foreach ($companies as $company) {
            $this->info("Analyzing Company: {$company->name}");

            // Force tenant context for the service/models
            // (Assuming you have a way to set current tenant, or we manually handle it)
            // For this loop, we just grab employees by ID

            $employees = $company->employees()->where('status', 'active')->get();

            $bar = $this->output->createProgressBar(count($employees));

            foreach ($employees as $employee) {
                // Ensure the employee object has the correct company_id set for the TenantScoped trait
                if (!$employee->company_id) $employee->company_id = $company->id;

                $service->analyze($employee);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info('AI Analysis Complete!');
    }
}
