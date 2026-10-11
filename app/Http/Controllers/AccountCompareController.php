<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryWeapon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountCompareController extends Controller
{
    /**
     * Halaman Pembanding Karakter Antara 2 Akun Game
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::where('game', 'genshin_impact')
            ->orderBy('nickname')
            ->get();

        if ($accounts->isEmpty()) {
            $accounts = GameAccount::orderBy('nickname')->get();
        }

        // Tentukan Akun 1 & Akun 2
        $account1Id = (int) $request->input('account1_id', session('active_game_account_id', $accounts->first()?->id));
        $account1 = $accounts->firstWhere('id', $account1Id) ?? $accounts->first();

        $account2Id = (int) $request->input('account2_id', 0);
        if ($account2Id === 0 || $account2Id === $account1?->id) {
            $account2 = $accounts->firstWhere('id', '!=', $account1?->id) ?? $accounts->first();
        } else {
            $account2 = $accounts->firstWhere('id', $account2Id) ?? $accounts->first();
        }

        // Ambil daftar karakter yang dimiliki masing-masing akun
        $acc1CharIds = $account1 ? InventoryCharacter::where('game_account_id', $account1->id)->pluck('character_id')->toArray() : [];
        $acc2CharIds = $account2 ? InventoryCharacter::where('game_account_id', $account2->id)->pluck('character_id')->toArray() : [];

        // Daftar semua karakter master
        $allCharacters = Character::where('is_active', true)
            ->orderBy('rarity', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        foreach ($allCharacters as $c) {
            $c->owned_1 = in_array($c->id, $acc1CharIds);
            $c->owned_2 = in_array($c->id, $acc2CharIds);
            $c->owned_both = $c->owned_1 && $c->owned_2;
        }

        // Tentukan Karakter yang Dipilih
        $selectedCharId = (int) $request->input('character_id', 0);
        if ($selectedCharId === 0) {
            // Prioritas 1: Karakter yang dimiliki kedua akun
            $commonChar = $allCharacters->firstWhere('owned_both', true);
            if ($commonChar) {
                $selectedCharId = $commonChar->id;
            } elseif (!empty($acc1CharIds)) {
                $selectedCharId = $acc1CharIds[0];
            } elseif (!empty($acc2CharIds)) {
                $selectedCharId = $acc2CharIds[0];
            } else {
                $selectedCharId = $allCharacters->first()?->id ?? 1;
            }
        }

        $selectedCharacter = Character::find($selectedCharId) ?? $allCharacters->first();

        // Data Build Lengkap Akun 1 & Akun 2
        $build1 = $account1 ? $this->getCharacterBuildData($account1, $selectedCharacter) : null;
        $build2 = $account2 ? $this->getCharacterBuildData($account2, $selectedCharacter) : null;

        // Komparasi metrik
        $comparison = $this->compareBuilds($build1, $build2);

        return view('inventory.compare', [
            'title'             => 'Komparasi Build Akun: ' . ($selectedCharacter?->name ?? 'Karakter'),
            'accounts'          => $accounts,
            'account1'          => $account1,
            'account2'          => $account2,
            'allCharacters'     => $allCharacters,
            'selectedCharacter' => $selectedCharacter,
            'build1'            => $build1,
            'build2'            => $build2,
            'comparison'        => $comparison,
        ]);
    }

    /**
     * Ambil data lengkap karakter, senjata yang dipakai, dan 5 slot artefak untuk 1 akun
     */
    private function getCharacterBuildData(GameAccount $account, Character $character): array
    {
        // 1. Data Karakter di Inventori Akun
        $invChar = InventoryCharacter::where('game_account_id', $account->id)
            ->where('character_id', $character->id)
            ->first();

        // 2. Senjata yang sedang dipakai karakter ini
        $invWeapon = InventoryWeapon::with('weapon')
            ->where('game_account_id', $account->id)
            ->where('equipped_character_id', $character->id)
            ->first();

        // 3. Artefak yang sedang dipakai karakter ini (5 slot)
        $equippedArts = InventoryArtifact::with('artifactSet')
            ->where('game_account_id', $account->id)
            ->where('equipped_character_id', $character->id)
            ->get();

        $slots = [
            'flower'  => null,
            'plume'   => null,
            'sands'   => null,
            'goblet'  => null,
            'circlet' => null,
        ];

        $setCounts = [];
        $totalCritRate = 0.0;
        $totalCritDmg = 0.0;
        $totalArtifactScore = 0.0;
        $scoredCount = 0;

        foreach ($equippedArts as $art) {
            $slotKey = strtolower($art->slot_key);
            if (array_key_exists($slotKey, $slots)) {
                $slots[$slotKey] = $art;
            }

            // Hitung Set Bonus
            if ($art->artifactSet) {
                $setName = $art->artifactSet->name;
                $setCounts[$setName] = ($setCounts[$setName] ?? 0) + 1;
            }

            // Hitung CV & Substats
            if (!empty($art->sub_stats) && is_array($art->sub_stats)) {
                foreach ($art->sub_stats as $sub) {
                    $k = strtolower($sub['key'] ?? '');
                    $val = (float) ($sub['value'] ?? 0);
                    if (in_array($k, ['crit_rate', 'critrate', 'critrate_', 'cr'])) {
                        $totalCritRate += $val;
                    }
                    if (in_array($k, ['crit_dmg', 'critdmg', 'critdmg_', 'cd'])) {
                        $totalCritDmg += $val;
                    }
                }
            }

            // Hitung Artifact Score
            if ($art->score !== null) {
                $totalArtifactScore += (float) $art->score;
                $scoredCount++;
            }
        }

        // Tentukan Set Bonuses yang aktif (2-pc atau 4-pc)
        $activeSetBonuses = [];
        foreach ($setCounts as $setName => $count) {
            if ($count >= 4) {
                $activeSetBonuses[] = [
                    'name'  => $setName,
                    'count' => 4,
                    'label' => "4-Piece Set: {$setName}",
                ];
            } elseif ($count >= 2) {
                $activeSetBonuses[] = [
                    'name'  => $setName,
                    'count' => 2,
                    'label' => "2-Piece Set: {$setName}",
                ];
            }
        }

        // Total Crit Value (CV) dari artefak
        $totalCv = ($totalCritRate * 2) + $totalCritDmg;

        return [
            'is_owned'              => $invChar !== null,
            'character'             => $invChar,
            'weapon'                => $invWeapon,
            'artifacts'             => $slots,
            'active_set_bonuses'    => $activeSetBonuses,
            'total_crit_rate'       => round($totalCritRate, 1),
            'total_crit_dmg'        => round($totalCritDmg, 1),
            'total_cv'              => round($totalCv, 1),
            'total_artifact_score'  => round($totalArtifactScore, 1),
            'avg_artifact_score'    => $scoredCount > 0 ? round($totalArtifactScore / $scoredCount, 1) : 0,
            'equipped_artifact_cnt' => $equippedArts->count(),
        ];
    }

    /**
     * Hitung perbandingan metrik antara kedua build
     */
    private function compareBuilds(?array $b1, ?array $b2): array
    {
        if (!$b1 || !$b2) {
            return [];
        }

        $res = [];

        // 1. Level Karakter
        $lvl1 = $b1['is_owned'] ? (int) ($b1['character']->level ?? 0) : 0;
        $lvl2 = $b2['is_owned'] ? (int) ($b2['character']->level ?? 0) : 0;
        $res['level_winner'] = $lvl1 > $lvl2 ? 1 : ($lvl2 > $lvl1 ? 2 : 0);
        $res['level_diff']   = abs($lvl1 - $lvl2);

        // 2. Konstelasi
        $c1 = $b1['is_owned'] ? (int) ($b1['character']->constellation ?? 0) : 0;
        $c2 = $b2['is_owned'] ? (int) ($b2['character']->constellation ?? 0) : 0;
        $res['const_winner'] = $c1 > $c2 ? 1 : ($c2 > $c1 ? 2 : 0);
        $res['const_diff']   = abs($c1 - $c2);

        // 3. Senjata
        $w1Level = $b1['weapon'] ? (int) $b1['weapon']->level : 0;
        $w2Level = $b2['weapon'] ? (int) $b2['weapon']->level : 0;
        $w1Ref = $b1['weapon'] ? (int) $b1['weapon']->refinement : 0;
        $w2Ref = $b2['weapon'] ? (int) $b2['weapon']->refinement : 0;
        $res['weapon_winner'] = ($w1Ref > $w2Ref || ($w1Ref === $w2Ref && $w1Level > $w2Level)) ? 1 : (($w2Ref > $w1Ref || ($w1Ref === $w2Ref && $w2Level > $w1Level)) ? 2 : 0);

        // 4. Crit Value (CV) Artefak
        $cv1 = $b1['total_cv'];
        $cv2 = $b2['total_cv'];
        $res['cv_winner'] = $cv1 > $cv2 ? 1 : ($cv2 > $cv1 ? 2 : 0);
        $res['cv_diff']   = round(abs($cv1 - $cv2), 1);

        // 5. Total Artifact Score
        $s1 = $b1['total_artifact_score'];
        $s2 = $b2['total_artifact_score'];
        $res['score_winner'] = $s1 > $s2 ? 1 : ($s2 > $s1 ? 2 : 0);
        $res['score_diff']   = round(abs($s1 - $s2), 1);

        return $res;
    }
}