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
        Schema::create('resin_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_account_id')->constrained('game_accounts')->cascadeOnDelete();
            $table->integer('resin_amount');
            $table->integer('max_resin')->default(200);
            $table->integer('threshold')->default(160);
            $table->string('alert_type', 40)->default('threshold_reached'); // threshold_reached, resin_capped
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('notified_at');
            $table->timestamps();

            $table->index(['game_account_id', 'is_read']);
            $table->index(['game_account_id', 'notified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resin_alerts');
    }
};
