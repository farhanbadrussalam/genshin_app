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
        Schema::create('inventory_characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_account_id')->constrained('game_accounts')->cascadeOnDelete();
            $table->foreignId('character_id')->constrained('characters')->cascadeOnDelete();
            $table->unsignedSmallInteger('level')->default(1); // 1 - 90
            $table->unsignedTinyInteger('ascension')->default(0); // 0 - 6
            $table->unsignedTinyInteger('constellation')->default(0); // 0 - 6
            $table->unsignedTinyInteger('talent_attack')->default(1); // 1 - 10
            $table->unsignedTinyInteger('talent_skill')->default(1); // 1 - 10
            $table->unsignedTinyInteger('talent_burst')->default(1); // 1 - 10
            $table->text('notes')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            // 1 akun game hanya memiliki 1 entri per karakter
            $table->unique(['game_account_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_characters');
    }
};
