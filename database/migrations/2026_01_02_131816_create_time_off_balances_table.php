<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_off_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); // Tenant scope
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('time_off_type_id')->constrained('time_off_types')->cascadeOnDelete();
            $table->integer('year'); // e.g., 2024
            $table->decimal('allocated_days', 4, 1)->default(0); // Total days allocated for this type in this year
            $table->decimal('days_taken', 4, 1)->default(0); // Days already taken
            $table->timestamps();

            // Each employee can only have one balance entry per time off type per year
            $table->unique(['company_id', 'employee_id', 'time_off_type_id', 'year'], 'employee_time_off_balance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_off_balances');
    }
};
