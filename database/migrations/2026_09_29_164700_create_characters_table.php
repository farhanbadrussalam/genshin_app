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
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('element'); // Pyro, Hydro, Anemo, Electro, Dendro, Cryo, Geo
            $table->string('weapon_type'); // Sword, Claymore, Polearm, Bow, Catalyst
            $table->tinyInteger('rarity')->default(4); // 4 atau 5
            $table->string('region')->nullable(); // Mondstadt, Liyue, Inazuma, Sumeru, Fontaine, Natlan, Snezhnaya, Other
            $table->string('icon_url')->nullable();
            $table->string('splash_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
