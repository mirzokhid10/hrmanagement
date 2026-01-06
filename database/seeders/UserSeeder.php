<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch existing companies
        $platformCompany = Company::where('slug', 'platform-management')->first();
        $acmeCompany = Company::where('subdomain', 'acme')->first();
        $widgetCoCompany = Company::where('subdomain', 'widgetco')->first();

        // Create users for these companies, if they don't already exist
        if ($platformCompany) {
            User::firstOrCreate(
                ['email' => 'admin@gmail.com'], // Or a unique email for this user
                [
                    'company_id' => $platformCompany->id,
                    'name' => 'General Admin',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($acmeCompany) {
            User::firstOrCreate(
                ['email' => 'hr@gmail.com'],
                [
                    'company_id' => $acmeCompany->id,
                    'name' => 'Acme HR',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($widgetCoCompany) {
            User::firstOrCreate(
                ['email' => 'hrmanager@gmail.com'],
                [
                    'company_id' => $widgetCoCompany->id,
                    'name' => 'WidgetCo HR Manager',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('Additional users seeded successfully.');
    }
}
