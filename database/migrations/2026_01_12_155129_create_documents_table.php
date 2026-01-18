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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('name'); // Display name e.g., "Labor Contract 2025"
            $table->string('type'); // e.g., 'contract', 'id_card', 'policy', 'other'
            $table->string('file_path');
            $table->string('mime_type')->nullable(); // pdf, jpg
            $table->integer('size_kb')->nullable();
            $table->date('expiry_date')->nullable(); // For passports/visas

            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
