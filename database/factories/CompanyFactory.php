<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyName = $this->faker->unique()->company();
        $slug = Str::slug($companyName);

        // This creates a User - only use this factory when you WANT to create a user
        $user = User::factory()->create();

        return [
            'name' => $companyName,
            'slug' => $slug,
            'subdomain' => $this->faker->unique()->word(),
            'user_id' => $user->id,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Company $company) {
            // Update the user's company_id after company creation
            $company->user->update(['company_id' => $company->id]);
        });
    }

    /**
     * Indicate that the company should not have a subdomain.
     */
    public function withNullSubdomain(): static
    {
        return $this->state(fn(array $attributes) => [
            'subdomain' => null,
        ]);
    }

    /**
     * Indicate that the company should have a specific subdomain.
     */
    public function withSubdomain(string $subdomain): static
    {
        return $this->state(fn(array $attributes) => [
            'subdomain' => $subdomain,
        ]);
    }

    /**
     * Associate company with an existing user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
