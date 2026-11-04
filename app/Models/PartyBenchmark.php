<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyBenchmark extends Model
{
    use HasFactory;

    protected $table = 'party_benchmarks';

    protected $fillable = [
        'saved_party_id',
        'game_account_id',
        'title',
        'preset_name',
        'dps',
        'total_time',
        'total_damage',
        'max_hit',
        'target_dummy',
        'modifiers',
        'character_dmg_distribution',
        'elemental_dmg_distribution',
        'rotations',
        'character_stats',
        'stage_guid',
        'uid',
        'evaluation',
        'screenshot_path',
    ];

    protected $casts = [
        'dps'                        => 'integer',
        'total_time'                 => 'float',
        'total_damage'               => 'integer',
        'max_hit'                    => 'integer',
        'target_dummy'               => 'array',
        'character_dmg_distribution' => 'array',
        'elemental_dmg_distribution' => 'array',
        'rotations'                  => 'array',
        'character_stats'            => 'array',
        'evaluation'                 => 'array',
    ];

    public function savedParty(): BelongsTo
    {
        return $this->belongsTo(SavedParty::class, 'saved_party_id');
    }

    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    /**
     * Hitung tier penilaian otomatis berdasarkan DPS
     */
    public function getTierAttribute(): string
    {
        return match (true) {
            $this->dps >= 100000 => 'SS',
            $this->dps >= 70000  => 'S',
            $this->dps >= 50000  => 'A',
            default              => 'B',
        };
    }

    /**
     * Label deskriptif tier
     */
    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'SS'    => 'Overkill / Meta Endgame',
            'S'     => 'Abyss Floor 12 Ready',
            'A'     => 'Layak Lulus 36★',
            default => 'Butuh Peningkatan Build & Rotasi',
        };
    }

    /**
     * Warna representatif tier
     */
    public function getTierColorAttribute(): string
    {
        return match ($this->tier) {
            'SS'    => '#f59e0b', // Gold / Amber
            'S'     => '#10b981', // Emerald
            'A'     => '#3b82f6', // Blue
            default => '#ef4444', // Red
        };
    }
}

