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

    protected $appends = [
        'elemental_resonances',
    ];

    public function gameAccount(): BelongsTo
    {
        return $this->belongsTo(GameAccount::class, 'game_account_id');
    }

    public function enemy(): BelongsTo
    {
        return $this->belongsTo(Enemy::class, 'enemy_id');
    }

    public function benchmarks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PartyBenchmark::class, 'saved_party_id')->latest();
    }

    public function latestBenchmark(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PartyBenchmark::class, 'saved_party_id')->latestOfMany();
    }

    /**
     * Mengambil koleksi model Character berdasarkan array character_ids
     */
    public function getCharactersAttribute()
    {
        if ($this->relationLoaded('characters_cached')) {
            return $this->getRelation('characters_cached');
        }
        if (empty($this->character_ids)) {
            return collect();
        }
        return Character::whereIn('id', $this->character_ids)->get();
    }

    /**
     * Menghitung resonansi elemental aktif berdasarkan karakter yang ada di party
     */
    public function getElementalResonancesAttribute(): array
    {
        $characters = $this->characters;
        if ($characters->isEmpty()) {
            return [];
        }

        $elementCounts = [];
        foreach ($characters as $char) {
            $el = ucfirst(strtolower($char->element ?? ''));
            if ($el) {
                $elementCounts[$el] = ($elementCounts[$el] ?? 0) + 1;
            }
        }

        $resonances = [];
        $isFullParty = count($characters) >= 4;

        foreach ($elementCounts as $el => $cnt) {
            if ($cnt >= 2) {
                $resData = match ($el) {
                    'Pyro' => [
                        'name' => 'Fervent Flames',
                        'element' => 'Pyro',
                        'icon' => 'bi-fire',
                        'badge' => 'Pyro + Pyro',
                        'color' => '#ef4444',
                        'is_active' => $isFullParty,
                        'desc' => 'Meningkatkan Base ATK sebesar +25% dan mengurangi durasi efek Cryo sebesar 40%.',
                    ],
                    'Hydro' => [
                        'name' => 'Soothing Water',
                        'element' => 'Hydro',
                        'icon' => 'bi-droplet-fill',
                        'badge' => 'Hydro + Hydro',
                        'color' => '#3b82f6',
                        'is_active' => $isFullParty,
                        'desc' => 'Meningkatkan Max HP seluruh party sebesar +25% dan mengurangi durasi efek Pyro sebesar 40%.',
                    ],
                    'Cryo' => [
                        'name' => 'Shattering Ice',
                        'element' => 'Cryo',
                        'icon' => 'bi-snow',
                        'badge' => 'Cryo + Cryo',
                        'color' => '#06b6d4',
                        'is_active' => $isFullParty,
                        'desc' => 'Meningkatkan CRIT Rate +15% saat menyerang musuh yang terkena Cryo atau status Frozen.',
                    ],
                    'Geo' => [
                        'name' => 'Enduring Rock',
                        'element' => 'Geo',
                        'icon' => 'bi-shield-shaded',
                        'badge' => 'Geo + Geo',
                        'color' => '#eab308',
                        'is_active' => $isFullParty,
                        'desc' => 'Meningkatkan Shield Strength +15% dan DMG saat dilindungi shield +15%, serta Geo RES musuh berkurang 20%.',
                    ],
                    'Dendro' => [
                        'name' => 'Sprawling Greenery',
                        'element' => 'Dendro',
                        'icon' => 'bi-flower1',
                        'badge' => 'Dendro + Dendro',
                        'color' => '#22c55e',
                        'is_active' => $isFullParty,
                        'desc' => 'Meningkatkan Elemental Mastery sebesar +50 hingga +100 poin saat memicu reaksi Dendro.',
                    ],
                    'Anemo' => [
                        'name' => 'Impetuous Winds',
                        'element' => 'Anemo',
                        'icon' => 'bi-wind',
                        'badge' => 'Anemo + Anemo',
                        'color' => '#10b981',
                        'is_active' => $isFullParty,
                        'desc' => 'Mengurangi konsumsi stamina 15%, meningkatkan Movement Speed 10%, dan mempercepat CD Skill 5%.',
                    ],
                    'Electro' => [
                        'name' => 'High Voltage',
                        'element' => 'Electro',
                        'icon' => 'bi-lightning-charge-fill',
                        'badge' => 'Electro + Electro',
                        'color' => '#a855f7',
                        'is_active' => $isFullParty,
                        'desc' => 'Memulihkan partikel energi Electro (CD 5s) saat memicu reaksi terkait Electro.',
                    ],
                    default => null,
                };

                if ($resData) {
                    $resonances[] = $resData;
                }
            }
        }

        if (count($elementCounts) === 4) {
            $resonances[] = [
                'name' => 'Protective Canopy',
                'element' => 'Multi',
                'icon' => 'bi-shield-fill-check',
                'badge' => '4 Elemen Unik',
                'color' => '#f59e0b',
                'is_active' => $isFullParty,
                'desc' => 'Meningkatkan seluruh Elemental RES & Physical RES sebesar +15%.',
            ];
        }

        return $resonances;
    }
}
