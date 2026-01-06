<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeOff;
use App\Models\TimeOffBalance;
use App\Models\TimeOffType;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

class TimeOffSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch existing companies (from CompanySeeder)
        $acmeCompany = Company::where('subdomain', 'acme')->first();
        $widgetCoCompany = Company::where('subdomain', 'widgetco')->first();

        if (!$acmeCompany || !$widgetCoCompany) {
            $this->command->error('Acme or WidgetCo company not found. Please run CompanySeeder first.');
            return;
        }

        // Fetch some employees for these companies
        $acmeEmployees = Employee::where('company_id', $acmeCompany->id)->get();
        $widgetCoEmployees = Employee::where('company_id', $widgetCoCompany->id)->get();

        if ($acmeEmployees->isEmpty() || $widgetCoEmployees->isEmpty()) {
            $this->command->error('No employees found for Acme or WidgetCo. Please ensure employees are seeded.');
            return;
        }

        // --- Create Time Off Types for each company ---
        $this->seedTimeOffTypesForCompany($acmeCompany);
        $this->seedTimeOffTypesForCompany($widgetCoCompany);

        // Fetch the common types created using direct queries
        $vacationTypeAcme = TimeOffType::where('company_id', $acmeCompany->id)
            ->where('name', 'Vacation') // <-- FIX: Query by name
            ->first();
        $sickLeaveTypeAcme = TimeOffType::where('company_id', $acmeCompany->id)
            ->where('name', 'Sick Leave') // <-- FIX: Query by name
            ->first();
        $personalLeaveTypeAcme = TimeOffType::where('company_id', $acmeCompany->id)
            ->where('name', 'Personal Leave') // Add this for consistency
            ->first();

        $vacationTypeWidgetCo = TimeOffType::where('company_id', $widgetCoCompany->id)
            ->where('name', 'Vacation') // <-- FIX: Query by name
            ->first();
        $sickLeaveTypeWidgetCo = TimeOffType::where('company_id', $widgetCoCompany->id)
            ->where('name', 'Sick Leave') // <-- FIX: Query by name
            ->first();
        $personalLeaveTypeWidgetCo = TimeOffType::where('company_id', $widgetCoCompany->id)
            ->where('name', 'Personal Leave') // Add this for consistency
            ->first();


        // --- Create Time Off Balances ---
        $currentYear = date('Y');
        foreach ($acmeEmployees as $employee) {

            if ($vacationTypeAcme) {
                TimeOffBalance::factory()->forEmployeeAndType($employee, $vacationTypeAcme, $currentYear)->create([
                    'allocated_days' => $vacationTypeAcme->default_days_per_year,
                    'days_taken' => $this->faker->numberBetween(0, $vacationTypeAcme->default_days_per_year / 2),
                ]);
            }
            if ($sickLeaveTypeAcme) {
                TimeOffBalance::factory()->forEmployeeAndType($employee, $sickLeaveTypeAcme, $currentYear)->create([
                    'allocated_days' => $sickLeaveTypeAcme->default_days_per_year,
                    'days_taken' => $this->faker->numberBetween(0, $sickLeaveTypeAcme->default_days_per_year / 2),
                ]);
            }
            if ($personalLeaveTypeAcme) { // Also seed personal leave balances
                TimeOffBalance::factory()->forEmployeeAndType($employee, $personalLeaveTypeAcme, $currentYear)->create([
                    'allocated_days' => $personalLeaveTypeAcme->default_days_per_year,
                    'days_taken' => $this->faker->numberBetween(0, $personalLeaveTypeAcme->default_days_per_year / 2),
                ]);
            }
        }
        foreach ($widgetCoEmployees as $employee) {
            if ($vacationTypeWidgetCo) {
                TimeOffBalance::factory()->forEmployeeAndType($employee, $vacationTypeWidgetCo, $currentYear)->create([
                    'allocated_days' => $vacationTypeWidgetCo->default_days_per_year,
                    'days_taken' => $this->faker->numberBetween(0, $vacationTypeWidgetCo->default_days_per_year / 2),
                ]);
            }
            if ($sickLeaveTypeWidgetCo) {
                TimeOffBalance::factory()->forEmployeeAndType($employee, $sickLeaveTypeWidgetCo, $currentYear)->create([
                    'allocated_days' => $sickLeaveTypeWidgetCo->default_days_per_year,
                    'days_taken' => $this->faker->numberBetween(0, $sickLeaveTypeWidgetCo->default_days_per_year / 2),
                ]);
            }
            if ($personalLeaveTypeWidgetCo) { // Also seed personal leave balances
                TimeOffBalance::factory()->forEmployeeAndType($employee, $personalLeaveTypeWidgetCo, $currentYear)->create([
                    'allocated_days' => $personalLeaveTypeWidgetCo->default_days_per_year,
                    'days_taken' => $this->faker->numberBetween(0, $personalLeaveTypeWidgetCo->default_days_per_year / 2),
                ]);
            }
        }


        // --- Create Time Off Requests ---
        // For Acme employees
        foreach ($acmeEmployees as $employee) {

            $numRequests = $this->faker->numberBetween(2, 5);
            for ($i = 0; $i < $numRequests; $i++) {
                // Ensure the random element picks from available types
                $availableTypes = array_filter([$vacationTypeAcme, $sickLeaveTypeAcme, $personalLeaveTypeAcme]);
                $type = $this->faker->randomElement($availableTypes);
                if ($type) {
                    TimeOff::factory()->forEmployee($employee)->ofType($type)->create([
                        'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
                    ]);
                }
            }
        }

        // For WidgetCo employees
        foreach ($widgetCoEmployees as $employee) {
            $numRequests = $this->faker->numberBetween(2, 5);
            for ($i = 0; $i < $numRequests; $i++) {
                // Ensure the random element picks from available types
                $availableTypes = array_filter([$vacationTypeWidgetCo, $sickLeaveTypeWidgetCo, $personalLeaveTypeWidgetCo]);
                $type = $this->faker->randomElement($availableTypes);
                if ($type) {
                    TimeOff::factory()->forEmployee($employee)->ofType($type)->create([
                        'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
                    ]);
                }
            }
        }

        // Ensure at least one pending request for a specific employee for testing approval workflow
        $testEmployee = $acmeEmployees->first();
        if ($testEmployee && $vacationTypeAcme) {
            TimeOff::factory()->forEmployee($testEmployee)->ofType($vacationTypeAcme)->pending()->create([
                'start_date' => now()->addWeeks(2),
                'end_date' => now()->addWeeks(2)->addDays(4),
                'total_days' => 5,
                'reason' => 'Annual leave for personal trip.',
            ]);
        }
    }

    protected function seedTimeOffTypesForCompany(Company $company): void
    {
        // FirstOrCreate ensures these specific types are only created once per company
        TimeOffType::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Vacation'],
            TimeOffType::factory()->vacation()->forCompany($company)->raw()
        );
        TimeOffType::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Sick Leave'],
            TimeOffType::factory()->sickLeave()->forCompany($company)->raw()
        );
        TimeOffType::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Personal Leave'],
            TimeOffType::factory()->forCompany($company)->state(function (array $attributes) {
                return [
                    'name' => 'Personal Leave',
                    'description' => 'Unplanned time off for personal matters.',
                    'is_paid' => true,
                    'default_days_per_year' => 5,
                ];
            })->raw()
        );
        // Add more types as needed
    }
}
