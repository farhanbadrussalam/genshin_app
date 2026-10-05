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
        Schema::create('game_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // nullable jika belum ada auth
            $table->string('game')->default('genshin_impact');          // genshin_impact, dll
            $table->string('uid', 20)->unique();                        // UID player di game
            $table->string('nickname', 100);                            // nama akun game
            $table->string('server', 10)->default('asia');              // asia, na, eu, cht
            $table->string('avatar_url')->nullable();                   // foto profil akun
            $table->text('notes')->nullable();                          // catatan tambahan
            $table->timestamp('last_synced_at')->nullable();            // terakhir sync Enka.Network
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_accounts');
    }
};
