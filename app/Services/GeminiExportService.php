<?php

namespace App\Services;

use App\Models\ArtifactSet;
use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryMaterial;
use App\Models\InventoryWeapon;
use App\Models\Weapon;
use Illuminate\Support\Collection;

/**
 * Service untuk menyusun dan mengekspor data akun Genshin Impact
 * dalam format komprehensif dan terstruktur untuk analisis AI (Google Gemini / NotebookLM)
 * dan Data Science / Python Pandas di Jupyter Notebook atau Google Colab.
 */
class GeminiExportService
{
    /**
     * Hitung nilai Crit Value (CV) dari kumpulan substat
     * Rumus standar komunitas: (Crit Rate * 2) + Crit DMG
     */
    public static function calculateCritValue(array|null $substats): float
    {
        if (empty($substats)) {
            return 0.0;
        }

        $cr = 0.0;
        $cd = 0.0;

        foreach ($substats as $sub) {
            $key = strtolower($sub['key'] ?? '');
            $val = (float) ($sub['value'] ?? 0);

            if (in_array($key, ['crit_rate', 'critrate', 'critrate_'])) {
                $cr += $val;
            } elseif (in_array($key, ['crit_dmg', 'critdmg', 'critdmg_'])) {
                $cd += $val;
            }
        }

        return round(($cr * 2) + $cd, 1);
    }

    /**
     * Tentukan klasifikasi tier kualitas artefak berdasarkan CV
     */
    public static function getCritValueRating(float $cv): string
    {
        return match (true) {
            $cv >= 45.0 => 'God Piece (SSS)',
            $cv >= 35.0 => 'Exceptional (SS)',
            $cv >= 28.0 => 'Great (S)',
            $cv >= 20.0 => 'Good (A)',
            $cv >= 10.0 => 'Average (B)',
            default     => 'Entry / Non-Crit Focus (C)',
        };
    }

    /**
     * Ekspor seluruh data akun menjadi array komprehensif untuk Gemini / Notebook
     */
    public function exportData(GameAccount $account, array $options = []): array
    {
        $includeCharacters = $options['include_characters'] ?? true;
        $includeWeapons    = $options['include_weapons'] ?? true;
        $includeArtifacts  = $options['include_artifacts'] ?? true;
        $includeMaterials  = $options['include_materials'] ?? true;

        // Ambil data karakter akun
        $invChars = InventoryCharacter::with('character')
            ->where('game_account_id', $account->id)
            ->orderBy('level', 'desc')
            ->get();

        // Ambil seluruh senjata akun
        $invWeapons = InventoryWeapon::with(['weapon', 'equippedCharacter'])
            ->where('game_account_id', $account->id)
            ->orderBy('level', 'desc')
            ->get();

        // Ambil seluruh artefak akun
        $invArtifacts = InventoryArtifact::with(['artifactSet', 'equippedCharacter'])
            ->where('game_account_id', $account->id)
            ->orderBy('level', 'desc')
            ->orderBy('rarity', 'desc')
            ->get();

        // Ambil seluruh material
        $invMaterials = InventoryMaterial::with('material')
            ->where('game_account_id', $account->id)
            ->get();

        // Mapping senjata yang terpasang per character_id
        $weaponByCharId = [];
        foreach ($invWeapons as $iw) {
            if ($iw->equipped_character_id) {
                $weaponByCharId[$iw->equipped_character_id] = $iw;
            }
        }

        // Mapping artefak yang terpasang per character_id
        $artifactsByCharId = [];
        foreach ($invArtifacts as $ia) {
            if ($ia->equipped_character_id) {
                $artifactsByCharId[$ia->equipped_character_id][] = $ia;
            }
        }

        // 1. Susun Roster Karakter Lengkap (Builds terpasang)
        $charactersData = [];
        $totalArtifactsCV = 0.0;
        $activeCharacterCount = 0;

        if ($includeCharacters) {
            foreach ($invChars as $invChar) {
                $char = $invChar->character;
                if (!$char) {
                    continue;
                }

                $activeCharacterCount++;
                $charId = $char->id;

                // Senjata terpasang
                $equippedWeaponData = null;
                if (isset($weaponByCharId[$charId])) {
                    $eqW = $weaponByCharId[$charId];
                    $wModel = $eqW->weapon;
                    $equippedWeaponData = [
                        'name'            => $wModel?->name ?? 'Unknown Weapon',
                        'type'            => $wModel?->type ?? '',
                        'rarity'          => $wModel?->rarity ?? 4,
                        'level'           => (int) $eqW->level,
                        'ascension'       => (int) $eqW->ascension,
                        'refinement'      => (int) max(1, $eqW->refinement),
                        'base_atk'        => $wModel?->base_atk ?? 0,
                        'sub_stat_type'   => $wModel?->sub_stat_type ?? '',
                        'sub_stat_value'  => $wModel?->sub_stat_value ?? '',
                        'passive_name'    => $wModel?->passive_name ?? '',
                        'passive_desc'    => $wModel?->passive_desc ?? '',
                    ];
                }

                // Artefak terpasang (5 slot) & Hitung Set Bonus
                $equippedArtifacts = [];
                $setCount = [];
                $setModels = [];
                $charTotalCV = 0.0;

                if (isset($artifactsByCharId[$charId])) {
                    foreach ($artifactsByCharId[$charId] as $art) {
                        $cv = self::calculateCritValue($art->sub_stats);
                        $charTotalCV += $cv;
                        $setName = $art->artifactSet?->name ?? 'Unknown Set';

                        if ($art->artifactSet) {
                            $setCount[$setName] = ($setCount[$setName] ?? 0) + 1;
                            $setModels[$setName] = $art->artifactSet;
                        }

                        $equippedArtifacts[$art->slot_key] = [
                            'set_name'        => $setName,
                            'slot'            => $art->slot_key,
                            'rarity'          => (int) $art->rarity,
                            'level'           => (int) $art->level,
                            'main_stat'       => $art->main_stat_key,
                            'main_stat_value' => $art->main_stat_value,
                            'sub_stats'       => $art->sub_stats ?? [],
                            'crit_value'      => $cv,
                            'cv_rating'       => self::getCritValueRating($cv),
                            'quality_score'   => $art->score,
                            'quality_rating'  => $art->score_rating,
                        ];
                    }
                }

                // Hitung bonus set artefak yang aktif
                $activeSetBonuses = [];
                foreach ($setCount as $sName => $cnt) {
                    $sModel = $setModels[$sName] ?? null;
                    if ($cnt >= 4) {
                        $activeSetBonuses[] = [
                            'set_name'   => $sName,
                            'pieces'     => 4,
                            'bonus_2pc'  => $sModel?->two_piece_bonus ?? '',
                            'bonus_4pc'  => $sModel?->four_piece_bonus ?? '',
                        ];
                    } elseif ($cnt >= 2) {
                        $activeSetBonuses[] = [
                            'set_name'   => $sName,
                            'pieces'     => 2,
                            'bonus_2pc'  => $sModel?->two_piece_bonus ?? '',
                        ];
                    }
                }

                $totalArtifactsCV += $charTotalCV;

                $charactersData[] = [
                    'name'                => $char->name,
                    'element'             => $char->element,
                    'weapon_type'         => $char->weapon_type,
                    'rarity'              => (int) $char->rarity,
                    'region'              => $char->region,
                    'investment'          => [
                        'level'           => (int) $invChar->level,
                        'max_level'       => $invChar->max_level,
                        'ascension'       => (int) $invChar->ascension,
                        'constellation'   => (int) $invChar->constellation,
                        'talents'         => [
                            'normal_attack'    => (int) $invChar->talent_attack,
                            'elemental_skill'  => (int) $invChar->talent_skill,
                            'elemental_burst'  => (int) $invChar->talent_burst,
                        ],
                    ],
                    'equipped_weapon'     => $equippedWeaponData,
                    'equipped_artifacts'  => $equippedArtifacts,
                    'active_set_bonuses'  => $activeSetBonuses,
                    'total_artifact_cv'   => round($charTotalCV, 1),
                ];
            }
        }

        // 2. Susun Inventori Senjata Lengkap (Termasuk yang di tas)
        $weaponsInventory = [];
        if ($includeWeapons) {
            foreach ($invWeapons as $iw) {
                $w = $iw->weapon;
                $equippedTo = $iw->equippedCharacter?->name;

                $weaponsInventory[] = [
                    'name'           => $w?->name ?? 'Unknown Weapon',
                    'type'           => $w?->type ?? '',
                    'rarity'         => $w?->rarity ?? 4,
                    'level'          => (int) $iw->level,
                    'ascension'      => (int) $iw->ascension,
                    'refinement'     => (int) max(1, $iw->refinement),
                    'base_atk'       => $w?->base_atk ?? 0,
                    'sub_stat_type'  => $w?->sub_stat_type ?? '',
                    'sub_stat_value' => $w?->sub_stat_value ?? '',
                    'passive_name'   => $w?->passive_name ?? '',
                    'passive_desc'   => $w?->passive_desc ?? '',
                    'is_equipped'    => !empty($equippedTo),
                    'equipped_to'    => $equippedTo ?: 'Inventory (Available)',
                ];
            }
        }

        // 3. Susun Inventori Artefak Lengkap
        $artifactsInventory = [];
        $highCvArtifacts = [];
        if ($includeArtifacts) {
            foreach ($invArtifacts as $ia) {
                $cv = self::calculateCritValue($ia->sub_stats);
                $setName = $ia->artifactSet?->name ?? 'Unknown Set';
                $equippedTo = $ia->equippedCharacter?->name;

                $item = [
                    'set_name'        => $setName,
                    'slot'            => $ia->slot_key,
                    'rarity'          => (int) $ia->rarity,
                    'level'           => (int) $ia->level,
                    'main_stat'       => $ia->main_stat_key,
                    'main_stat_value' => $ia->main_stat_value,
                    'sub_stats'       => $ia->sub_stats ?? [],
                    'crit_value'      => $cv,
                    'cv_rating'       => self::getCritValueRating($cv),
                    'quality_score'   => $ia->score,
                    'quality_rating'  => $ia->score_rating,
                    'is_equipped'     => !empty($equippedTo),
                    'equipped_to'     => $equippedTo ?: 'Inventory (Available)',
                ];

                $artifactsInventory[] = $item;

                if ($cv >= 30.0) {
                    $highCvArtifacts[] = [
                        'set'         => $setName,
                        'slot'        => $ia->slot_key,
                        'cv'          => $cv,
                        'main_stat'   => $ia->main_stat_key,
                        'equipped_to' => $equippedTo ?: 'Inventory',
                    ];
                }
            }
        }

        // Sort Top CV artifacts
        usort($highCvArtifacts, fn($a, $b) => $b['cv'] <=> $a['cv']);
        $highCvArtifacts = array_slice($highCvArtifacts, 0, 15);

        // 4. Susun Material Inventory
        $materialsData = [];
        if ($includeMaterials) {
            foreach ($invMaterials as $im) {
                if ($im->material) {
                    $materialsData[] = [
                        'name'   => $im->material->name,
                        'amount' => (int) $im->amount,
                    ];
                }
            }
        }

        // 5. Analisis Ringkasan Statistik
        $elementsCount = [];
        foreach ($charactersData as $c) {
            $el = $c['element'] ?? 'Unknown';
            $elementsCount[$el] = ($elementsCount[$el] ?? 0) + 1;
        }

        $weaponsRarityCount = [5 => 0, 4 => 0, 3 => 0];
        foreach ($weaponsInventory as $w) {
            $r = (int) ($w['rarity'] ?? 4);
            if (isset($weaponsRarityCount[$r])) {
                $weaponsRarityCount[$r]++;
            }
        }

        return [
            'schema_version'     => '1.0.0-gemini-notebook',
            'export_date'        => now()->toIso8601String(),
            'ai_system_role'     => 'Expert Genshin Impact Theorycrafter, Team Builder, and Account Investment Advisor',
            'prompt_instruction'=> 'Analisis data inventori Genshin Impact berikut ini. Evaluasi komposisi tim Spiral Abyss & Imaginarium Theater, kualitas build karakter (synergy senjata & artefak), serta rekomendasi prioritas farming resin (talenta, boss material, dan domain artefak).',
            'account_overview'   => [
                'nickname'             => $account->nickname ?: 'Player',
                'uid'                  => $account->uid ?: (string) $account->id,
                'game'                 => 'Genshin Impact',
                'server'               => $account->region ?: 'Asia',
                'total_characters'     => count($charactersData),
                'total_weapons'        => count($weaponsInventory),
                'total_artifacts'      => count($artifactsInventory),
                'total_materials'      => count($materialsData),
                'total_5star_weapons'  => $weaponsRarityCount[5],
                'element_distribution' => $elementsCount,
                'top_crit_artifacts'   => $highCvArtifacts,
            ],
            'characters'         => $charactersData,
            'weapons_inventory'  => $weaponsInventory,
            'artifacts_inventory'=> $artifactsInventory,
            'materials_inventory'=> $materialsData,
        ];
    }

    /**
     * Konversi data menjadi dokumen Markdown lengkap yang siap dijadikan prompt AI
     */
    public function exportMarkdown(GameAccount $account, array $options = []): string
    {
        $data = $this->exportData($account, $options);
        $overview = $data['account_overview'];

        $md = [];
        $md[] = "# 🎮 Laporan Data Akun Genshin Impact untuk Analisis Gemini AI";
        $md[] = "> Diekspor pada: " . now()->format('d F Y, H:i:s') . " | Format: Gemini AI & Notebook Context Ready";
        $md[] = "";

        // Instruksi Prompt untuk Gemini
        $md[] = "## 🤖 Instruksi Prompt untuk Gemini AI";
        $md[] = "```text";
        $md[] = "Halo Gemini! Bertindaklah sebagai Theorycrafter Genshin Impact profesional dan Data Analyst.";
        $md[] = "Berdasarkan data lengkap inventori akun saya di bawah ini, tolong berikan analisis mendalam:";
        $md[] = "1. [Evaluasi Karakter]: Mana saja karakter saya yang sudah memiliki build optimal dan mana yang masih butuh perbaikan stat/senjata/artefak?";
        $md[] = "2. [Rekomendasi Tim Spiral Abyss]: Buatkan 2 tim terbaik (Floor 12 ready) yang memiliki sinergi reaksi elemen dan rotasi damage tinggi.";
        $md[] = "3. [Optimasi Senjata & Artefak]: Apakah ada senjata atau artefak di inventori saya yang lebih cocok dipindahkan ke karakter tertentu?";
        $md[] = "4. [Prioritas Farming]: Buatkan roadmap farming mingguan untuk talenta, material ascension, dan domain artefak paling efisien.";
        $md[] = "```";
        $md[] = "";

        // Ringkasan Akun
        $md[] = "## 👤 Profil Akun";
        $md[] = "- **Nickname:** {$overview['nickname']}";
        $md[] = "- **UID:** {$overview['uid']}";
        $md[] = "- **Server:** {$overview['server']}";
        $md[] = "- **Total Karakter:** {$overview['total_characters']}";
        $md[] = "- **Total Senjata:** {$overview['total_weapons']} (5★: {$overview['total_5star_weapons']})";
        $md[] = "- **Total Artefak:** {$overview['total_artifacts']}";
        $md[] = "";

        // Daftar Karakter & Build Terpasang
        if (!empty($data['characters'])) {
            $md[] = "## 👥 Roster Karakter & Build Terpasang";
            $md[] = "";

            foreach ($data['characters'] as $c) {
                $inv = $c['investment'];
                $talents = "NA: {$inv['talents']['normal_attack']} | Skill: {$inv['talents']['elemental_skill']} | Burst: {$inv['talents']['elemental_burst']}";
                $w = $c['equipped_weapon'];
                $weaponStr = $w ? "{$w['name']} (Lv. {$w['level']}, R{$w['refinement']})" : "Tidak ada senjata";

                $md[] = "### ✦ {$c['name']} ({$c['element']} - {$c['weapon_type']}) ★{$c['rarity']}";
                $md[] = "- **Level / Konstelasi:** Lv. {$inv['level']}/{$inv['max_level']} | **C{$inv['constellation']}**";
                $md[] = "- **Talenta:** {$talents}";
                $md[] = "- **Senjata Terpasang:** {$weaponStr}";
                if ($w && !empty($w['sub_stat_type'])) {
                    $md[] = "  - *Substat Senjata:* {$w['sub_stat_type']} ({$w['sub_stat_value']}) | Base ATK: {$w['base_atk']}";
                }

                // Set bonus aktif
                if (!empty($c['active_set_bonuses'])) {
                    $setList = array_map(fn($s) => "{$s['set_name']} ({$s['pieces']}-Piece)", $c['active_set_bonuses']);
                    $md[] = "- **Bonus Set Artefak:** " . implode(', ', $setList);
                } else {
                    $md[] = "- **Bonus Set Artefak:** Rainbow / Belum ada set bonus lengkap";
                }

                $md[] = "- **Total Artifact Crit Value (CV):** {$c['total_artifact_cv']} CV";

                // Detail 5 slot artefak
                if (!empty($c['equipped_artifacts'])) {
                    $md[] = "#### Rincian Artefak Terpasang:";
                    $md[] = "| Slot | Set Artefak | Level | Main Stat | Substats | CV | Quality |";
                    $md[] = "| :--- | :--- | :---: | :--- | :--- | :---: | :---: |";

                    foreach ($c['equipped_artifacts'] as $slot => $art) {
                        $subsArr = [];
                        foreach ($art['sub_stats'] as $sb) {
                            $subsArr[] = "{$sb['key']}: {$sb['value']}";
                        }
                        $subsStr = implode(', ', $subsArr) ?: '-';
                        $md[] = "| " . ucfirst($slot) . " | {$art['set_name']} | +{$art['level']} | {$art['main_stat']} ({$art['main_stat_value']}) | {$subsStr} | {$art['crit_value']} | {$art['quality_rating']} |";
                    }
                }
                $md[] = "";
            }
        }

        // Senjata di Inventory (Yang Belum Dipakai)
        $freeWeapons = array_filter($data['weapons_inventory'], fn($w) => !$w['is_equipped']);
        if (!empty($freeWeapons)) {
            $md[] = "## 🗡️ Senjata Cadangan di Inventory (Belum Terpasang)";
            $md[] = "| Nama Senjata | Rarity | Tipe | Level | Refinement | Substat |";
            $md[] = "| :--- | :---: | :--- | :---: | :---: | :--- |";

            foreach (array_slice($freeWeapons, 0, 30) as $fw) {
                $md[] = "| {$fw['name']} | {$fw['rarity']}★ | {$fw['type']} | Lv. {$fw['level']} | R{$fw['refinement']} | {$fw['sub_stat_type']} ({$fw['sub_stat_value']}) |";
            }
            $md[] = "";
        }

        // Top Artefak Berdasarkan Crit Value
        if (!empty($overview['top_crit_artifacts'])) {
            $md[] = "## 💎 Top 15 Artefak dengan Crit Value (CV) Tertinggi";
            $md[] = "| Set Artefak | Slot | Main Stat | CV | Status Pemakai |";
            $md[] = "| :--- | :--- | :--- | :---: | :--- |";

            foreach ($overview['top_crit_artifacts'] as $topArt) {
                $md[] = "| {$topArt['set']} | " . ucfirst($topArt['slot']) . " | {$topArt['main_stat']} | **{$topArt['cv']} CV** | {$topArt['equipped_to']} |";
            }
            $md[] = "";
        }

        return implode("\n", $md);
    }
}
