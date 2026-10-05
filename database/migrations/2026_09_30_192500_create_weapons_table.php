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
        Schema::create('weapons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('type'); // Sword, Claymore, Polearm, Bow, Catalyst
            $table->tinyInteger('rarity')->default(4); // 1 - 5
            $table->unsignedSmallInteger('base_atk')->default(454); // Base ATK di Lv. 90
            $table->string('sub_stat_type')->nullable(); // CRIT DMG, CRIT Rate, ATK%, Energy Recharge, Elemental Mastery, HP%, DEF%
            $table->string('sub_stat_value')->nullable(); // e.g. 66.2%, 33.1%, 55.1%
            $table->string('passive_name')->nullable();
            $table->text('passive_desc')->nullable();
            $table->string('icon_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weapons');
    }
};
