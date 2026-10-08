<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Enemy;
use App\Models\GameAccount;
use App\Models\InventoryCharacter;
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
     * Hitung skor kecocokan individu karakter melawan musuh tertentu (0 - 100)
     */
    public function scoreCharacterVsEnemy(Character $character, Enemy $enemy, ?InventoryCharacter $inv = null): array
    {
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
            // Butuh Panah (Bow) untuk musuh terbang / weakspot mata
            if (in_array('bow', $mechanics)) {
                if ($character->weapon_type === 'Bow') {
                    $baseScore += 20;
                    $pros[] = "Senjata Panah (Bow) efektif menembak weak point inti atau menjangkau musuh yang melayang.";
                    $tags[] = 'Sniper Weakspot';
                } else {
                    $cons[] = "Musuh memiliki mekanisme jarak jauh/lemah terhadap panah; karakter jarak dekat berisiko sulit menjangkau.";
                }
            }

            // Butuh Catalyst / Serangan Jarak Jauh
            if (in_array('ranged_dps', $mechanics) && in_array($character->weapon_type, ['Bow', 'Catalyst'])) {
                $baseScore += 15;
                $tags[] = 'Ranged Target';
            }

            // Butuh Claymore / Blunt / Shield Breaker batu
            if (in_array('claymore', $mechanics) || in_array('shield_breaker', $mechanics)) {
                if ($character->weapon_type === 'Claymore' || $charElement === 'Geo') {
                    $baseScore += 20;
                    $pros[] = "Serangan Heavy Blunt (Claymore/Geo) sangat cepat meremukkan armor keras / perisai Geo.";
                    $tags[] = 'Blunt Breaker';
                }
            }

            // Butuh Crowd Control (Anemo)
            if (in_array('crowd_control', $mechanics) && $charElement === 'Anemo') {
                $baseScore += 20;
                $pros[] = "Karakter Anemo mampu mengumpulkan musuh berkerumun (Crowd Control).";
                $tags[] = 'Anemo CC';
            }

            // Butuh Shielder
            if (in_array('shielder', $mechanics) && in_array($character->name, $this->shielders)) {
                $baseScore += 25;
                $pros[] = "Mampu memberikan perisai kokoh untuk menahan serangan mematikan/memantulkan serangan boss.";
                $tags[] = 'Essential Shield';
            }

            // Butuh Healer
            if (in_array('healer', $mechanics) && in_array($character->name, $this->healers)) {
                $baseScore += 20;
                $pros[] = "Menjaga kelangsungan hidup party dari efek korosi dan serangan berkala musuh.";
                $tags[] = 'Sustainer';
            }
        }

        // 5. Bonus Status Inventori (Jika akun pemain dipilih)
        if ($inv) {
            $levelBonus = min(15, (int)(($inv->level / 90) * 15));
            $baseScore += $levelBonus;
            if ($inv->level >= 80) {
                $pros[] = "Karakter sudah siap tempur di level {$inv->level}.";
            }
            if ($inv->constellation > 0) {
                $constBonus = min(10, $inv->constellation * 2);
                $baseScore += $constBonus;
                $pros[] = "Memiliki Konstelasi C{$inv->constellation} yang memperkuat performa.";
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
        ];
    }

    /**
     * Analisis sinergi lengkap dari 4 karakter dalam satu party melawan musuh
     */
    public function analyzePartyVsEnemy(array $characterIds, Enemy $enemy, ?int $gameAccountId = null): array
    {
        $characterIds = array_filter(array_slice($characterIds, 0, 4));
        if (empty($characterIds)) {
            return [
                'team_score' => 0,
                'team_tier' => 'C',
                'members' => [],
                'resonances' => [],
                'warnings' => ['Pilih minimal 1 karakter untuk memulai analisis tim.'],
                'advantages' => [],
                'role_coverage' => ['dps' => false, 'sub_dps' => false, 'support' => false, 'sustain' => false],
            ];
        }

        $characters = Character::whereIn('id', $characterIds)->get()->keyBy('id');
        $invMap = collect();
        if ($gameAccountId) {
            $invMap = InventoryCharacter::where('game_account_id', $gameAccountId)
                ->whereIn('character_id', $characterIds)
                ->get()
                ->keyBy('character_id');
        }

        $members = [];
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

            $analysis = $this->scoreCharacterVsEnemy($char, $enemy, $inv);
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

        $teamScore = max(10, min(100, $avgScore + $bonusScore));
        $teamTier = match (true) {
            $teamScore >= 85 => 'S',
            $teamScore >= 70 => 'A',
            $teamScore >= 55 => 'B',
            default          => 'C',
        };

        return [
            'team_score'    => $teamScore,
            'team_tier'     => $teamTier,
            'members'       => $members,
            'resonances'    => $resonances,
            'warnings'      => $warnings,
            'advantages'    => $advantages,
            'role_coverage' => [
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
    public function autoGenerateParty(Enemy $enemy, ?int $gameAccountId = null): array
    {
        // Ambil kandidat karakter
        if ($gameAccountId) {
            $invChars = InventoryCharacter::with('character')
                ->where('game_account_id', $gameAccountId)
                ->get();
            
            $scored = $invChars->map(function ($inv) use ($enemy) {
                return $this->scoreCharacterVsEnemy($inv->character, $enemy, $inv);
            });
        } else {
            $chars = Character::where('is_active', true)->get();
            $scored = $chars->map(function ($c) use ($enemy) {
                return $this->scoreCharacterVsEnemy($c, $enemy, null);
            });
        }

        // Filter kandidat yang TIDAK kebal
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