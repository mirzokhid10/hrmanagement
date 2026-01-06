<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => null, // Default to null - most employees don't have user accounts
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'image' => $this->faker->optional(0.7)->imageUrl(640, 480, 'people', true),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->optional(0.7)->phoneNumber(),
            'address' => $this->faker->optional(0.8)->address(),
            'date_of_birth' => $this->faker->boolean(90)
                ? $this->faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d')
                : null,
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'job_title' => $this->faker->jobTitle(),
            'department_id' => Department::factory(),
            'salary' => $this->faker->numberBetween(30000, 120000),
            'status' => $this->faker->randomElement(['active', 'inactive', 'on_leave']),
            'reports_to' => null,
        ];
    }

    /**
     * Indicate that the employee is for a specific company and picks an existing department.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(function (array $attributes) use ($company) {
            $departmentId = $company->departments->random()?->id
                ?? Department::factory()->forCompany($company)->create()->id;

            return [
                'company_id' => $company->id,
                'department_id' => $departmentId,
            ];
        });
    }

    /**
     * Indicate that the employee is associated with a specific user.
     * This should only be used for the ONE company owner/HR manager.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'email' => $user->email,
        ]);
    }

    /**
     * Indicate that the employee reports to a specific manager.
     */
    public function reportsTo(Employee $manager): static
    {
        return $this->state(fn(array $attributes) => [
            'reports_to' => $manager->id,
            'company_id' => $manager->company_id,
        ]);
    }

    /**
     * Indicate an employee who is a manager (e.g., has direct reports).
     * Note: This is just for the employee record, NOT a user account.
     */
    public function manager(): static
    {
        return $this->state(fn(array $attributes) => [
            'job_title' => $this->faker->randomElement(['Manager', 'Team Lead', 'Supervisor', 'Director']),
            'salary' => $this->faker->numberBetween(70000, 150000),
        ]);
    }

    /**
     * Indicate that the employee is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
            'hire_date' => $this->faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
        ]);
    }
}
