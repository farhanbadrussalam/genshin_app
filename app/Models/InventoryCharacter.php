<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCharacter extends Model
{
    use HasFactory;

    protected $table = 'inventory_characters';

    protected $fillable = [
        'game_account_id',
        'character_id',
        'level',
        'ascension',
        'constellation',
        'talent_attack',
        'talent_skill',
        'talent_burst',
        'notes',
        'scanned_at',
    ];

    protected $casts = [
        'level'         => 'integer',
        'ascension'     => 'integer',
        'constellation' => 'integer',
        'talent_attack' => 'integer',
        'talent_skill'  => 'integer',
        'talent_burst'  => 'integer',
        'scanned_at'    => 'datetime',
    ];

    /**
     * Akun game pemilik karakter ini
     */
    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    /**
     * Master data karakter
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id');
    }

    /**
     * Helper status level cap berdasarkan ascension
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
