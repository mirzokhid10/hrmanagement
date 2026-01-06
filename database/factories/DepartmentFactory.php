<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $departmentNames = [
            'Human Resources',
            'Finance',
            'Engineering',
            'Marketing',
            'Sales',
            'Operations',
            'Customer Support',
            'Product Management',
            'Legal',
            'Administration'
        ];

        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->randomElement($departmentNames),
            'description' => $this->faker->sentence(),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn(array $attributes) => [
            'company_id' => $company->id,
        ]);
    }
}
