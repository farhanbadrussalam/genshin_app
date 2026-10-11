<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah tipe kolom ltuid_v2 dan ltoken_v2 menjadi TEXT agar muat ciphertext terenkripsi
        Schema::table('game_accounts', function (Blueprint $table) {
            $table->text('ltuid_v2')->nullable()->change();
            $table->text('ltoken_v2')->nullable()->change();
        });

        // 2. Tambahkan foreign key constraint untuk user_id jika belum ada
        Schema::table('game_accounts', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // 3. Enkripsi data cookie yang saat ini masih berbentuk plain text
        $accounts = DB::table('game_accounts')
            ->whereNotNull('ltuid_v2')
            ->orWhereNotNull('ltoken_v2')
            ->get();

        foreach ($accounts as $acc) {
            $updates = [];
            if (!empty($acc->ltuid_v2)) {
                try {
                    Crypt::decryptString($acc->ltuid_v2);
                } catch (\Throwable $e) {
                    $updates['ltuid_v2'] = Crypt::encryptString(trim($acc->ltuid_v2));
                }
            }

            if (!empty($acc->ltoken_v2)) {
                try {
                    Crypt::decryptString($acc->ltoken_v2);
                } catch (\Throwable $e) {
                    $updates['ltoken_v2'] = Crypt::encryptString(trim($acc->ltoken_v2));
                }
            }

            if (!empty($updates)) {
                DB::table('game_accounts')->where('id', $acc->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_accounts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->string('ltuid_v2', 255)->nullable()->change();
            $table->string('ltoken_v2', 255)->nullable()->change();
        });
    }
};