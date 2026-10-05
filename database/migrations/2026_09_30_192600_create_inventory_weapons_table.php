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
        Schema::create('inventory_weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_account_id')->constrained('game_accounts')->cascadeOnDelete();
            $table->foreignId('weapon_id')->constrained('weapons')->cascadeOnDelete();
            $table->unsignedSmallInteger('level')->default(1); // 1 - 90
            $table->unsignedTinyInteger('ascension')->default(0); // 0 - 6
            $table->unsignedTinyInteger('refinement')->default(1); // 1 - 5 (R1 - R5)
            $table->foreignId('equipped_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_weapons');
    }
};
