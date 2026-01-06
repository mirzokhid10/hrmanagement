<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeOff;
use App\Models\TimeOffType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeOff>
 */
class TimeOffFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeOff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', '+3 months');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 10) . ' days');
        $totalDays = $startDate->diff($endDate)->days + 1; // +1 to include start date

        return [
            'company_id' => Company::factory(), // Defaults to creating a new company
            'employee_id' => Employee::factory(), // Defaults to creating a new employee
            'time_off_type_id' => TimeOffType::factory(), // Defaults to creating a new time off type
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
            'approver_id' => null, // Will be set by states
            'approved_at' => null, // Will be set by states
            'rejection_reason' => null, // Will be set by states
        ];
    }

    /**
     * Indicate that the time off is for a specific employee and company.
     */
    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn(array $attributes) => [
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id, // Ensure consistent company_id
        ]);
    }

    /**
     * Indicate that the time off is of a specific type.
     */
    public function ofType(TimeOffType $type): static
    {
        return $this->state(fn(array $attributes) => [
            'time_off_type_id' => $type->id,
        ]);
    }

    /**
     * Indicate that the time off request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Pending',
            'approver_id' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the time off request is approved.
     */
    public function approved(User $approver = null): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Approved',
            'approver_id' => $approver?->id ?? User::factory()->create(['company_id' => $attributes['company_id']])->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the time off request is rejected.
     */
    public function rejected(User $approver = null, string $reason = null): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Rejected',
            'approver_id' => $approver?->id ?? User::factory()->create(['company_id' => $attributes['company_id']])->id,
            'approved_at' => null,
            'rejection_reason' => $reason ?? $this->faker->sentence(),
        ]);
    }
}
