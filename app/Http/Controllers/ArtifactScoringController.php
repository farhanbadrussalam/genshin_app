<?php

namespace App\Http\Controllers;

use App\Models\ArtifactScoringRule;
use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Services\ArtifactScoringService;
use App\Services\EnkaNetworkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtifactScoringController extends Controller
{
    protected $scorer;
    protected $enkaService;

    public function __construct(ArtifactScoringService $scorer, EnkaNetworkService $enkaService)
    {
        $this->scorer = $scorer;
        $this->enkaService = $enkaService;
    }

    /**
     * Halaman utama: daftar artifact dengan skor
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::orderBy('nickname')->get();
        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);
        $activeAccount = $accounts->firstWhere('id', $selectedAccountId) ?? $accounts->first();

        $artifacts = collect();
        $characters = collect();
        $rules      = collect();

        if ($activeAccount) {
            $query = InventoryArtifact::with(['artifactSet', 'equippedCharacter', 'scoredForCharacter'])
                ->where('game_account_id', $activeAccount->id);

            // Filter rating
            if ($request->filled('rating') && $request->input('rating') !== 'all') {
                $query->where('score_rating', $request->input('rating'));
            }

            // Filter slot
            if ($request->filled('slot') && $request->input('slot') !== 'all') {
                $query->where('slot_key', $request->input('slot'));
            }

            // Filter karakter yang memakai artifact
            if ($request->filled('equipped_character') && $request->input('equipped_character') !== 'all') {
                $equipped = $request->input('equipped_character');
                if ($equipped === 'unequipped') {
                    $query->whereNull('equipped_character_id');
                } elseif ($equipped === 'equipped') {
                    $query->whereNotNull('equipped_character_id');
                } else {
                    $query->where('equipped_character_id', $equipped);
                }
            }

            // Filter hanya yang sudah di-score
            if ($request->boolean('scored_only')) {
                $query->whereNotNull('score');
            }

            // Sorting
            $sort = $request->input('sort', 'score_desc');
            match ($sort) {
                'score_asc'  => $query->orderBy('score', 'asc'),
                'level_desc' => $query->orderBy('level', 'desc'),
                default      => $query->orderByRaw('score IS NULL, score DESC'),
            };

            $artifacts = $query->paginate(20)->withQueryString();

            // Karakter untuk dropdown scoring & filter
            $characters = Character::where(function ($q) use ($activeAccount) {
                $q->whereHas('inventoryCharacters', function ($sub) use ($activeAccount) {
                    $sub->where('game_account_id', $activeAccount->id);
                })->orWhereIn('id', function ($sub) use ($activeAccount) {
                    $sub->select('equipped_character_id')
                        ->from('inventory_artifacts')
                        ->where('game_account_id', $activeAccount->id)
                        ->whereNotNull('equipped_character_id');
                });
            })->orderBy('name')->get();

            $rules = ArtifactScoringRule::with('character')
                ->whereIn('character_id', $characters->pluck('id'))
                ->get()
                ->keyBy('character_id');
        }

        return view('inventory.artifact-scoring', [
            'title'         => 'Artifact Scoring',
            'accounts'      => $accounts,
            'activeAccount' => $activeAccount,
            'artifacts'     => $artifacts,
            'characters'    => $characters,
            'rules'         => $rules,
            'archetypes'    => $this->scorer->getArchetypes(),
            'statOptions'   => [
                'crit_rate' => 'CRIT Rate (%)',
                'crit_dmg'  => 'CRIT DMG (%)',
                'atk_pct'   => 'ATK (%)',
                'hp_pct'    => 'HP (%)',
                'def_pct'   => 'DEF (%)',
                'em'        => 'Elemental Mastery',
                'er'        => 'Energy Recharge (%)',
                'flat_atk'  => 'ATK (Flat)',
                'flat_hp'   => 'HP (Flat)',
                'flat_def'  => 'DEF (Flat)',
            ],
            'filters'       => $request->only(['account_id', 'rating', 'slot', 'equipped_character', 'scored_only', 'sort']),
        ]);
    }

    /**
     * Skor satu artifact via AJAX → return JSON
     */
    public function scoreOne(Request $request, InventoryArtifact $artifact): JsonResponse
    {
        $characterId = $request->input('character_id');
        $character   = $characterId ? Character::find($characterId) : $artifact->equippedCharacter;

        $rule   = $character
            ? ArtifactScoringRule::where('character_id', $character->id)->first()
            : null;

        $result = $this->scorer->score($artifact, $character, $rule);
        $this->scorer->scoreAndSave($artifact, $character, $rule);

        return response()->json([
            'success'  => true,
            'score'    => $result['score'],
            'rating'   => $result['rating'],
            'details'  => $result['details'],
            'character'=> $result['character'],
        ]);
    }

    /**
     * Batch score semua artifact di account aktif
     */
    public function scoreAll(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:game_accounts,id',
        ]);

        $result = $this->scorer->scoreAllForAccount((int) $request->input('account_id'));

        return response()->json([
            'success'   => true,
            'processed' => $result['processed'],
            'skipped'   => $result['skipped'],
            'message'   => "Berhasil menghitung {$result['processed']} artifact. Dilewati: {$result['skipped']} (tanpa sub-stats).",
        ]);
    }

    /**
     * Update/input sub-stats untuk satu artifact via AJAX & hitung ulang skor
     */
    public function updateSubStats(Request $request, InventoryArtifact $artifact): JsonResponse
    {
        $request->validate([
            'sub_stats'    => 'required|array',
            'character_id' => 'nullable|exists:characters,id',
        ]);

        $formatted = [];
        foreach ($request->input('sub_stats') as $item) {
            $key = trim($item['key'] ?? '');
            $val = (float) ($item['value'] ?? 0);
            if (!empty($key) && $val > 0) {
                $formatted[] = [
                    'key'   => $key,
                    'value' => $val,
                ];
            }
        }

        $artifact->update([
            'sub_stats' => $formatted,
        ]);

        // Hitung skor artifact yang baru
        $characterId = $request->input('character_id');
        $character   = $characterId ? Character::find($characterId) : $artifact->equippedCharacter;
        $rule        = $character ? ArtifactScoringRule::where('character_id', $character->id)->first() : null;

        $result = $this->scorer->score($artifact, $character, $rule);
        $this->scorer->scoreAndSave($artifact, $character, $rule);

        return response()->json([
            'success'   => true,
            'message'   => 'Sub-stat berhasil disimpan dan skor langsung dihitung!',
            'sub_stats' => $formatted,
            'score'     => $result['score'],
            'rating'    => $result['rating'],
            'details'   => $result['details'],
            'character' => $result['character'],
        ]);
    }

    /**
     * Generate sub-stats acak realistis untuk testing semua artifact yang belum memiliki sub-stats
     */
    public function generateMockSubStats(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:game_accounts,id',
        ]);

        $accountId = (int) $request->input('account_id');
        $artifacts = InventoryArtifact::where('game_account_id', $accountId)->get();

        $statPool = [
            'crit_rate' => [2.7, 3.1, 3.5, 3.9],
            'crit_dmg'  => [5.4, 6.2, 7.0, 7.8],
            'atk_pct'   => [4.1, 4.7, 5.3, 5.8],
            'hp_pct'    => [4.1, 4.7, 5.3, 5.8],
            'def_pct'   => [5.1, 5.8, 6.6, 7.3],
            'em'        => [16, 19, 21, 23],
            'er'        => [4.5, 5.2, 5.8, 6.5],
            'flat_atk'  => [14, 16, 18, 19],
            'flat_hp'   => [209, 239, 269, 299],
            'flat_def'  => [16, 19, 21, 23],
        ];

        $count = 0;
        foreach ($artifacts as $art) {
            $availableKeys = array_keys($statPool);
            shuffle($availableKeys);
            $selectedKeys = array_slice($availableKeys, 0, 4);

            $numRolls = (int) floor(($art->level ?: 20) / 4) + ($art->rarity === 5 ? 4 : 3);
            $generated = [];
            foreach ($selectedKeys as $k) {
                $baseVal = $statPool[$k][array_rand($statPool[$k])];
                $generated[$k] = $baseVal;
            }

            // Tambahkan rolls tambahan sesuai level (+0 s/d +20)
            $extraRolls = max(0, $numRolls - 4);
            for ($i = 0; $i < $extraRolls; $i++) {
                $targetKey = $selectedKeys[array_rand($selectedKeys)];
                $addVal = $statPool[$targetKey][array_rand($statPool[$targetKey])];
                $generated[$targetKey] = round($generated[$targetKey] + $addVal, 1);
            }

            $formatted = [];
            foreach ($generated as $k => $v) {
                $formatted[] = ['key' => $k, 'value' => $v];
            }

            $art->sub_stats = $formatted;
            $art->save();

            // Hitung skor otomatis
            $character = $art->equippedCharacter;
            $rule = $character ? ArtifactScoringRule::where('character_id', $character->id)->first() : null;
            $this->scorer->scoreAndSave($art, $character, $rule);

            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghasilkan sub-stats realistis untuk {$count} artifact dan menghitung seluruh skornya!",
        ]);
    }

    /**
     * Sinkronkan artifact showcase dengan sub-stat asli dari Enka.Network API via UID
     */
    public function syncEnka(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:game_accounts,id',
            'uid'        => 'nullable|string|max:20',
        ]);

        $account = GameAccount::findOrFail($request->input('account_id'));
        $uid = $request->filled('uid') ? trim($request->input('uid')) : $account->uid;

        try {
            $result = $this->enkaService->syncArtifactsFromEnka($account, $uid);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sync dari Enka.Network: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Tampilkan/edit aturan scoring untuk satu karakter
     */
    public function showRule(Character $character): View
    {
        $rule = ArtifactScoringRule::firstOrNew(['character_id' => $character->id]);

        return view('inventory.scoring-rule', [
            'title'      => "Aturan Scoring: {$character->name}",
            'character'  => $character,
            'rule'       => $rule,
            'archetypes' => $this->scorer->getArchetypes(),
        ]);
    }

    /**
     * Simpan/update aturan scoring untuk karakter
     */
    public function saveRule(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'w_crit_rate' => 'required|numeric|min:0|max:1',
            'w_crit_dmg'  => 'required|numeric|min:0|max:1',
            'w_atk_pct'   => 'required|numeric|min:0|max:1',
            'w_hp_pct'    => 'required|numeric|min:0|max:1',
            'w_def_pct'   => 'required|numeric|min:0|max:1',
            'w_em'        => 'required|numeric|min:0|max:1',
            'w_er'        => 'required|numeric|min:0|max:1',
            'w_flat_atk'  => 'required|numeric|min:0|max:1',
            'w_flat_hp'   => 'required|numeric|min:0|max:1',
            'w_flat_def'  => 'required|numeric|min:0|max:1',
            'role'        => 'required|in:dps,sub_dps,support,healer',
            'build_note'  => 'nullable|string|max:255',
        ]);

        ArtifactScoringRule::updateOrCreate(
            ['character_id' => $character->id],
            $validated
        );

        return redirect()
            ->route('artifact-scoring.rule', $character->id)
            ->with('success', "Aturan scoring untuk {$character->name} berhasil disimpan.");
    }

    /**
     * Load preset archetype weights via AJAX
     */
    public function loadPreset(Request $request): JsonResponse
    {
        $archetype = $request->input('archetype', 'dps_crit');
        $weights   = $this->scorer->getTemplateWeights($archetype);

        return response()->json(['weights' => $weights]);
    }

    /**
     * Daftar scoring rules semua karakter
     */
    public function rulesIndex(Request $request): View
    {
        $accounts = GameAccount::orderBy('nickname')->get();
        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);
        $activeAccount = $accounts->firstWhere('id', $selectedAccountId) ?? $accounts->first();

        $characters = Character::with('scoringRule')
            ->when($activeAccount, function ($q) use ($activeAccount) {
                $q->whereHas('inventoryCharacters', function ($iq) use ($activeAccount) {
                    $iq->where('game_account_id', $activeAccount->id);
                });
            })
            ->orderBy('name')
            ->get();

        return view('inventory.scoring-rules-index', [
            'title'         => 'Aturan Scoring Artifact per Karakter',
            'accounts'      => $accounts,
            'activeAccount' => $activeAccount,
            'characters'    => $characters,
        ]);
    }
}
