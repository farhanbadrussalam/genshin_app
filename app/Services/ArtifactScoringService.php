<?php

namespace App\Services;

use App\Models\ArtifactScoringRule;
use App\Models\Character;
use App\Models\InventoryArtifact;
use Illuminate\Support\Collection;

/**
 * ArtifactScoringService
 *
 * Menghitung skor kualitas artifact berdasarkan sub-stat yang relevan
 * untuk karakter tertentu. Algoritma mengadopsi pendekatan "Crit Value" (CV)
 * yang umum digunakan komunitas Genshin Impact.
 *
 * Formula:
 *   Score = Σ (sub_stat_value × bobot_stat)
 *
 * Rating:
 *   SS = ≥ 55, S = ≥ 45, A = ≥ 35, B = ≥ 25, C = ≥ 15, D = < 15
 */
class ArtifactScoringService
{
    /**
     * Nilai referensi "satu roll" untuk tiap sub-stat (nilai rata-rata per roll)
     * Digunakan untuk normalisasi skor agar adil antar stat
     */
    private const STAT_ROLL_REFERENCE = [
        'crit_rate'  => 3.3,   // %
        'crit_dmg'   => 6.6,   // %
        'atk_pct'    => 4.95,  // %
        'hp_pct'     => 4.95,  // %
        'def_pct'    => 6.2,   // %
        'em'         => 19.75, // flat
        'er'         => 5.5,   // %
        'flat_atk'   => 16.5,  // flat
        'flat_hp'    => 253.0, // flat
        'flat_def'   => 19.4,  // flat
    ];

    /**
     * Mapping dari berbagai key sub-stat ke key internal kita
     * (dari HoYoLAB API, Enka, dll.)
     */
    private const STAT_KEY_MAP = [
        // CRIT
        'critRate_'          => 'crit_rate',
        'crit_rate'          => 'crit_rate',
        'crit rate'          => 'crit_rate',
        'CRITICAL'           => 'crit_rate',
        // CRIT DMG
        'critDMG_'           => 'crit_dmg',
        'crit_dmg'           => 'crit_dmg',
        'crit dmg'           => 'crit_dmg',
        'CRITICAL_HURT'      => 'crit_dmg',
        // ATK%
        'atkPercent'         => 'atk_pct',
        'atk_percent'        => 'atk_pct',
        'atk%'               => 'atk_pct',
        'ATK_PERCENT'        => 'atk_pct',
        // HP%
        'hpPercent'          => 'hp_pct',
        'hp_percent'         => 'hp_pct',
        'hp%'                => 'hp_pct',
        'HP_PERCENT'         => 'hp_pct',
        // DEF%
        'defPercent'         => 'def_pct',
        'def_percent'        => 'def_pct',
        'def%'               => 'def_pct',
        'DEFEND_PERCENT'     => 'def_pct',
        // EM
        'elementalMastery'   => 'em',
        'elemental_mastery'  => 'em',
        'em'                 => 'em',
        'ELEMENT_MASTERY'    => 'em',
        // ER
        'energyRecharge_'    => 'er',
        'energy_recharge'    => 'er',
        'er'                 => 'er',
        'CHARGE_EFFICIENCY'  => 'er',
        // Flat ATK
        'atk'                => 'flat_atk',
        'flat_atk'           => 'flat_atk',
        'ATK'                => 'flat_atk',
        // Flat HP
        'hp'                 => 'flat_hp',
        'flat_hp'            => 'flat_hp',
        'HP'                 => 'flat_hp',
        // Flat DEF
        'def'                => 'flat_def',
        'flat_def'           => 'flat_def',
        'DEFEND'             => 'flat_def',
    ];

    /**
     * Hitung skor artifact untuk karakter tertentu
     *
     * @param InventoryArtifact $artifact
     * @param Character|null $character Jika null, gunakan karakter equipped
     * @param ArtifactScoringRule|null $rule Jika null, gunakan default (full CV)
     * @return array ['score' => float, 'rating' => string, 'details' => array]
     */
    public function score(
        InventoryArtifact $artifact,
        ?Character $character = null,
        ?ArtifactScoringRule $rule = null
    ): array {
        // Jika tidak ada karakter, pakai yang equipped
        $character ??= $artifact->equippedCharacter;

        // Ambil rule scoring
        if ($rule === null && $character !== null) {
            $rule = ArtifactScoringRule::where('character_id', $character->id)->first();
        }

        // Jika masih tidak ada rule, pakai default CV (crit-focused)
        $weights = $rule?->getWeightsArray() ?? $this->getDefaultWeights();

        // Ambil sub-stats
        $subStats = $artifact->sub_stats ?? [];

        if (empty($subStats)) {
            return [
                'score'    => 0.0,
                'rating'   => 'D',
                'details'  => [],
                'character'=> $character?->name,
            ];
        }

        $totalScore = 0.0;
        $details    = [];

        foreach ($subStats as $stat) {
            $key   = $this->normalizeStatKey($stat['key'] ?? $stat['stat'] ?? '');
            $value = (float) ($stat['value'] ?? $stat['val'] ?? 0);

            if ($key === null) continue;

            $weight    = $weights[$key] ?? 0.0;
            $reference = self::STAT_ROLL_REFERENCE[$key] ?? 1.0;

            // Normalisasi: nilai stat dibagi reference value per roll
            // → artinya 1 roll perfect = 1.0 poin
            $rolls     = $value / $reference;
            $statScore = round($rolls * $weight * 10, 2); // ×10 agar range lebih intuitif

            $details[$key] = [
                'value'      => $value,
                'weight'     => $weight,
                'rolls'      => round($rolls, 2),
                'contribution' => $statScore,
                'label'      => $this->getStatLabel($key),
            ];

            $totalScore += $statScore;
        }

        $totalScore = round(min($totalScore, 100), 2);
        $rating     = $this->getRating($totalScore);

        return [
            'score'    => $totalScore,
            'rating'   => $rating,
            'details'  => $details,
            'character'=> $character?->name,
        ];
    }

    /**
     * Hitung dan simpan skor artifact ke database
     */
    public function scoreAndSave(
        InventoryArtifact $artifact,
        ?Character $character = null,
        ?ArtifactScoringRule $rule = null
    ): InventoryArtifact {
        $result = $this->score($artifact, $character, $rule);

        $artifact->update([
            'score'                  => $result['score'],
            'score_rating'           => $result['rating'],
            'score_details'          => $result['details'],
            'scored_for_character_id'=> $character?->id,
        ]);

        return $artifact->refresh();
    }

    /**
     * Hitung skor semua artifact milik sebuah game account
     * Artifact yang terpasang ke karakter → di-score untuk karakter itu
     * Artifact yang tidak terpasang → di-score dengan default CV
     *
     * @return array ['processed' => int, 'skipped' => int]
     */
    public function scoreAllForAccount(int $gameAccountId): array
    {
        $artifacts = InventoryArtifact::with(['equippedCharacter'])
            ->where('game_account_id', $gameAccountId)
            ->whereNotNull('sub_stats') // hanya yang punya sub-stats
            ->get();

        $processed = 0;
        $skipped   = 0;

        foreach ($artifacts as $artifact) {
            if (empty($artifact->sub_stats)) {
                $skipped++;
                continue;
            }

            $character = $artifact->equippedCharacter;
            $rule      = $character
                ? ArtifactScoringRule::where('character_id', $character->id)->first()
                : null;

            $this->scoreAndSave($artifact, $character, $rule);
            $processed++;
        }

        return ['processed' => $processed, 'skipped' => $skipped];
    }

    /**
     * Bobot default (CV-based): hanya crit rate + crit dmg yang dihitung
     */
    public function getDefaultWeights(): array
    {
        return [
            'crit_rate' => 1.0,
            'crit_dmg'  => 1.0,
            'atk_pct'   => 0.5,
            'hp_pct'    => 0.25,
            'def_pct'   => 0.0,
            'em'        => 0.25,
            'er'        => 0.25,
            'flat_atk'  => 0.1,
            'flat_hp'   => 0.0,
            'flat_def'  => 0.0,
        ];
    }

    /**
     * Template bobot berdasarkan archetype karakter
     */
    public function getTemplateWeights(string $archetype): array
    {
        return match ($archetype) {
            'dps_crit' => [
                'crit_rate' => 1.0, 'crit_dmg' => 1.0, 'atk_pct' => 0.75,
                'hp_pct' => 0.0, 'def_pct' => 0.0, 'em' => 0.25,
                'er' => 0.25, 'flat_atk' => 0.1, 'flat_hp' => 0.0, 'flat_def' => 0.0,
            ],
            'dps_em' => [
                'crit_rate' => 0.5, 'crit_dmg' => 0.5, 'atk_pct' => 0.25,
                'hp_pct' => 0.0, 'def_pct' => 0.0, 'em' => 1.0,
                'er' => 0.25, 'flat_atk' => 0.0, 'flat_hp' => 0.0, 'flat_def' => 0.0,
            ],
            'hp_scaling' => [
                'crit_rate' => 0.75, 'crit_dmg' => 0.75, 'atk_pct' => 0.0,
                'hp_pct' => 1.0, 'def_pct' => 0.0, 'em' => 0.0,
                'er' => 0.5, 'flat_atk' => 0.0, 'flat_hp' => 0.1, 'flat_def' => 0.0,
            ],
            'support' => [
                'crit_rate' => 0.25, 'crit_dmg' => 0.25, 'atk_pct' => 0.0,
                'hp_pct' => 0.5, 'def_pct' => 0.25, 'em' => 0.5,
                'er' => 1.0, 'flat_atk' => 0.0, 'flat_hp' => 0.0, 'flat_def' => 0.0,
            ],
            'healer' => [
                'crit_rate' => 0.0, 'crit_dmg' => 0.0, 'atk_pct' => 0.0,
                'hp_pct' => 1.0, 'def_pct' => 0.25, 'em' => 0.0,
                'er' => 1.0, 'flat_atk' => 0.0, 'flat_hp' => 0.1, 'flat_def' => 0.0,
            ],
            default => $this->getDefaultWeights(),
        };
    }

    /**
     * Normalisasi key sub-stat ke format internal
     */
    private function normalizeStatKey(string $raw): ?string
    {
        $raw = trim(strtolower($raw));

        foreach (self::STAT_KEY_MAP as $pattern => $internal) {
            if (strtolower($pattern) === $raw) {
                return $internal;
            }
        }

        // Fuzzy match
        if (str_contains($raw, 'crit') && str_contains($raw, 'rate')) return 'crit_rate';
        if (str_contains($raw, 'crit') && (str_contains($raw, 'dmg') || str_contains($raw, 'hurt'))) return 'crit_dmg';
        if (str_contains($raw, 'atk') && str_contains($raw, '%')) return 'atk_pct';
        if (str_contains($raw, 'hp') && str_contains($raw, '%')) return 'hp_pct';
        if (str_contains($raw, 'def') && str_contains($raw, '%')) return 'def_pct';
        if (str_contains($raw, 'mastery') || $raw === 'em') return 'em';
        if (str_contains($raw, 'recharge') || $raw === 'er') return 'er';
        if (str_contains($raw, 'atk') && !str_contains($raw, '%')) return 'flat_atk';
        if (str_contains($raw, 'hp') && !str_contains($raw, '%')) return 'flat_hp';
        if (str_contains($raw, 'def') && !str_contains($raw, '%')) return 'flat_def';

        return null;
    }

    /**
     * Label ramah baca untuk sub-stat key
     */
    public function getStatLabel(string $key): string
    {
        return match ($key) {
            'crit_rate' => 'CRIT Rate',
            'crit_dmg'  => 'CRIT DMG',
            'atk_pct'   => 'ATK%',
            'hp_pct'    => 'HP%',
            'def_pct'   => 'DEF%',
            'em'        => 'Elemental Mastery',
            'er'        => 'Energy Recharge',
            'flat_atk'  => 'ATK (Flat)',
            'flat_hp'   => 'HP (Flat)',
            'flat_def'  => 'DEF (Flat)',
            default     => ucwords(str_replace('_', ' ', $key)),
        };
    }

    /**
     * Tentukan rating huruf berdasarkan skor total
     */
    public function getRating(float $score): string
    {
        return match (true) {
            $score >= 55 => 'SS',
            $score >= 45 => 'S',
            $score >= 35 => 'A',
            $score >= 25 => 'B',
            $score >= 15 => 'C',
            default      => 'D',
        };
    }

    /**
     * Daftar semua archetype yang tersedia
     */
    public function getArchetypes(): array
    {
        return [
            'dps_crit'   => 'DPS — CRIT Build (umum)',
            'dps_em'     => 'DPS — Elemental Mastery Build',
            'hp_scaling' => 'DPS — HP Scaling (Hu Tao, Kokomi)',
            'support'    => 'Support (buffer/debuffer)',
            'healer'     => 'Healer / Tank',
        ];
    }
}
