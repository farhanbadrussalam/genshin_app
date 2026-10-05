<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactScoringRule extends Model
{
    use HasFactory;

    protected $table = 'artifact_scoring_rules';

    protected $fillable = [
        'character_id',
        'w_crit_rate',
        'w_crit_dmg',
        'w_atk_pct',
        'w_hp_pct',
        'w_def_pct',
        'w_em',
        'w_er',
        'w_flat_atk',
        'w_flat_hp',
        'w_flat_def',
        'role',
        'build_note',
    ];

    protected $casts = [
        'w_crit_rate' => 'float',
        'w_crit_dmg'  => 'float',
        'w_atk_pct'   => 'float',
        'w_hp_pct'    => 'float',
        'w_def_pct'   => 'float',
        'w_em'        => 'float',
        'w_er'        => 'float',
        'w_flat_atk'  => 'float',
        'w_flat_hp'   => 'float',
        'w_flat_def'  => 'float',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Kembalikan semua bobot sebagai array key => weight
     */
    public function getWeightsArray(): array
    {
        return [
            'crit_rate' => $this->w_crit_rate,
            'crit_dmg'  => $this->w_crit_dmg,
            'atk_pct'   => $this->w_atk_pct,
            'hp_pct'    => $this->w_hp_pct,
            'def_pct'   => $this->w_def_pct,
            'em'        => $this->w_em,
            'er'        => $this->w_er,
            'flat_atk'  => $this->w_flat_atk,
            'flat_hp'   => $this->w_flat_hp,
            'flat_def'  => $this->w_flat_def,
        ];
    }
}
