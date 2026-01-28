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
        Schema::create('employee_insights', function (Blueprint $table) {
            $table->id();
            // Tenant Scope
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // The Data
            $table->string('type')->default('turnover_risk'); // extensible for future
            $table->integer('score')->default(0); // 0 (Safe) - 100 (Leaving tomorrow)
            $table->string('risk_level'); // 'low', 'medium', 'high', 'critical'

            // The "Why" (JSON array of factors)
            $table->json('factors')->nullable();

            // The AI's opinion (Cached)
            $table->text('ai_analysis_uz')->nullable();
            $table->text('ai_analysis_ru')->nullable();

            $table->timestamps();

            // Ensure one insight per employee per type
            $table->unique(['employee_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_insights');
    }
};
