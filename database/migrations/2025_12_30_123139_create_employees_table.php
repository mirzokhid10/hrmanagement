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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // Link to the tenant company
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('image')->nullable();
            $table->string('email')->unique(); // Unique within a company, handled in model/request
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('hire_date');
            $table->string('job_title');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null'); // Assuming you have a departments table
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('status')->default('active'); // e.g., active, inactive, on_leave
            $table->foreignId('reports_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            // Add an index for faster lookups on company_id and email
            $table->index(['company_id', 'email']);
            $table->index(['company_id', 'department_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
