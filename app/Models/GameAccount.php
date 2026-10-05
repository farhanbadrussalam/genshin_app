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
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
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
     * Relasi ke User (jika auth sudah diaktifkan)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
}
