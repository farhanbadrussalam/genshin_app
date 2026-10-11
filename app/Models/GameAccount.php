<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game',
        'uid',
        'nickname',
        'server',
        'ltuid_v2',
        'ltoken_v2',
        'avatar_url',
        'notes',
        'last_synced_at',
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
    ];

    protected $casts = [
        'last_synced_at'       => 'datetime',
        'auto_checkin_enabled' => 'boolean',
        'resin_alert_enabled'  => 'boolean',
        'last_checkin_at'      => 'datetime',
        'last_resin_synced_at' => 'datetime',
        'last_resin_alert_at'  => 'datetime',
    ];

    /**
     * Daftar game yang didukung
     */
    public static function supportedGames(): array
    {
        return [
            'genshin_impact' => 'Genshin Impact',
            'honkai_star_rail' => 'Honkai: Star Rail',
            'zenless_zone_zero' => 'Zenless Zone Zero',
        ];
    }

    /**
     * Daftar server yang didukung
     */
    public static function supportedServers(): array
    {
        return [
            'asia' => 'Asia',
            'na'   => 'America (NA)',
            'eu'   => 'Europe (EU)',
            'cht'  => 'TW/HK/MO (CHT)',
        ];
    }

    /**
     * Label game yang terbaca manusia
     */
    public function getGameLabelAttribute(): string
    {
        return self::supportedGames()[$this->game] ?? $this->game;
    }

    /**
     * Label server yang terbaca manusia
     */
    public function getServerLabelAttribute(): string
    {
        return self::supportedServers()[$this->server] ?? $this->server;
    }

    /**
     * Cek apakah akun memiliki cookie HoYoLAB yang lengkap
     */
    public function hasHoyoLabCookies(): bool
    {
        return !empty($this->ltuid_v2) && !empty($this->ltoken_v2);
    }

    /**
     * Relasi ke User (jika auth sudah diaktifkan)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Target task yang dimiliki akun game ini
     */
    public function tasks()
    {
        return $this->hasMany(task::class, 'game_account_id');
    }

    /**
     * Karakter yang dimiliki akun game ini
     */
    public function inventoryCharacters()
    {
        return $this->hasMany(InventoryCharacter::class, 'game_account_id');
    }

    /**
     * Senjata yang dimiliki akun game ini
     */
    public function inventoryWeapons()
    {
        return $this->hasMany(InventoryWeapon::class, 'game_account_id');
    }

    /**
     * Artifact yang dimiliki akun game ini
     */
    public function inventoryArtifacts()
    {
        return $this->hasMany(InventoryArtifact::class, 'game_account_id');
    }

    /**
     * Material yang dimiliki akun game ini
     */
    public function inventoryMaterials()
    {
        return $this->hasMany(InventoryMaterial::class, 'game_account_id');
    }

    /**
     * Log check-in harian
     */
    public function dailyCheckinLogs()
    {
        return $this->hasMany(DailyCheckinLog::class, 'game_account_id')->latest('checked_at');
    }

    /**
     * Log alert resin
     */
    public function resinAlerts()
    {
        return $this->hasMany(ResinAlert::class, 'game_account_id')->latest('notified_at');
    }

    /**
     * Alert resin yang belum dibaca
     */
    public function unreadResinAlerts()
    {
        return $this->hasMany(ResinAlert::class, 'game_account_id')->where('is_read', false);
    }
}
