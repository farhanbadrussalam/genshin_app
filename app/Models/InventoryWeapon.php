<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryWeapon extends Model
{
    use HasFactory;

    protected $table = 'inventory_weapons';

    protected $fillable = [
        'game_account_id',
        'weapon_id',
        'level',
        'ascension',
        'refinement',
        'equipped_character_id',
        'notes',
        'scanned_at',
    ];

    protected $casts = [
        'level'      => 'integer',
        'ascension'  => 'integer',
        'refinement' => 'integer',
        'scanned_at' => 'datetime',
    ];

    /**
     * Akun game pemilik senjata
     */
    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    /**
     * Data master senjata
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class, 'weapon_id');
    }

    /**
     * Karakter yang sedang memakai senjata ini (opsional)
     */
    public function equippedCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'equipped_character_id');
    }

    /**
     * Helper level cap berdasarkan ascension phase
     */
    public function getMaxLevelAttribute(): int
    {
        return match ($this->ascension) {
            0 => 20,
            1 => 40,
            2 => 50,
            3 => 60,
            4 => 70,
            5 => 80,
            6 => 90,
            default => 90,
        };
    }
}
