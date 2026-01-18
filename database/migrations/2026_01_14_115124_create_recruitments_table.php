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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            // Core Job Details
            $table->string('title');                // e.g. "Laravel Developer"

            $table->foreignId('department_id')->constrained()->onDelete('cascade');           // e.g. "IT Department"
            $table->string('job_type');             // e.g. "Full-time", "Contract"
            $table->string('salary_range')->nullable(); // e.g. "10M - 15M UZS"

            // Specific Requirements
            $table->string('experience');           // e.g. "1-3 years", "3-6 years"
            $table->string('schedule');             // e.g. "5/2", "6/1"
            $table->string('working_hours');        // e.g. "9:00 - 18:00" or "8 hours"
            $table->string('location');             // e.g. "Tashkent, Chilonzor"

            $table->date('deadline')->nullable();
            $table->longText('description');        // Using longText for rich HTML descriptions
            $table->json('key_skills')->nullable(); // Stores ["PHP", "MySQL", "Git"]

            // Integration & Status
            $table->string('hh_vacancy_id')->nullable(); // ID from HH.ru
            $table->string('hh_url')->nullable();        // Public link on HH.ru
            $table->enum('status', ['published', 'draft', 'closed'])->default('published');

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
