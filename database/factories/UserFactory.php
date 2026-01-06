<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            // Make company_id lazy. It will be resolved by the factory system or 'forCompany' state.
            'company_id' => Company::factory(), // <-- REMOVED ->create()->id
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn(array $attributes) => [
            'company_id' => $company->id,
        ]);
    }
}
