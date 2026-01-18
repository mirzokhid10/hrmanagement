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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->unique()->after('user_id');
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            $table->timestamp('last_interaction_at')->nullable()->after('status');

            $table->index(['company_id', 'telegram_chat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_company_id_telegram_chat_id_index');
            $table->dropColumn('telegram_chat_id');
            $table->dropColumn('telegram_username');
            $table->dropColumn('last_interaction_at');
        });
    }
};
