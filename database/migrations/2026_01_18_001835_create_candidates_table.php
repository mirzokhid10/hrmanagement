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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            // ✅ TENANCY: Link to Company
            // Even though it links to recruitment, having company_id here makes
            // "Get all candidates for this company" queries much faster/safer.
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            // Link to the Job Vacancy (Recruitment)
            // 'constrained' automatically looks for 'recruitments' table based on the model name usually,
            // but we specify the table name to be safe.
            $table->foreignId('recruitment_id')
                ->constrained('recruitments')
                ->onDelete('cascade');

            // Personal Info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone'); // Essential for Telegram Bot later

            // Files & Assets
            $table->string('photo_path')->nullable(); // Avatar
            $table->string('resume_path')->nullable(); // PDF/Doc path
            $table->text('cover_letter')->nullable();

            // Workflow Status
            // Matches the badges in your Blade template
            $table->enum('status', [
                'pending',
                'shortlisted',
                'interviewed',
                'hired',
                'rejected'
            ])->default('pending');

            // Interview Logic
            $table->dateTime('interview_scheduled_at')->nullable();

            // HH.ru Integration
            $table->string('hh_candidate_id')->nullable(); // ID of the applicant on HH.ru
            $table->string('hh_resume_id')->nullable();    // ID of their specific resume

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
