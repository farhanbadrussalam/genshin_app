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
        Schema::create('enemies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('Common Enemies'); // Common Enemies, Elite Enemies, Normal Bosses, Weekly Bosses
            $table->string('family')->nullable(); // Automatons, Hilichurls, The Abyss, Fatui, Elemental Lifeforms, etc.
            $table->string('region')->nullable(); // Mondstadt, Liyue, Inazuma, Sumeru, Fontaine, Natlan, Global
            $table->json('elements')->nullable(); // ["Pyro", "Cryo", ...]
            $table->text('description')->nullable();
            $table->string('icon_url')->nullable();
            $table->integer('mora_gained')->default(0);

            // Kolom Taktik & Analisis Party Counter
            $table->json('immunities')->nullable(); // ["Pyro"]
            $table->json('elemental_res')->nullable(); // {"physical": 70, "pyro": 10, ...}
            $table->json('weakness_elements')->nullable(); // ["Cryo", "Dendro"]
            $table->json('recommended_mechanics')->nullable(); // ["bow", "shield_breaker", "crowd_control", "shielder"]
            $table->text('tips_strategy')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('enemy_drops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enemy_id')->constrained('enemies')->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->string('name');
            $table->string('drop_type')->default('material'); // material, artifact, other
            $table->integer('rarity')->nullable(); // 1 - 5
            $table->integer('minimum_level')->nullable(); // 1, 40, 60, dst
            $table->string('source_note')->nullable();
            $table->string('icon_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enemy_drops');
        Schema::dropIfExists('enemies');
    }
};