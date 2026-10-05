<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel artifact_scoring_rules
     * Menyimpan bobot sub-stat per karakter untuk kalkulasi artifact score
     */
    public function up(): void
    {
        Schema::create('artifact_scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('character_id')->unique();
            $table->foreign('character_id')->references('id')->on('characters')->cascadeOnDelete();

            // Bobot untuk setiap sub-stat (0.0 - 1.0)
            // Stat yang tidak relevan = 0, stat penting = 1.0
            $table->float('w_crit_rate', 4, 2)->default(0.0);
            $table->float('w_crit_dmg', 4, 2)->default(0.0);
            $table->float('w_atk_pct', 4, 2)->default(0.0);
            $table->float('w_hp_pct', 4, 2)->default(0.0);
            $table->float('w_def_pct', 4, 2)->default(0.0);
            $table->float('w_em', 4, 2)->default(0.0);       // Elemental Mastery
            $table->float('w_er', 4, 2)->default(0.0);       // Energy Recharge
            $table->float('w_flat_atk', 4, 2)->default(0.0);
            $table->float('w_flat_hp', 4, 2)->default(0.0);
            $table->float('w_flat_def', 4, 2)->default(0.0);

            // Role utama karakter ini: dps, sub_dps, support, healer
            $table->enum('role', ['dps', 'sub_dps', 'support', 'healer'])->default('dps');

            // Catatan opsional tentang build yang dioptimalkan
            $table->string('build_note', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifact_scoring_rules');
    }
};
