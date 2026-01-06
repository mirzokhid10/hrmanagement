<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\TimeOffType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeOffType>
 */
class TimeOffTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TimeOffType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(), // Automatically create and link a company
            'name' => $this->faker->unique()->randomElement(['Vacation', 'Sick Leave', 'Personal Leave', 'Bereavement', 'Maternity', 'Paternity', 'Jury Duty']) . ' ' . $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_paid' => $this->faker->boolean(90), // 90% chance of being paid
            'default_days_per_year' => $this->faker->numberBetween(5, 25),
        ];
    }

    /**
     * Indicate that the time off type is for a specific company.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(fn(array $attributes) => [
            'company_id' => $company->id,
        ]);
    }

    /**
     * Indicate a common, generic vacation type.
     */
    public function vacation(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Vacation',
            'description' => 'Paid time off for personal leisure and rest.',
            'is_paid' => true,
            'default_days_per_year' => 15,
        ]);
    }

    /**
     * Indicate a common, generic sick leave type.
     */
    public function sickLeave(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Sick Leave',
            'description' => 'Paid time off for illness or medical appointments.',
            'is_paid' => true,
            'default_days_per_year' => 10,
        ]);
    }
}
