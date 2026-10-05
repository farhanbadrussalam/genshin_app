<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom scoring ke tabel inventory_artifacts
     */
    public function up(): void
    {
        Schema::table('inventory_artifacts', function (Blueprint $table) {
            // Score numerik total (0-100)
            $table->float('score', 5, 2)->nullable()->after('notes');
            // Rating huruf: SS, S, A, B, C, D
            $table->enum('score_rating', ['SS', 'S', 'A', 'B', 'C', 'D'])->nullable()->after('score');
            // Breakdown detail per sub-stat (JSON): {"crit_rate": 8.2, "crit_dmg": 15.5, ...}
            $table->json('score_details')->nullable()->after('score_rating');
            // Karakter rujukan yang digunakan untuk kalkulasi terakhir
            $table->unsignedBigInteger('scored_for_character_id')->nullable()->after('score_details');
            $table->foreign('scored_for_character_id')->references('id')->on('characters')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_artifacts', function (Blueprint $table) {
            $table->dropForeign(['scored_for_character_id']);
            $table->dropColumn(['score', 'score_rating', 'score_details', 'scored_for_character_id']);
        });
    }
};
