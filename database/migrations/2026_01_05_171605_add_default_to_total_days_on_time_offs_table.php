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
        Schema::table('time_offs', function (Blueprint $table) {
            // Change column to allow default value, or make it nullable if that's preferred
            $table->decimal('total_days', 4, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_offs', function (Blueprint $table) {
            // Revert by removing the default (if you want to undo)
            $table->decimal('total_days', 4, 1)->change(); // This removes the default
        });
    }
};
