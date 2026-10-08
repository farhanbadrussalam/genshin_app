<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_account_id')->nullable()->constrained('game_accounts')->nullOnDelete();
            $table->foreignId('enemy_id')->constrained('enemies')->cascadeOnDelete();
            $table->string('name')->default('My Party');
            $table->json('character_ids'); // [1, 5, 12, 20]
            $table->integer('synergy_score')->default(0);
            $table->string('synergy_tier', 10)->default('B');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_parties');
    }
};