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
        Schema::create('inventory_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_account_id')->constrained('game_accounts')->cascadeOnDelete();
            $table->foreignId('artifact_set_id')->constrained('artifact_sets')->cascadeOnDelete();
            $table->enum('slot_key', ['flower', 'plume', 'sands', 'goblet', 'circlet'])->index();
            $table->unsignedTinyInteger('rarity')->default(5); // 1-5
            $table->unsignedTinyInteger('level')->default(20); // 0-20
            $table->string('main_stat_key')->default('hp'); // hp, atk, hp_percent, atk_percent, def_percent, energy_recharge, elemental_mastery, crit_rate, crit_dmg, etc.
            $table->string('main_stat_value')->default('4780'); // e.g. "46.6%", "4780", "62.2%"
            $table->json('sub_stats')->nullable(); // [{"key": "crit_rate", "value": "7.0%"}, ...]
            $table->foreignId('equipped_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['game_account_id', 'slot_key']);
            $table->index(['game_account_id', 'equipped_character_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_artifacts');
    }
};
