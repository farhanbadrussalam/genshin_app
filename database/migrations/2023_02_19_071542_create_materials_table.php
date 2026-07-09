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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('familie_id')->constrained('families');
            $table->integer('rarity')->nullable();
            $table->string('category');
            $table->string('materialtype')->nullable();
            $table->string('dropdomain')->nullable();
            $table->integer('amount');
            $table->text('description');
            $table->string('images')->nullable();
            $table->json('daysofweek')->nullable();
            $table->json('source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
