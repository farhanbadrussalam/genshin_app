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
        // 1. Tabel App Settings (Key-Value fleksibel)
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel AI Usage Logs (Monitoring Kuota & Limit)
        if (!Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('feature', 50)->index(); // 'smart_team_builder', 'chat', 'compare', 'vision_extract', 'good_export'
                $table->string('model_used', 100)->index();
                $table->unsignedInteger('prompt_tokens')->default(0);
                $table->unsignedInteger('candidates_tokens')->default(0);
                $table->unsignedInteger('total_tokens')->default(0);
                $table->string('status', 20)->default('success'); // 'success', 'fallback', 'error'
                $table->text('error_message')->nullable();
                $table->unsignedInteger('duration_ms')->default(0);
                $table->timestamp('created_at')->useCurrent()->index();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('app_settings');
    }
};
