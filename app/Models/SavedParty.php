<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedParty extends Model
{
    use HasFactory;

    protected $table = 'saved_parties';

    protected $fillable = [
        'game_account_id',
        'enemy_id',
        'name',
        'character_ids',
        'synergy_score',
        'synergy_tier',
        'notes',
    ];

    protected $casts = [
        'character_ids' => 'array',
        'synergy_score' => 'integer',
    ];

    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    public function enemy(): BelongsTo
    {
        return $this->belongsTo(Enemy::class, 'enemy_id');
    }

    /**
     * Mengambil koleksi model Character berdasarkan array character_ids
     */
    public function getCharactersAttribute()
    {
        if (empty($this->character_ids)) {
            return collect();
        }
        return Character::whereIn('id', $this->character_ids)->get();
    }
}