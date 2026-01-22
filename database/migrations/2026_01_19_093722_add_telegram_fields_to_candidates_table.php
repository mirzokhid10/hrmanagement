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
        Schema::table('candidates', function (Blueprint $table) {
            // Nullable because HH.ru candidates won't have this
            $table->bigInteger('telegram_chat_id')->nullable()->index()->after('email');

            // To differentiate source
            $table->string('source')->default('manual')->after('status'); // 'telegram', 'hh_ru', 'manual'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'source']);
        });
    }
};
