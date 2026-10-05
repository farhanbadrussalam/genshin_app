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
        Schema::table('weapons', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->nullable()->unique()->after('id');
        });

        Schema::table('artifact_sets', function (Blueprint $table) {
            $table->unsignedBigInteger('set_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weapons', function (Blueprint $table) {
            $table->dropColumn('game_id');
        });

        Schema::table('artifact_sets', function (Blueprint $table) {
            $table->dropColumn('set_id');
        });
    }
};
