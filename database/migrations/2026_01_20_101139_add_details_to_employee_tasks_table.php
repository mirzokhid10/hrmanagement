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
        Schema::table('employee_tasks', function (Blueprint $table) {
            $table->string('status')->default('new')->after('requires_upload'); // new, in_progress, pending, completed
            $table->string('priority')->default('medium')->after('status'); // low, medium, high
            $table->string('title')->nullable()->after('employee_id'); // Short title
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_tasks', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority', 'title', 'start_date', 'due_date', 'description']);
        });
    }
};
