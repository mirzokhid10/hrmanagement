<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeOffBalance;
use App\Models\TimeOffType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeOffBalance>
 */
class TimeOffBalanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeOffBalance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allocated = $this->faker->numberBetween(10, 30);
        $taken = $this->faker->numberBetween(0, $allocated);

        return [
            'company_id' => Company::factory(),
            'employee_id' => Employee::factory(),
            'time_off_type_id' => TimeOffType::factory(),
            'year' => $this->faker->numberBetween(date('Y') - 1, date('Y') + 1), // Current, past, or future year
            'allocated_days' => $allocated,
            'days_taken' => $taken,
        ];
    }

    /**
     * Indicate that the balance is for a specific employee, type, and company.
     */
    public function forEmployeeAndType(Employee $employee, TimeOffType $type, int $year = null): static
    {
        return $this->state(fn(array $attributes) => [
            'employee_id' => $employee->id,
            'time_off_type_id' => $type->id,
            'company_id' => $employee->company_id,
            'year' => $year ?? date('Y'),
        ]);
    }
}
