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
        Schema::create('bot_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            // Attendance settings
            $table->boolean('attendance_enabled')->default(true);
            $table->boolean('location_verification_required')->default(true);
            $table->boolean('wifi_verification_required')->default(true);
            $table->time('check_in_start_time')->default('08:00:00');
            $table->time('check_in_end_time')->default('10:00:00');
            $table->time('check_out_start_time')->default('17:00:00');
            $table->time('check_out_end_time')->default('20:00:00');
            $table->integer('late_threshold_minutes')->default(15);

            // Notification settings
            $table->boolean('send_daily_reminders')->default(true);
            $table->time('reminder_time')->default('08:30:00');
            $table->boolean('notify_managers_on_late')->default(true);
            $table->boolean('notify_managers_on_absent')->default(true);

            // Bot behavior
            $table->string('welcome_message')->nullable();
            $table->string('timezone')->default('Asia/Tashkent');
            $table->string('language')->default('en'); // en, ru, uz

            $table->timestamps();

            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_settings');
    }
};
