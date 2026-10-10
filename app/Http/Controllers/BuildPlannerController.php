<?php

namespace App\Http\Controllers;

use App\Models\ArtifactScoringRule;
use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Models\InventoryCharacter;
use App\Models\InventoryWeapon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildPlannerController extends Controller
{
    /**
     * Tampilkan ringkasan build karakter beserta item terbaik yang dimiliki akun.
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::where('game', 'genshin_impact')->orderBy('nickname')->get();
        $activeAccount = $accounts->firstWhere('id', (int) $request->input('account_id')) ?? $accounts->first();
        $ownedCharacters = collect();

        if ($activeAccount) {
            $ownedCharacters = InventoryCharacter::with('character')->where('game_account_id', $activeAccount->id)
                ->orderByDesc('level')->get()->filter(fn (InventoryCharacter $item) => $item->character);
        }

        $characters = $ownedCharacters->isNotEmpty()
            ? $ownedCharacters->map(fn (InventoryCharacter $item) => $item->character)->unique('id')->values()
            : Character::where('is_active', true)->orderBy('name')->get();
        $selectedCharacter = $characters->firstWhere('id', (int) $request->input('character_id')) ?? $characters->first();
        $characterInventory = $selectedCharacter && $activeAccount
            ? $ownedCharacters->firstWhere('character_id', $selectedCharacter->id) : null;

        $equippedWeapon = null;
        $weaponOptions = collect();
        $equippedArtifacts = collect();
        $bestArtifacts = collect();
        $setSummary = collect();
        $rule = null;

        if ($selectedCharacter && $activeAccount) {
            $equippedWeapon = InventoryWeapon::with('weapon')->where('game_account_id', $activeAccount->id)
                ->where('equipped_character_id', $selectedCharacter->id)->orderByDesc('level')->first();
            $weaponOptions = InventoryWeapon::with('weapon')->where('game_account_id', $activeAccount->id)
                ->whereHas('weapon', fn ($query) => $query->where('type', $selectedCharacter->weapon_type))
                ->get()->sortByDesc(fn (InventoryWeapon $item) => (($item->weapon?->rarity ?? 0) * 1000) + $item->level)
                ->take(5)->values();
            $equippedArtifacts = InventoryArtifact::with('artifactSet')->where('game_account_id', $activeAccount->id)
                ->where('equipped_character_id', $selectedCharacter->id)->get()->keyBy('slot_key');
            $bestArtifacts = InventoryArtifact::with('artifactSet')->where('game_account_id', $activeAccount->id)
                ->get()->groupBy('slot_key')
                ->map(fn ($items) => $items->sortByDesc(fn (InventoryArtifact $item) => (($item->rarity ?? 0) * 1000) + (($item->score ?? 0) * 10) + $item->level)->first());
            $setSummary = $equippedArtifacts->filter(fn (InventoryArtifact $item) => $item->artifactSet)
                ->groupBy('artifact_set_id')->map(function ($items) {
                    $set = $items->first()->artifactSet;
                    return [
                        'name' => $set->name,
                        'icon_url' => $set->icon_url,
                        'count' => $items->count(),
                        'two_piece_bonus' => $set->two_piece_bonus,
                        'four_piece_bonus' => $set->four_piece_bonus,
                    ];
                })->values();
        }
        if ($selectedCharacter) {
            $rule = ArtifactScoringRule::where('character_id', $selectedCharacter->id)->first();
        }

        return view('planner.build.index', [
            'title' => 'Build Planner',
            'accounts' => $accounts,
            'activeAccount' => $activeAccount,
            'characters' => $characters,
            'selectedCharacter' => $selectedCharacter,
            'characterInventory' => $characterInventory,
            'equippedWeapon' => $equippedWeapon,
            'weaponOptions' => $weaponOptions,
            'equippedArtifacts' => $equippedArtifacts,
            'bestArtifacts' => $bestArtifacts,
            'setSummary' => $setSummary,
            'rule' => $rule,
            'guidance' => $selectedCharacter ? $this->buildGuidance($selectedCharacter, $rule) : null,
        ]);
    }

    private function buildGuidance(Character $character, ?ArtifactScoringRule $rule): array
    {
        $weights = $rule?->getWeightsArray() ?? [
            'crit_rate' => 1.0, 'crit_dmg' => 1.0, 'atk_pct' => 0.7, 'hp_pct' => 0.0, 'def_pct' => 0.0,
            'em' => 0.3, 'er' => 0.5, 'flat_atk' => 0.2, 'flat_hp' => 0.0, 'flat_def' => 0.0,
        ];
        arsort($weights);
        $labels = [
            'crit_rate' => 'CRIT Rate',
            'crit_dmg' => 'CRIT DMG',
            'atk_pct' => 'ATK%',
            'hp_pct' => 'HP%',
            'def_pct' => 'DEF%',
            'em' => 'Elemental Mastery',
            'er' => 'Energy Recharge',
            'flat_atk' => 'ATK flat',
            'flat_hp' => 'HP flat',
            'flat_def' => 'DEF flat',
        ];
        $priorities = collect(array_keys($weights))->take(4)->map(fn (string $key) => $labels[$key])->values();
        $mainStats = [
            'sands' => 'ATK% atau Energy Recharge',
            'goblet' => $character->element . ' DMG Bonus',
            'circlet' => 'CRIT Rate atau CRIT DMG',
        ];
        if (($weights['hp_pct'] ?? 0) > ($weights['atk_pct'] ?? 0)) {
            $mainStats['sands'] = 'HP% atau Energy Recharge';
        } elseif (($weights['def_pct'] ?? 0) > ($weights['atk_pct'] ?? 0)) {
            $mainStats['sands'] = 'DEF% atau Energy Recharge';
        } elseif (($weights['em'] ?? 0) >= 0.8) {
            $mainStats['sands'] = 'Elemental Mastery atau Energy Recharge';
            $mainStats['goblet'] = 'Elemental Mastery atau ' . $character->element . ' DMG Bonus';
        }
        return [
            'role' => $rule?->role ?: 'Main DPS / fleksibel',
            'note' => $rule?->build_note ?: 'Belum ada aturan khusus. Rekomendasi memakai prioritas CRIT standar.',
            'priorities' => $priorities,
            'main_stats' => $mainStats,
        ];
    }
}
