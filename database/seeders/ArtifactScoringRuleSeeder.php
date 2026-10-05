<?php

namespace Database\Seeders;

use App\Models\ArtifactScoringRule;
use App\Models\Character;
use Illuminate\Database\Seeder;

/**
 * Seed aturan scoring artifact untuk karakter-karakter populer Genshin Impact
 * Berdasarkan tier list dan rekomendasi komunitas (Genshin Helper Table)
 */
class ArtifactScoringRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [character_slug_or_name => [bobot, role, catatan]]
        $rules = [
            // ─── Pyro DPS ───────────────────────────────────────────
            'Hu Tao' => [
                'role' => 'dps',
                'note' => 'HP scaling → Crimson Witch 4pc, goblet HP%',
                'w_crit_rate' => 0.75, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.0,   'w_hp_pct' => 1.0,
                'w_def_pct' => 0.0,   'w_em' => 0.5,
                'w_er' => 0.25,       'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.05,  'w_flat_def' => 0.0,
            ],
            'Arlecchino' => [
                'role' => 'dps',
                'note' => 'ATK/CRIT → Fragment of Harmonic Whimsy 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.75,  'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.0,
                'w_er' => 0.25,       'w_flat_atk' => 0.1,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            'Yoimiya' => [
                'role' => 'dps',
                'note' => 'Normal Attack DPS → Shimenawa / Marechaussee',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.75,  'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.0,
                'w_er' => 0.25,       'w_flat_atk' => 0.1,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            // ─── Hydro ─────────────────────────────────────────────
            'Neuvillette' => [
                'role' => 'dps',
                'note' => 'HP scaling + CRIT → Marechaussee Hunter 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.0,   'w_hp_pct' => 0.75,
                'w_def_pct' => 0.0,   'w_em' => 0.0,
                'w_er' => 0.25,       'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.05,  'w_flat_def' => 0.0,
            ],
            'Tartaglia' => [
                'role' => 'dps',
                'note' => 'Normal Attack Hydro → Heart of Depth 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.75,  'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.0,
                'w_er' => 0.25,       'w_flat_atk' => 0.1,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            // ─── Electro ───────────────────────────────────────────
            'Raiden Shogun' => [
                'role' => 'sub_dps',
                'note' => 'ER scaling burst → Emblem of Severed Fate 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.5,   'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.25,
                'w_er' => 1.0,        'w_flat_atk' => 0.1,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            'Cyno' => [
                'role' => 'dps',
                'note' => 'EM/CRIT → Gilded Dreams 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.5,   'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.75,
                'w_er' => 0.25,       'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            // ─── Anemo Support ────────────────────────────────────
            'Kazuha' => [
                'role' => 'support',
                'note' => 'EM/ER → Viridescent Venerer 4pc',
                'w_crit_rate' => 0.25, 'w_crit_dmg' => 0.25,
                'w_atk_pct' => 0.0,    'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,    'w_em' => 1.0,
                'w_er' => 0.75,        'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.0,    'w_flat_def' => 0.0,
            ],
            'Venti' => [
                'role' => 'support',
                'note' => 'EM/ER → Viridescent Venerer 4pc',
                'w_crit_rate' => 0.25, 'w_crit_dmg' => 0.25,
                'w_atk_pct' => 0.0,    'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,    'w_em' => 1.0,
                'w_er' => 0.75,        'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.0,    'w_flat_def' => 0.0,
            ],
            // ─── Geo ───────────────────────────────────────────────
            'Zhongli' => [
                'role' => 'support',
                'note' => 'HP → Tenacity of Millelith 4pc',
                'w_crit_rate' => 0.0,  'w_crit_dmg' => 0.0,
                'w_atk_pct' => 0.0,    'w_hp_pct' => 1.0,
                'w_def_pct' => 0.0,    'w_em' => 0.0,
                'w_er' => 0.5,         'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.1,    'w_flat_def' => 0.0,
            ],
            // ─── Cryo DPS ─────────────────────────────────────────
            'Ganyu' => [
                'role' => 'dps',
                'note' => 'CRIT melt → Blizzard Strayer 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.75,  'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.5,
                'w_er' => 0.25,       'w_flat_atk' => 0.1,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            'Ayaka' => [
                'role' => 'dps',
                'note' => 'CRIT/ATK → Blizzard Strayer 4pc',
                'w_crit_rate' => 1.0, 'w_crit_dmg' => 1.0,
                'w_atk_pct' => 0.75,  'w_hp_pct' => 0.0,
                'w_def_pct' => 0.0,   'w_em' => 0.0,
                'w_er' => 0.5,        'w_flat_atk' => 0.1,
                'w_flat_hp' => 0.0,   'w_flat_def' => 0.0,
            ],
            // ─── Healer ───────────────────────────────────────────
            'Kokomi' => [
                'role' => 'healer',
                'note' => 'HP/Healing → Ocean-Hued Clam 4pc',
                'w_crit_rate' => 0.0,  'w_crit_dmg' => 0.0,
                'w_atk_pct' => 0.0,    'w_hp_pct' => 1.0,
                'w_def_pct' => 0.0,    'w_em' => 0.25,
                'w_er' => 0.75,        'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.1,    'w_flat_def' => 0.0,
            ],
            'Bennett' => [
                'role' => 'support',
                'note' => 'HP/ER → Noblesse Oblige 4pc',
                'w_crit_rate' => 0.0,  'w_crit_dmg' => 0.0,
                'w_atk_pct' => 0.0,    'w_hp_pct' => 1.0,
                'w_def_pct' => 0.0,    'w_em' => 0.0,
                'w_er' => 1.0,         'w_flat_atk' => 0.0,
                'w_flat_hp' => 0.1,    'w_flat_def' => 0.0,
            ],
        ];

        $count = 0;
        foreach ($rules as $charName => $data) {
            $character = Character::where('name', $charName)->first();
            if (!$character) {
                $this->command->warn("  ⚠️  Karakter tidak ditemukan: $charName");
                continue;
            }

            ArtifactScoringRule::updateOrCreate(
                ['character_id' => $character->id],
                [
                    'role'        => $data['role'],
                    'build_note'  => $data['note'],
                    'w_crit_rate' => $data['w_crit_rate'],
                    'w_crit_dmg'  => $data['w_crit_dmg'],
                    'w_atk_pct'   => $data['w_atk_pct'],
                    'w_hp_pct'    => $data['w_hp_pct'],
                    'w_def_pct'   => $data['w_def_pct'],
                    'w_em'        => $data['w_em'],
                    'w_er'        => $data['w_er'],
                    'w_flat_atk'  => $data['w_flat_atk'],
                    'w_flat_hp'   => $data['w_flat_hp'],
                    'w_flat_def'  => $data['w_flat_def'],
                ]
            );

            $this->command->info("  ✅ $charName [{$data['role']}]");
            $count++;
        }

        $this->command->info("  📊 Total: $count aturan scoring berhasil di-seed.");
    }
}
