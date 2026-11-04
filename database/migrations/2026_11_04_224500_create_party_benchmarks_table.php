<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saved_party_id')->constrained('saved_parties')->cascadeOnDelete();
            $table->foreignId('game_account_id')->nullable()->constrained('game_accounts')->nullOnDelete();
            $table->string('title')->default('Hasil Pengujian DPS');
            $table->string('preset_name')->nullable()->default('Spiral Abyss Lantai 12');
            $table->unsignedBigInteger('dps')->default(0);
            $table->decimal('total_time', 8, 2)->default(0);
            $table->unsignedBigInteger('total_damage')->default(0);
            $table->unsignedBigInteger('max_hit')->default(0);
            $table->json('target_dummy')->nullable();
            $table->text('modifiers')->nullable();
            $table->json('character_dmg_distribution')->nullable();
            $table->json('elemental_dmg_distribution')->nullable();
            $table->json('rotations')->nullable();
            $table->json('character_stats')->nullable();
            $table->string('stage_guid', 50)->nullable();
            $table->string('uid', 30)->nullable();
            $table->json('evaluation')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_benchmarks');
    }
};

