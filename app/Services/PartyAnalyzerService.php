<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Enemy;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryWeapon;
use Illuminate\Support\Collection;

class PartyAnalyzerService
{
    /**
     * Karakter yang dikenal luas sebagai Shielder
     */
    protected array $shielders = [
        'Zhongli', 'Layla', 'Diona', 'Noelle', 'Thoma', 'Kirara', 'Baizhu', 'Beidou', 'Dehya'
    ];

    /**
     * Karakter yang dikenal luas sebagai Healer / Sustainer
     */
    protected array $healers = [
        'Sangonomiya Kokomi', 'Bennett', 'Barbara', 'Baizhu', 'Kuki Shinobu', 'Jean', 
        'Qiqi', 'Charlotte', 'Xianyun', 'Chevreuse', 'Diona', 'Mika', 'Sayu', 'Yaoyao', 'Dori'
    ];

    /**
     * Karakter yang dikenal luas sebagai Buffer / Crowd Control Anemo
     */
    protected array $buffers = [
        'Kaedehara Kazuha', 'Venti', 'Sucrose', 'Furina', 'Bennett', 'Mona', 
        'Faruzan', 'Kujou Sara', 'Gorou', 'Shenhe', 'Yun Jin', 'Chevreuse', 'Xianyun'
    ];

    /**
     * Daftar senjata dengan utilitas baterai energi tim
     */
    protected array $batteryWeapons = [
        'Favonius Sword', 'Favonius Greatsword', 'Favonius Lance', 'Favonius Warbow', 'Favonius Codex',
        'Sacrificial Sword', 'Sacrificial Greatsword', 'Sacrificial Fragments', 'Sacrificial Bow'
    ];

    /**
     * Hitung skor kecocokan individu karakter melawan musuh tertentu (0 - 100)
     */
    public function scoreCharacterVsEnemy(
        Character $character, 
        Enemy $enemy, 
        ?InventoryCharacter $inv = null, 
        ?array $equipment = null
    ): array {
        $baseScore = 50;
        $pros = [];
        $cons = [];
        $tags = [];
        $isImmune = false;
        $charElement = $character->element;

        // 1. Cek Imunitas Absolut
        $immunities = $enemy->immunities ?? [];
        if (is_array($immunities) && in_array($charElement, $immunities)) {
            $isImmune = true;
            $baseScore -= 60;
            $cons[] = "Musuh KEBAL terhadap elemen {$charElement}! Karakter ini tidak akan menghasilkan damage elemental.";
            $tags[] = 'KEBAL!';
        }

        // 2. Cek Kelemahan Musuh (Weakness / Shield Break Counter)
        $weaknesses = $enemy->weakness_elements ?? [];
        if (is_array($weaknesses) && in_array($charElement, $weaknesses)) {
            $baseScore += 30;
            $pros[] = "Elemen {$charElement} adalah kelemahan langsung musuh atau sangat cepat memecahkan perisainya.";
            $tags[] = 'Counter Elemen';
        }

        // 3. Sinergi Reaksi Elemental Alami Terhadap Elemen Musuh
        $enemyElements = $enemy->elements ?? [];
        if (!empty($enemyElements) && is_array($enemyElements)) {
            if (in_array('Pyro', $enemyElements) && $charElement === 'Hydro') {
                $baseScore += 20;
                $pros[] = "Memanfaatkan aura Pyro musuh untuk memicu reaksi Vaporize (2x Damage).";
                $tags[] = 'Vaporize 2x';
            } elseif (in_array('Hydro', $enemyElements) && $charElement === 'Cryo') {
                $baseScore += 20;
                $pros[] = "Membekukan musuh ber-aura Hydro secara permanen (Freeze).";
                $tags[] = 'Freeze Lock';
            } elseif (in_array('Hydro', $enemyElements) && $charElement === 'Dendro') {
                $baseScore += 20;
                $pros[] = "Memicu reaksi Bloom terus-menerus tanpa membutuhkan Hydro enabler tambahan.";
                $tags[] = 'Bloom Reaction';
            } elseif (in_array('Cryo', $enemyElements) && $charElement === 'Pyro') {
                $baseScore += 20;
                $pros[] = "Memicu reaksi Melt (2x Damage) terhadap perisai/aura Cryo.";
                $tags[] = 'Melt 2x';
            } elseif (in_array('Electro', $enemyElements) && in_array($charElement, ['Pyro', 'Cryo', 'Dendro'])) {
                $baseScore += 15;
                $pros[] = "Bereaksi efektif melawan aura Electro musuh.";
            }
        }

        // 4. Mekanik Senjata & Tipe Serangan
        $mechanics = $enemy->recommended_mechanics ?? [];
        if (is_array($mechanics)) {
            if (in_array('bow', $mechanics)) {
                if ($character->weapon_type === 'Bow') {
                    $baseScore += 20;
                    $pros[] = "Senjata Panah (Bow) efektif menembak weak point inti atau menjangkau musuh yang melayang.";
                    $tags[] = 'Sniper Weakspot';
                } else {
                    $cons[] = "Musuh memiliki mekanisme jarak jauh/lemah terhadap panah; karakter jarak dekat berisiko sulit menjangkau.";
                }
            }

            if (in_array('ranged_dps', $mechanics) && in_array($character->weapon_type, ['Bow', 'Catalyst'])) {
                $baseScore += 15;
                $tags[] = 'Ranged Target';
            }

            if (in_array('claymore', $mechanics) || in_array('shield_breaker', $mechanics)) {
                if ($character->weapon_type === 'Claymore' || $charElement === 'Geo') {
                    $baseScore += 20;
                    $pros[] = "Serangan Heavy Blunt (Claymore/Geo) sangat cepat meremukkan armor keras / perisai Geo.";
                    $tags[] = 'Blunt Breaker';
                }
            }

            if (in_array('crowd_control', $mechanics) && $charElement === 'Anemo') {
                $baseScore += 20;
                $pros[] = "Karakter Anemo mampu mengumpulkan musuh berkerumun (Crowd Control).";
                $tags[] = 'Anemo CC';
            }

            if (in_array('shielder', $mechanics) && in_array($character->name, $this->shielders)) {
                $baseScore += 25;
                $pros[] = "Mampu memberikan perisai kokoh untuk menahan serangan mematikan/memantulkan serangan boss.";
                $tags[] = 'Essential Shield';
            }

            if (in_array('healer', $mechanics) && in_array($character->name, $this->healers)) {
                $baseScore += 20;
                $pros[] = "Menjaga kelangsungan hidup party dari efek korosi dan serangan berkala musuh.";
                $tags[] = 'Sustainer';
            }
        }

        // 5. Bonus Status Inventori Karakter
        if ($inv) {
            $levelBonus = min(15, (int)(($inv->level / 90) * 15));
            $baseScore += $levelBonus;
            if ($inv->level >= 80) {
                $pros[] = "Karakter siap tempur di Lv. {$inv->level}.";
            }
            if ($inv->constellation > 0) {
                $constBonus = min(10, $inv->constellation * 2);
                $baseScore += $constBonus;
                $pros[] = "Memiliki Konstelasi C{$inv->constellation} yang memperkuat performa.";
            }
        }

        // 6. Analisis Pengaruh Senjata & Artefak Terpasang
        if (!empty($equipment) && !empty($equipment['has_equipment'])) {
            $wep = $equipment['weapon'] ?? null;
            $art = $equipment['artifacts'] ?? null;

            // Evaluasi Senjata
            if ($wep && !empty($wep['name'])) {
                if ($wep['level'] >= 80) {
                    $baseScore += 6;
                    $pros[] = "Senjata {$wep['name']} siap di Lv. {$wep['level']} R{$wep['refinement']}.";
                } elseif ($wep['level'] < 60) {
                    $baseScore -= 4;
                    $cons[] = "Senjata {$wep['name']} masih level rendah (Lv. {$wep['level']}).";
                }

                if (!empty($wep['team_buffs'])) {
                    foreach ($wep['team_buffs'] as $buff) {
                        $pros[] = "Utilitas Senjata: {$buff['title']} - {$buff['desc']}";
                        $tags[] = $buff['title'];
                    }
                }
            } else {
                $cons[] = 'Karakter belum dipasangi senjata!';
                $baseScore -= 8;
            }

            // Evaluasi Artefak
            if ($art) {
                if ($art['pieces_count'] === 5) {
                    $baseScore += 5;
                    if ($art['avg_level'] >= 18) {
                        $baseScore += 4;
                        $pros[] = "Set artefak lengkap (5 piece) dengan rata-rata Lv. {$art['avg_level']}.";
                    }
                } elseif ($art['pieces_count'] < 3) {
                    $cons[] = "Artefak belum lengkap (baru {$art['pieces_count']}/5 piece).";
                    $baseScore -= 5;
                }

                if (!empty($art['active_sets'])) {
                    foreach ($art['active_sets'] as $actSet) {
                        $tags[] = $actSet['label'];
                        if (!empty($actSet['team_effect'])) {
                            $pros[] = "Buff Tim Artefak: {$actSet['label']} ({$actSet['team_effect']})";
                        }
                    }
                }
            }
        }

        // Clamp Skor antara 10 - 100
        $finalScore = max(10, min(100, $baseScore));

        $tier = match (true) {
            $finalScore >= 85 => 'S',
            $finalScore >= 70 => 'A',
            $finalScore >= 55 => 'B',
            default           => 'C',
        };

        return [
            'score'       => $finalScore,
            'tier'        => $tier,
            'is_immune'   => $isImmune,
            'tags'        => array_values(array_unique($tags)),
            'pros'        => $pros,
            'cons'        => $cons,
            'character'   => $character,
            'inventory'   => $inv,
            'equipment'   => $equipment,
        ];
    }

    /**
     * Memuat seluruh data perlengkapan (senjata & artefak) untuk akun dalam batch query (mengeliminasi N+1 query)
     */
    public function loadAccountEquipmentData(int $gameAccountId, ?array $characterIds = null): array
    {
        $charsQuery = InventoryCharacter::with('character')->where('game_account_id', $gameAccountId);
        $weapQuery = InventoryWeapon::with('weapon')->where('game_account_id', $gameAccountId)->whereNotNull('equipped_character_id');
        $artQuery = InventoryArtifact::with('artifactSet')->where('game_account_id', $gameAccountId)->whereNotNull('equipped_character_id');

        if ($characterIds !== null && !empty($characterIds)) {
            $charsQuery->whereIn('character_id', $characterIds);
            $weapQuery->whereIn('equipped_character_id', $characterIds);
            $artQuery->whereIn('equipped_character_id', $characterIds);
        }

        return [
            'characters' => $charsQuery->get()->keyBy('character_id'),
            'weapons'    => $weapQuery->get()->keyBy('equipped_character_id'),
            'artifacts'  => $artQuery->get()->groupBy('equipped_character_id'),
        ];
    }

    /**
     * Helper analisis perlengkapan menggunakan data yang sudah di-preload (0 database query)
     */
    public function analyzeMemberEquipmentPreloaded(
        Character $character, 
        ?int $gameAccountId, 
        ?InventoryWeapon $invWeapon = null, 
        $invArtifacts = null, 
        ?InventoryCharacter $invChar = null
    ): array {
        return $this->analyzeMemberEquipment($character, $gameAccountId, true, $invWeapon, $invArtifacts, $invChar);
    }

    /**
     * Analisis perlengkapan individual (Senjata & Artefak) dari satu karakter
     */
    public function analyzeMemberEquipment(
        Character $character, 
        ?int $gameAccountId,
        bool $usePreloaded = false,
        ?InventoryWeapon $preloadedWeapon = null,
        $preloadedArtifacts = null,
        ?InventoryCharacter $preloadedChar = null
    ): array
    {
        if (!$gameAccountId) {
            return [
                'has_equipment'    => false,
                'weapon'           => null,
                'artifacts'        => null,
                'combat_readiness' => 50,
                'readiness_tier'   => 'B',
            ];
        }

        // 1. Ambil Senjata yang Sedang Dipasang
        $invWeapon = $usePreloaded 
            ? $preloadedWeapon 
            : InventoryWeapon::with('weapon')
                ->where('game_account_id', $gameAccountId)
                ->where('equipped_character_id', $character->id)
                ->first();

        $weaponData = null;
        $wepReadiness = 0;

        if ($invWeapon && $invWeapon->weapon) {
            $w = $invWeapon->weapon;
            $wepReadiness = (int)min(100, round(($invWeapon->level / 90) * 80) + ($invWeapon->refinement * 4));

            $weaponBuffs = [];
            $wName = $w->name;

            // Deteksi Favonius series (Battery)
            if (str_contains($wName, 'Favonius')) {
                $weaponBuffs[] = [
                    'type'  => 'battery',
                    'title' => 'Favonius Battery',
                    'desc'  => 'Menghasilkan partikel netral saat CRIT untuk mengisi energi Burst seluruh tim.',
                ];
            }

            // Deteksi Sacrificial series
            if (str_contains($wName, 'Sacrificial')) {
                $weaponBuffs[] = [
                    'type'  => 'reset',
                    'title' => 'Sacrificial Reset',
                    'desc'  => 'Peluang mereset cooldown Elemental Skill untuk partikel energi ganda.',
                ];
            }

            // TTDS
            if ($wName === 'Thrilling Tales of Dragon Slayers') {
                $atkPct = match ($invWeapon->refinement) {
                    1 => 24,
                    2 => 30,
                    3 => 36,
                    4 => 42,
                    default => 48,
                };
                $weaponBuffs[] = [
                    'type'  => 'atk',
                    'value' => $atkPct,
                    'title' => "TTDS +{$atkPct}% ATK",
                    'desc'  => "Memberikan +{$atkPct}% Base ATK untuk karakter berikutnya yang bertukar posisi.",
                ];
            }

            // Elegy for the End
            if ($wName === 'Elegy for the End') {
                $weaponBuffs[] = [
                    'type'     => 'team_buff',
                    'title'    => 'Elegy for the End',
                    'em_value' => 100,
                    'atk_value'=> 20,
                    'desc'     => 'Memberikan +100 Elemental Mastery dan +20% ATK untuk seluruh anggota party.',
                ];
            }

            // Freedom-Sworn
            if ($wName === 'Freedom-Sworn') {
                $weaponBuffs[] = [
                    'type'      => 'team_buff',
                    'title'     => 'Freedom-Sworn',
                    'atk_value' => 20,
                    'dmg_value' => 16,
                    'desc'      => 'Memberikan +20% ATK dan +16% Normal/Charged/Plunging Attack DMG untuk seluruh tim.',
                ];
            }

            // Song of Broken Pines
            if ($wName === 'Song of Broken Pines') {
                $weaponBuffs[] = [
                    'type'      => 'team_buff',
                    'title'     => 'Song of Broken Pines',
                    'atk_value' => 20,
                    'desc'      => 'Memberikan +20% ATK dan +12% ATK SPD untuk seluruh anggota tim.',
                ];
            }

            // Hakushin Ring
            if ($wName === 'Hakushin Ring') {
                $weaponBuffs[] = [
                    'type'  => 'team_buff',
                    'title' => 'Hakushin Ring',
                    'desc'  => 'Memberikan Elemental DMG Bonus elemen terkait bagi anggota party saat memicu reaksi Electro.',
                ];
            }

            // Xiphos' Moonlight
            if (str_contains($wName, 'Xiphos')) {
                $weaponBuffs[] = [
                    'type'  => 'battery',
                    'title' => "Xiphos' Moonlight",
                    'desc'  => 'Mentransfer bonus Energy Recharge ke seluruh anggota party berdasarkan EM pemegang.',
                ];
            }

            // Key of Khaj-Nisut
            if ($wName === 'Key of Khaj-Nisut') {
                $weaponBuffs[] = [
                    'type'     => 'em',
                    'title'    => 'Key of Khaj-Nisut',
                    'em_value' => 120,
                    'desc'     => 'Mentransfer Elemental Mastery ke seluruh tim berdasarkan Max HP pemegang.',
                ];
            }

            $weaponData = [
                'id'              => $invWeapon->id,
                'name'            => $w->name,
                'icon_url'        => $w->icon_url,
                'rarity'          => $w->rarity,
                'type'            => $w->type,
                'level'           => $invWeapon->level,
                'refinement'      => $invWeapon->refinement,
                'base_atk'        => $w->base_atk,
                'sub_stat_type'   => $w->sub_stat_type,
                'sub_stat_value'  => $w->sub_stat_value,
                'passive_name'    => $w->passive_name,
                'passive_desc'    => $w->passive_desc,
                'readiness_score' => $wepReadiness,
                'team_buffs'      => $weaponBuffs,
            ];
        }

        // 2. Ambil Artefak yang Sedang Dipasang
        $invArtifacts = $usePreloaded 
            ? ($preloadedArtifacts ?? collect()) 
            : InventoryArtifact::with('artifactSet')
                ->where('game_account_id', $gameAccountId)
                ->where('equipped_character_id', $character->id)
                ->get();

        $piecesCount = $invArtifacts->count();
        $avgLevel = $piecesCount > 0 ? round($invArtifacts->avg('level'), 1) : 0;
        $avgScore = $piecesCount > 0 ? round($invArtifacts->avg('score'), 1) : 0;

        // Hitung Set Bonus Aktif
        $setCounts = [];
        $setModels = [];
        foreach ($invArtifacts as $art) {
            if ($art->artifact_set_id && $art->artifactSet) {
                $sid = $art->artifact_set_id;
                $setCounts[$sid] = ($setCounts[$sid] ?? 0) + 1;
                $setModels[$sid] = $art->artifactSet;
            }
        }

        $activeSets = [];
        $has4pc = false;
        $has2pc = false;

        foreach ($setCounts as $sid => $cnt) {
            $set = $setModels[$sid];
            $setName = $set->name;

            if ($cnt >= 4) {
                $has4pc = true;
                $teamEffect = null;
                $buffType = null;
                $buffVal = 0;

                // Cek set dengan pengaruh tim luas
                if (str_contains($setName, 'Viridescent Venerer')) {
                    $teamEffect = '-40% Elemental RES Shred musuh dari Swirl';
                    $buffType = 'res_shred';
                    $buffVal = 40;
                } elseif (str_contains($setName, 'Noblesse Oblige')) {
                    $teamEffect = '+20% ATK untuk seluruh tim setelah Elemental Burst';
                    $buffType = 'atk';
                    $buffVal = 20;
                } elseif (str_contains($setName, 'Tenacity of the Millelith')) {
                    $teamEffect = '+20% ATK dan +30% Shield Strength saat Elemental Skill mengenai musuh';
                    $buffType = 'atk';
                    $buffVal = 20;
                } elseif (str_contains($setName, 'Deepwood Memories')) {
                    $teamEffect = '-30% Dendro RES Shred musuh setelah Skill/Burst';
                    $buffType = 'res_shred';
                    $buffVal = 30;
                } elseif (str_contains($setName, 'Instructor')) {
                    $teamEffect = '+120 Elemental Mastery tim setelah memicu reaksi elemen';
                    $buffType = 'em';
                    $buffVal = 120;
                } elseif (str_contains($setName, 'Scroll of the Hero of Cinder City')) {
                    $teamEffect = '+12% s/d +40% Elemental DMG Bonus untuk elemen terkait dalam tim';
                    $buffType = 'dmg_bonus';
                    $buffVal = 40;
                } elseif (str_contains($setName, 'Song of Days Past')) {
                    $teamEffect = 'Mengakumulasi healing untuk bonus Flat DMG serangan karakter aktif';
                    $buffType = 'flat_dmg';
                    $buffVal = 0;
                }

                $activeSets[] = [
                    'set_id'      => $sid,
                    'name'        => $setName,
                    'pieces'      => 4,
                    'label'       => "4-pc {$setName}",
                    'bonus_text'  => $set->four_piece_bonus,
                    'team_effect' => $teamEffect,
                    'buff_type'   => $buffType,
                    'buff_value'  => $buffVal,
                    'icon_url'    => $set->icon_url,
                ];
            } elseif ($cnt >= 2) {
                $has2pc = true;
                $activeSets[] = [
                    'set_id'      => $sid,
                    'name'        => $setName,
                    'pieces'      => 2,
                    'label'       => "2-pc {$setName}",
                    'bonus_text'  => $set->two_piece_bonus,
                    'team_effect' => null,
                    'buff_type'   => null,
                    'buff_value'  => 0,
                    'icon_url'    => $set->icon_url,
                ];
            }
        }

        $artReadiness = 0;
        if ($piecesCount > 0) {
            $pieceFactor = ($piecesCount / 5) * 45;
            $levelFactor = min(1, ($avgLevel / 20)) * 40;
            $setBonusFactor = $has4pc ? 15 : ($has2pc ? 8 : 0);
            $artReadiness = (int)min(100, round($pieceFactor + $levelFactor + $setBonusFactor));
        }

        // Slot pieces breakdown
        $piecesMap = [];
        foreach ($invArtifacts as $art) {
            $piecesMap[$art->slot_key] = [
                'id'              => $art->id,
                'slot_key'        => $art->slot_key,
                'slot_label'      => $art->slot_label,
                'set_name'        => $art->artifactSet->name ?? 'Unknown',
                'level'           => $art->level,
                'rarity'          => $art->rarity,
                'main_stat_label' => $art->main_stat_label,
                'main_stat_value' => $art->main_stat_value,
                'score'           => $art->score,
                'score_rating'    => $art->score_rating,
                'icon_url'        => $art->icon_url,
            ];
        }

        $artifactData = [
            'pieces_count'    => $piecesCount,
            'avg_level'       => $avgLevel,
            'avg_score'       => $avgScore,
            'active_sets'     => $activeSets,
            'pieces'          => $piecesMap,
            'readiness_score' => $artReadiness,
        ];

        // Overall Character Combat Readiness
        $charBaseReadiness = 50;
        $invChar = $usePreloaded 
            ? $preloadedChar 
            : InventoryCharacter::where('game_account_id', $gameAccountId)
                ->where('character_id', $character->id)
                ->first();
        if ($invChar) {
            $charBaseReadiness = (int)min(100, round(($invChar->level / 90) * 80) + ($invChar->constellation * 3));
        }

        $overallReadiness = (int)round(($charBaseReadiness * 0.3) + ($wepReadiness * 0.35) + ($artReadiness * 0.35));
        $overallTier = match (true) {
            $overallReadiness >= 85 => 'S',
            $overallReadiness >= 70 => 'A',
            $overallReadiness >= 55 => 'B',
            default                 => 'C',
        };

        return [
            'has_equipment'    => true,
            'weapon'           => $weaponData,
            'artifacts'        => $artifactData,
            'combat_readiness' => $overallReadiness,
            'readiness_tier'   => $overallTier,
        ];
    }

    /**
     * Analisis sinergi perlengkapan (senjata & artefak) gabungan seluruh tim
     */
    public function analyzeTeamEquipment(array $membersEquipment, array $elementCounts, array $characters): array
    {
        $hasAccount = false;
        $totalReadiness = 0;
        $memberCount = count($membersEquipment);
        $teamBuffs = [];
        $equipmentWarnings = [];
        $equipmentAdvantages = [];

        $noblesseCount = 0;
        $tenacityCount = 0;
        $deepwoodCount = 0;
        $vvCount = 0;
        $instructorCount = 0;
        $cinderCount = 0;
        $batteryCount = 0;
        $ttdsCount = 0;
        $elegyCount = 0;
        $freedomCount = 0;

        $totalAtkBonus = 0;
        $totalResShred = 0;
        $totalEmShare = 0;
        $totalDmgBonus = 0;

        foreach ($membersEquipment as $idx => $eq) {
            if (!empty($eq['has_equipment'])) {
                $hasAccount = true;
                $totalReadiness += $eq['combat_readiness'];

                $char = $characters[$idx] ?? null;
                $charName = $char ? $char->name : ('Karakter ' . ($idx + 1));

                // 1. Cek Senjata
                $wep = $eq['weapon'];
                if (!$wep) {
                    $equipmentWarnings[] = "{$charName} belum dipasangi senjata!";
                } else {
                    if ($wep['level'] < 70) {
                        $equipmentWarnings[] = "Senjata {$wep['name']} ({$charName}) masih Lv. {$wep['level']}; disarankan minimal Lv. 80.";
                    }

                    if (!empty($wep['team_buffs'])) {
                        foreach ($wep['team_buffs'] as $wb) {
                            if ($wb['type'] === 'battery') {
                                $batteryCount++;
                            } elseif ($wb['type'] === 'atk') {
                                $ttdsCount++;
                                $totalAtkBonus += $wb['value'] ?? 48;
                            } elseif ($wb['title'] === 'Elegy for the End') {
                                $elegyCount++;
                                $totalAtkBonus += 20;
                                $totalEmShare += 100;
                            } elseif ($wb['title'] === 'Freedom-Sworn') {
                                $freedomCount++;
                                $totalAtkBonus += 20;
                                $totalDmgBonus += 16;
                            }
                            $teamBuffs[] = [
                                'source' => "{$charName} ({$wep['name']})",
                                'title'  => $wb['title'],
                                'desc'   => $wb['desc'],
                                'icon'   => $wep['icon_url'],
                            ];
                        }
                    }
                }

                // 2. Cek Artefak
                $art = $eq['artifacts'];
                if (!$art || $art['pieces_count'] === 0) {
                    $equipmentWarnings[] = "{$charName} belum memiliki artefak terpasang!";
                } elseif ($art['pieces_count'] < 5) {
                    $equipmentWarnings[] = "{$charName} baru memakai {$art['pieces_count']}/5 piece artefak.";
                }

                if ($art && !empty($art['active_sets'])) {
                    foreach ($art['active_sets'] as $act) {
                        if ($act['pieces'] === 4) {
                            $setName = $act['name'];
                            if (str_contains($setName, 'Noblesse Oblige')) $noblesseCount++;
                            if (str_contains($setName, 'Tenacity of the Millelith')) $tenacityCount++;
                            if (str_contains($setName, 'Deepwood Memories')) $deepwoodCount++;
                            if (str_contains($setName, 'Viridescent Venerer')) $vvCount++;
                            if (str_contains($setName, 'Instructor')) $instructorCount++;
                            if (str_contains($setName, 'Scroll of the Hero of Cinder City')) $cinderCount++;

                            if (!empty($act['team_effect'])) {
                                $teamBuffs[] = [
                                    'source' => "{$charName} ({$act['label']})",
                                    'title'  => $act['label'],
                                    'desc'   => $act['team_effect'],
                                    'icon'   => $act['icon_url'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Peringatan Duplikasi Buff Artefak (Does Not Stack)
        if ($noblesseCount > 1) {
            $equipmentWarnings[] = "Peringatan Duplikasi: {$noblesseCount} karakter memakai 4-pc Noblesse Oblige! Buff +20% ATK tidak bertumpuk.";
        }
        if ($tenacityCount > 1) {
            $equipmentWarnings[] = "Peringatan Duplikasi: {$tenacityCount} karakter memakai 4-pc Tenacity of the Millelith! Buff ATK tidak bertumpuk.";
        }
        if ($deepwoodCount > 1) {
            $equipmentWarnings[] = "Peringatan Duplikasi: {$deepwoodCount} karakter memakai 4-pc Deepwood Memories! Pengurangan Dendro RES -30% tidak bertumpuk.";
        }

        // Akumulasi Buff Pool Tim
        if ($noblesseCount >= 1) $totalAtkBonus += 20;
        if ($tenacityCount >= 1) $totalAtkBonus += 20;
        if ($vvCount >= 1) $totalResShred += 40;
        if ($deepwoodCount >= 1) $totalResShred += 30;
        if ($instructorCount >= 1) $totalEmShare += 120;
        if ($cinderCount >= 1) $totalDmgBonus += 40;

        // Tambahkan Elemen Resonance ke Buff Pool
        if (($elementCounts['Pyro'] ?? 0) >= 2) $totalAtkBonus += 25;
        if (($elementCounts['Dendro'] ?? 0) >= 2) $totalEmShare += 50;
        if (($elementCounts['Geo'] ?? 0) >= 2) $totalDmgBonus += 15;

        // Keunggulan Perlengkapan
        if ($batteryCount >= 1) {
            $equipmentAdvantages[] = "Dukungan Energi (Battery): {$batteryCount} senjata Favonius/Sacrificial menjaga uptime Burst tim konsisten.";
        }
        if ($vvCount >= 1) {
            $equipmentAdvantages[] = 'Resist Shred: 4-pc Viridescent Venerer siap menurunkan -40% Elemental RES musuh.';
        }
        if ($noblesseCount === 1) {
            $equipmentAdvantages[] = 'Buffer Tim: 4-pc Noblesse Oblige aktif memberikan +20% ATK untuk seluruh tim.';
        }
        if ($tenacityCount === 1) {
            $equipmentAdvantages[] = 'Buffer Tenacity: 4-pc Tenacity aktif memperkuat +20% ATK & +30% Shield Strength tim.';
        }
        if ($ttdsCount >= 1) {
            $equipmentAdvantages[] = 'Nuke Buffer: Thrilling Tales siap melipatgandakan ATK Main DPS saat rotasi swap.';
        }

        $avgReadiness = $memberCount > 0 ? (int)round($totalReadiness / $memberCount) : 0;
        $readinessTier = match (true) {
            $avgReadiness >= 85 => 'S',
            $avgReadiness >= 70 => 'A',
            $avgReadiness >= 55 => 'B',
            default             => 'C',
        };

        return [
            'has_account_data'      => $hasAccount,
            'team_readiness'        => $avgReadiness,
            'team_readiness_tier'   => $readinessTier,
            'team_buffs'            => $teamBuffs,
            'equipment_warnings'    => $equipmentWarnings,
            'equipment_advantages'  => $equipmentAdvantages,
            'buff_pool'             => [
                'atk_bonus_percent' => $totalAtkBonus,
                'res_shred_percent' => $totalResShred,
                'em_share'          => $totalEmShare,
                'dmg_bonus_percent' => $totalDmgBonus,
                'battery_count'     => $batteryCount,
            ],
        ];
    }

    /**
     * Analisis sinergi lengkap dari 4 karakter dalam satu party melawan musuh
     */
    public function analyzePartyVsEnemy(
        array $characterIds, 
        Enemy $enemy, 
        ?int $gameAccountId = null,
        ?array $preloadedEquipment = null
    ): array
    {
        $characterIds = array_values(array_filter(array_slice($characterIds, 0, 4)));
        if (empty($characterIds)) {
            return [
                'team_score'         => 0,
                'team_tier'          => 'C',
                'members'            => [],
                'resonances'         => [],
                'warnings'           => ['Pilih minimal 1 karakter untuk memulai analisis tim.'],
                'advantages'         => [],
                'equipment_analysis' => [
                    'has_account_data'     => (bool)$gameAccountId,
                    'team_readiness'       => 0,
                    'team_readiness_tier'  => 'C',
                    'team_buffs'           => [],
                    'equipment_warnings'   => [],
                    'equipment_advantages' => [],
                    'buff_pool'            => [
                        'atk_bonus_percent' => 0,
                        'res_shred_percent' => 0,
                        'em_share'          => 0,
                        'dmg_bonus_percent' => 0,
                        'battery_count'     => 0,
                    ],
                ],
                'role_coverage'      => [
                    'has_bow'      => false,
                    'has_blunt'    => false,
                    'has_shielder' => false,
                    'has_healer'   => false,
                    'has_cc'       => false,
                ],
            ];
        }

        $characters = Character::whereIn('id', $characterIds)->get()->keyBy('id');
        $preloaded = $preloadedEquipment;
        if ($gameAccountId && $preloaded === null) {
            $preloaded = $this->loadAccountEquipmentData($gameAccountId, $characterIds);
        }

        $invMap = $preloaded['characters'] ?? collect();
        $weaponsMap = $preloaded['weapons'] ?? collect();
        $artifactsMap = $preloaded['artifacts'] ?? collect();

        $members = [];
        $membersEquipment = [];
        $charList = [];
        $elementCounts = [];
        $totalMemberScore = 0;
        $hasBow = false;
        $hasClaymoreOrGeo = false;
        $hasShielder = false;
        $hasHealer = false;
        $hasAnemo = false;
        $immuneMembers = [];

        foreach ($characterIds as $id) {
            if (!$characters->has($id)) continue;
            $char = $characters->get($id);
            $inv = $invMap->get($id);

            // Analisis Equipment Karakter
            $equipAnalysis = $gameAccountId 
                ? $this->analyzeMemberEquipmentPreloaded(
                    $char, 
                    $gameAccountId, 
                    $weaponsMap->get($id), 
                    $artifactsMap->get($id, collect()), 
                    $inv
                ) 
                : $this->analyzeMemberEquipment($char, null);
            $membersEquipment[] = $equipAnalysis;
            $charList[] = $char;

            // Analisis Skor Karakter vs Musuh
            $analysis = $this->scoreCharacterVsEnemy($char, $enemy, $inv, $equipAnalysis);
            $members[] = $analysis;
            $totalMemberScore += $analysis['score'];

            $el = $char->element;
            $elementCounts[$el] = ($elementCounts[$el] ?? 0) + 1;

            if ($char->weapon_type === 'Bow') $hasBow = true;
            if ($char->weapon_type === 'Claymore' || $el === 'Geo') $hasClaymoreOrGeo = true;
            if (in_array($char->name, $this->shielders)) $hasShielder = true;
            if (in_array($char->name, $this->healers)) $hasHealer = true;
            if ($el === 'Anemo') $hasAnemo = true;

            if ($analysis['is_immune']) {
                $immuneMembers[] = $char->name;
            }
        }

        $memberCount = count($members);
        $avgScore = $memberCount > 0 ? (int)round($totalMemberScore / $memberCount) : 0;

        // ─── Elemental Resonance ───────────────────────────────────────────
        $resonances = [];
        $bonusScore = 0;

        foreach ($elementCounts as $el => $cnt) {
            if ($cnt >= 2) {
                switch ($el) {
                    case 'Pyro':
                        $resonances[] = ['name' => 'Fervent Flames', 'element' => 'Pyro', 'desc' => 'Meningkatkan Base ATK sebesar +25%.'];
                        $bonusScore += 5;
                        break;
                    case 'Hydro':
                        $resonances[] = ['name' => 'Soothing Water', 'element' => 'Hydro', 'desc' => 'Meningkatkan Max HP seluruh party sebesar +25%.'];
                        $bonusScore += 5;
                        break;
                    case 'Cryo':
                        $resonances[] = ['name' => 'Shattering Ice', 'element' => 'Cryo', 'desc' => 'Meningkatkan CRIT Rate +15% saat menyerang musuh Frozen/Cryo.'];
                        $bonusScore += 5;
                        break;
                    case 'Geo':
                        $resonances[] = ['name' => 'Enduring Rock', 'element' => 'Geo', 'desc' => 'Meningkatkan Shield Strength +15% dan Damage karakter berpelindung +15%.'];
                        $bonusScore += 5;
                        break;
                    case 'Dendro':
                        $resonances[] = ['name' => 'Sprawling Greenery', 'element' => 'Dendro', 'desc' => 'Meningkatkan Elemental Mastery sebesar +50 hingga +100 poin.'];
                        $bonusScore += 5;
                        break;
                    case 'Anemo':
                        $resonances[] = ['name' => 'Impetuous Winds', 'element' => 'Anemo', 'desc' => 'Mengurangi konsumsi stamina 15%, meningkatkan Movement Speed 10%, CD -5%.'];
                        $bonusScore += 3;
                        break;
                    case 'Electro':
                        $resonances[] = ['name' => 'High Voltage', 'element' => 'Electro', 'desc' => 'Memulihkan partikel energi saat memicu reaksi Electro.'];
                        $bonusScore += 3;
                        break;
                }
            }
        }

        if (count($elementCounts) === 4) {
            $resonances[] = ['name' => 'Protective Canopy', 'element' => 'Multi', 'desc' => 'Meningkatkan seluruh Elemental RES & Physical RES sebesar +15%.'];
            $bonusScore += 4;
        }

        // ─── Analisis Sinergi Equipment Tim (Senjata & Artefak) ────────────
        $teamEquipAnalysis = $this->analyzeTeamEquipment($membersEquipment, $elementCounts, $charList);

        // ─── Warnings & Advantages Check ──────────────────────────────────
        $warnings = [];
        $advantages = [];
        $mechanics = $enemy->recommended_mechanics ?? [];

        if (!empty($immuneMembers)) {
            $names = implode(', ', $immuneMembers);
            $warnings[] = "Peringatan Imunitas: Karakter ({$names}) tidak efektif karena musuh kebal terhadap elemen tersebut!";
            $bonusScore -= 15;
        }

        if (in_array('shielder', $mechanics)) {
            if ($hasShielder) {
                $advantages[] = "Perisai (Shield) terpenuhi! Anda dapat menahan serangan masif musuh dengan aman.";
                $bonusScore += 8;
            } else {
                $warnings[] = "Musuh merekomendasikan Shielder. Tanpa perisai, risiko karakter tumbang terkena serangan one-shot sangat tinggi!";
                $bonusScore -= 10;
            }
        }

        if (in_array('bow', $mechanics)) {
            if ($hasBow) {
                $advantages[] = "Karakter Panah (Bow) tersedia untuk melumpuhkan titik lemah / musuh melayang.";
                $bonusScore += 6;
            } else {
                $warnings[] = "Musuh memiliki mekanisme weakspot/terbang; pertimbangkan membawa 1 karakter Panah.";
            }
        }

        if (in_array('claymore', $mechanics) || in_array('shield_breaker', $mechanics)) {
            if ($hasClaymoreOrGeo) {
                $advantages[] = "Mekanik penghancur perisai keras (Claymore/Geo) terpenuhi.";
                $bonusScore += 6;
            } else {
                $warnings[] = "Musuh memiliki perisai batu/armor tebal yang lambat ditembus tanpa Claymore atau Geo.";
            }
        }

        if (in_array('crowd_control', $mechanics) && $hasAnemo) {
            $advantages[] = "Karakter Anemo tersedia untuk memusatkan kerumunan musuh.";
            $bonusScore += 5;
        }

        // Role balance checks
        $hasSustain = $hasShielder || $hasHealer;
        if (!$hasSustain && $memberCount >= 3) {
            $warnings[] = "Party tidak memiliki Healer maupun Shielder. Ketahanan bertarung jangka panjang rentan.";
            $bonusScore -= 8;
        } elseif ($hasSustain) {
            $advantages[] = "Sustainer (Healer/Shielder) tersedia untuk menjaga keamanan tim.";
        }

        // Gabungkan keuntungan dan peringatan equipment ke daftar utama
        foreach ($teamEquipAnalysis['equipment_advantages'] as $eqAdv) {
            $advantages[] = $eqAdv;
        }
        foreach ($teamEquipAnalysis['equipment_warnings'] as $eqWarn) {
            $warnings[] = $eqWarn;
        }

        // Penyesuaian Skor dengan Kesiapan Equipment jika Akun Dipilih
        if ($teamEquipAnalysis['has_account_data']) {
            $readiness = $teamEquipAnalysis['team_readiness'];
            if ($readiness >= 85) {
                $bonusScore += 8;
            } elseif ($readiness < 50) {
                $bonusScore -= 8;
            }
        }

        $teamScore = max(10, min(100, $avgScore + $bonusScore));
        $teamTier = match (true) {
            $teamScore >= 85 => 'S',
            $teamScore >= 70 => 'A',
            $teamScore >= 55 => 'B',
            default          => 'C',
        };

        return [
            'team_score'         => $teamScore,
            'team_tier'          => $teamTier,
            'members'            => $members,
            'resonances'         => $resonances,
            'warnings'           => $warnings,
            'advantages'         => $advantages,
            'equipment_analysis' => $teamEquipAnalysis,
            'role_coverage'      => [
                'has_bow'       => $hasBow,
                'has_blunt'     => $hasClaymoreOrGeo,
                'has_shielder'  => $hasShielder,
                'has_healer'    => $hasHealer,
                'has_cc'        => $hasAnemo,
            ],
        ];
    }

    /**
     * Otomatis susun rekomendasi 4 karakter terbaik untuk melawan musuh ini
     */
    public function autoGenerateParty(
        Enemy $enemy, 
        ?int $gameAccountId = null,
        ?Collection $precomputedScored = null
    ): array
    {
        if ($precomputedScored !== null && $precomputedScored->isNotEmpty()) {
            $scored = $precomputedScored;
        } elseif ($gameAccountId) {
            $preloaded = $this->loadAccountEquipmentData($gameAccountId);
            $invChars = $preloaded['characters'];
            
            $scored = $invChars->map(function ($inv) use ($enemy, $gameAccountId, $preloaded) {
                $equip = $this->analyzeMemberEquipmentPreloaded(
                    $inv->character, 
                    $gameAccountId, 
                    $preloaded['weapons']->get($inv->character_id), 
                    $preloaded['artifacts']->get($inv->character_id, collect()), 
                    $inv
                );
                return $this->scoreCharacterVsEnemy($inv->character, $enemy, $inv, $equip);
            });
        } else {
            $chars = Character::where('is_active', true)->get();
            $scored = $chars->map(function ($c) use ($enemy) {
                return $this->scoreCharacterVsEnemy($c, $enemy, null, null);
            });
        }

        $validCandidates = $scored->filter(fn ($item) => !$item['is_immune'])->sortByDesc('score');

        if ($validCandidates->isEmpty()) {
            return [];
        }

        $picked = [];
        $pickedIds = [];

        // 1. Pilih Main DPS (Skor tertinggi)
        $mainDps = $validCandidates->first();
        if ($mainDps) {
            $picked[] = $mainDps['character']->id;
            $pickedIds[] = $mainDps['character']->id;
        }

        // 2. Pilih Sustainer (Shielder atau Healer)
        $mechanics = $enemy->recommended_mechanics ?? [];
        $needShielder = is_array($mechanics) && in_array('shielder', $mechanics);

        $sustainer = $validCandidates
            ->whereNotIn('character.id', $pickedIds)
            ->filter(function ($item) use ($needShielder) {
                $name = $item['character']->name;
                if ($needShielder) {
                    return in_array($name, $this->shielders);
                }
                return in_array($name, $this->healers) || in_array($name, $this->shielders);
            })
            ->first();

        if ($sustainer) {
            $picked[] = $sustainer['character']->id;
            $pickedIds[] = $sustainer['character']->id;
        }

        // 3. Jika musuh butuh Bow, pastikan ada Bow user
        if (is_array($mechanics) && in_array('bow', $mechanics)) {
            $hasBow = collect($picked)->contains(function ($id) use ($validCandidates) {
                $c = $validCandidates->firstWhere('character.id', $id);
                return $c && $c['character']->weapon_type === 'Bow';
            });

            if (!$hasBow) {
                $bowUser = $validCandidates
                    ->whereNotIn('character.id', $pickedIds)
                    ->firstWhere('character.weapon_type', 'Bow');
                if ($bowUser && count($picked) < 4) {
                    $picked[] = $bowUser['character']->id;
                    $pickedIds[] = $bowUser['character']->id;
                }
            }
        }

        // 4. Pilih Support / Enabler / Sinergi Reaksi
        $remaining = $validCandidates->whereNotIn('character.id', $pickedIds);
        foreach ($remaining as $item) {
            if (count($picked) >= 4) break;
            $picked[] = $item['character']->id;
            $pickedIds[] = $item['character']->id;
        }

        return $picked;
    }
}
