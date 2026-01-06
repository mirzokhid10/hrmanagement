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
        Schema::create('time_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); // Tenant scope
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete(); // Who is requesting
            $table->foreignId('time_off_type_id')->constrained('time_off_types')->cascadeOnDelete(); // Type of leave
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 4, 1); // e.g., 0.5, 1.0, 5.0
            $table->text('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Cancelled'])->default('Pending');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete(); // Who approved/rejected
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Add index for faster queries
            $table->index(['company_id', 'employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_offs');
    }
};
