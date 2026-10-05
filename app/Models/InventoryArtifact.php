<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryArtifact extends Model
{
    use HasFactory;

    protected $table = 'inventory_artifacts';

    protected $fillable = [
        'game_account_id',
        'artifact_set_id',
        'slot_key',
        'rarity',
        'level',
        'main_stat_key',
        'main_stat_value',
        'sub_stats',
        'equipped_character_id',
        'notes',
        'scanned_at',
        // Scoring fields
        'score',
        'score_rating',
        'score_details',
        'scored_for_character_id',
    ];

    protected $casts = [
        'rarity'       => 'integer',
        'level'        => 'integer',
        'sub_stats'    => 'array',
        'score'        => 'float',
        'score_details'=> 'array',
        'scanned_at'   => 'datetime',
    ];

    /**
     * Akun game pemilik artifact
     */
    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    /**
     * Master artifact set
     */
    public function artifactSet(): BelongsTo
    {
        return $this->belongsTo(ArtifactSet::class, 'artifact_set_id');
    }

    /**
     * Karakter yang sedang memakai artifact ini (opsional)
     */
    public function equippedCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'equipped_character_id');
    }

    /**
     * Karakter yang digunakan sebagai referensi kalkulasi score
     */
    public function scoredForCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'scored_for_character_id');
    }

    /**
     * Nama label slot piece
     */
    public function getSlotLabelAttribute(): string
    {
        return match ($this->slot_key) {
            'flower'  => 'Flower of Life',
            'plume'   => 'Plume of Death',
            'sands'   => 'Sands of Eon',
            'goblet'  => 'Goblet of Eonothem',
            'circlet' => 'Circlet of Logos',
            default   => ucfirst($this->slot_key),
        };
    }

    /**
     * Icon bootstrap untuk slot piece
     */
    public function getSlotIconAttribute(): string
    {
        return match ($this->slot_key) {
            'flower'  => 'bi-flower1',
            'plume'   => 'bi-feather',
            'sands'   => 'bi-hourglass-split',
            'goblet'  => 'bi-cup-straw',
            'circlet' => 'bi-circle-half',
            default   => 'bi-gem',
        };
    }

    /**
     * Nama label main stat yang ramah dibaca
     */
    public function getMainStatLabelAttribute(): string
    {
        return match ($this->main_stat_key) {
            'hp'                => 'HP',
            'atk'               => 'ATK',
            'hp_percent'        => 'HP%',
            'atk_percent'       => 'ATK%',
            'def_percent'       => 'DEF%',
            'energy_recharge'   => 'Energy Recharge',
            'elemental_mastery' => 'Elemental Mastery',
            'crit_rate'         => 'CRIT Rate',
            'crit_dmg'          => 'CRIT DMG',
            'healing_bonus'     => 'Healing Bonus',
            'pyro_dmg'          => 'Pyro DMG Bonus',
            'hydro_dmg'         => 'Hydro DMG Bonus',
            'dendro_dmg'        => 'Dendro DMG Bonus',
            'electro_dmg'       => 'Electro DMG Bonus',
            'anemo_dmg'         => 'Anemo DMG Bonus',
            'cryo_dmg'          => 'Cryo DMG Bonus',
            'geo_dmg'           => 'Geo DMG Bonus',
            'physical_dmg'      => 'Physical DMG Bonus',
            default             => strtoupper(str_replace('_', ' ', $this->main_stat_key)),
        };
    }

    /**
     * Max level berdasarkan rarity
     */
    public function getMaxLevelAttribute(): int
    {
        return $this->rarity === 5 ? 20 : 16;
    }

    /**
     * URL Icon artefak spesifik sesuai slot (flower, plume, sands, goblet, circlet)
     */
    public function getIconUrlAttribute(): ?string
    {
        $set = $this->artifactSet;
        if (!$set) {
            return null;
        }

        $setId = $set->set_id;
        if (!$setId && !empty($set->icon_url) && preg_match('/UI_RelicIcon_(\d+)_/i', $set->icon_url, $m)) {
            $setId = (int) $m[1];
        }

        if ($setId) {
            $slotNum = match ($this->slot_key) {
                'goblet'  => 1,
                'plume'   => 2,
                'circlet' => 3,
                'flower'  => 4,
                'sands'   => 5,
                default   => 4,
            };
            return "https://enka.network/ui/UI_RelicIcon_{$setId}_{$slotNum}.png";
        }

        return $set->icon_url;
    }

    public function getPieceIconUrlAttribute(): ?string
    {
        return $this->icon_url;
    }
}

