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
        Schema::table('game_accounts', function (Blueprint $table) {
            $table->boolean('auto_checkin_enabled')->default(true)->after('ltoken_v2');
            $table->timestamp('last_checkin_at')->nullable()->after('auto_checkin_enabled');
            $table->string('last_checkin_status', 30)->nullable()->after('last_checkin_at'); // success, already_claimed, failed
            $table->text('last_checkin_message')->nullable()->after('last_checkin_status');

            $table->boolean('resin_alert_enabled')->default(true)->after('last_checkin_message');
            $table->integer('resin_alert_threshold')->default(160)->after('resin_alert_enabled');
            $table->integer('last_known_resin')->nullable()->after('resin_alert_threshold');
            $table->integer('last_known_resin_max')->default(200)->after('last_known_resin');
            $table->timestamp('last_resin_synced_at')->nullable()->after('last_known_resin_max');
            $table->timestamp('last_resin_alert_at')->nullable()->after('last_resin_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'auto_checkin_enabled',
                'last_checkin_at',
                'last_checkin_status',
                'last_checkin_message',
                'resin_alert_enabled',
                'resin_alert_threshold',
                'last_known_resin',
                'last_known_resin_max',
                'last_resin_synced_at',
                'last_resin_alert_at',
            ]);
        });
    }
};
