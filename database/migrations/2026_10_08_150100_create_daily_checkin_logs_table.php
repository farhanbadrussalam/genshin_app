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
        Schema::create('daily_checkin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_account_id')->constrained('game_accounts')->cascadeOnDelete();
            $table->string('status', 30); // success, already_claimed, failed
            $table->string('reward_name')->nullable();
            $table->integer('reward_amount')->default(1);
            $table->text('reward_icon')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['game_account_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_checkin_logs');
    }
};
