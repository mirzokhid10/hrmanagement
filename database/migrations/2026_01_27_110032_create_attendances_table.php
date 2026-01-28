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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Core Data
            $table->date('date')->index(); // The specific day (2024-07-01)
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();

            // Status & Calculations
            $table->enum('status', ['present', 'late', 'absent', 'half_day', 'leave'])->default('absent');
            $table->integer('late_minutes')->default(0); // How many minutes late?
            $table->decimal('work_hours', 5, 2)->default(0); // Total hours worked (e.g., 8.50)

            // Verification Data (Anti-Fraud)
            $table->boolean('is_location_verified')->default(false);
            $table->boolean('is_wifi_verified')->default(false);
            $table->string('check_in_ip')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lon', 10, 7)->nullable();

            // Admin/Manual Override
            $table->boolean('is_regularized')->default(false); // If HR manually fixed it
            $table->text('regularization_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: One record per employee per day
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
