<?php

namespace App\Http\Controllers;

use App\Models\ArtifactSet;
use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryArtifact;
use App\Services\EnkaNetworkService;
use App\Services\HoyoLabMicroservice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryArtifactController extends Controller
{
    public function __construct(
        protected HoyoLabMicroservice $hoyolab,
        protected EnkaNetworkService $enka
    ) {}

    /**
     * Tampilkan inventori artifact berdasarkan akun game yang dipilih
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::where('game', 'genshin_impact')
            ->orderBy('nickname')
            ->get();

        if ($accounts->isEmpty()) {
            $accounts = GameAccount::orderBy('nickname')->get();
        }

        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);
        $activeAccount = $accounts->firstWhere('id', $selectedAccountId) ?? $accounts->first();

        $inventoryArtifacts = collect();
        $stats = [
            'total'    => 0,
            'star5'    => 0,
            'max_lvl'  => 0, // Level 20
            'equipped' => 0,
        ];

        if ($activeAccount) {
            $query = InventoryArtifact::with(['artifactSet', 'equippedCharacter'])
                ->where('game_account_id', $activeAccount->id);

            // Filter Pencarian Nama Set
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('artifactSet', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Filter Slot Piece
            if ($request->filled('slot') && $request->input('slot') !== 'all') {
                $query->where('slot_key', $request->input('slot'));
            }

            // Filter Set
            if ($request->filled('set_id') && $request->input('set_id') !== 'all') {
                $query->where('artifact_set_id', (int)$request->input('set_id'));
            }

            // Filter Rarity
            if ($request->filled('rarity') && $request->input('rarity') !== 'all') {
                $query->where('rarity', (int)$request->input('rarity'));
            }

            // Filter Status Equip
            if ($request->filled('equipped') && $request->input('equipped') !== 'all') {
                if ($request->input('equipped') === 'yes') {
                    $query->whereNotNull('equipped_character_id');
                } else {
                    $query->whereNull('equipped_character_id');
                }
            }

            // Sorting
            $sort = $request->input('sort', 'level_desc');
            match ($sort) {
                'level_asc'  => $query->orderBy('level', 'asc'),
                'rarity_desc'=> $query->orderBy('rarity', 'desc')->orderBy('level', 'desc'),
                'slot'       => $query->orderBy('slot_key', 'asc')->orderBy('level', 'desc'),
                default      => $query->orderBy('level', 'desc')->orderBy('rarity', 'desc'),
            };

            $inventoryArtifacts = $query->paginate(24)->withQueryString();

            // Statistik
            $allArtifacts = InventoryArtifact::where('game_account_id', $activeAccount->id)->get();
            $stats['total']    = $allArtifacts->count();
            $stats['star5']    = $allArtifacts->where('rarity', 5)->count();
            $stats['max_lvl']  = $allArtifacts->where('level', '>=', 20)->count();
            $stats['equipped'] = $allArtifacts->whereNotNull('equipped_character_id')->count();
        }

        // Master Set untuk dropdown
        $availableSets = ArtifactSet::orderBy('name')->get();

        // Karakter akun ini untuk opsi equip
        $accountCharacters = $activeAccount
            ? Character::whereHas('inventoryCharacters', function ($q) use ($activeAccount) {
                $q->where('game_account_id', $activeAccount->id);
            })->orderBy('name')->get()
            : collect();

        return view('inventory.artifacts', [
            'title'              => 'Inventori Artifact',
            'accounts'           => $accounts,
            'activeAccount'      => $activeAccount,
            'inventoryArtifacts' => $inventoryArtifacts,
            'availableSets'      => $availableSets,
            'accountCharacters'  => $accountCharacters,
            'stats'              => $stats,
            'filters'            => $request->only(['account_id', 'search', 'slot', 'set_id', 'rarity', 'equipped', 'sort']),
        ]);
    }

    /**
     * Tambah artifact ke inventori
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'game_account_id'       => 'required|exists:game_accounts,id',
            'artifact_set_id'       => 'required|exists:artifact_sets,id',
            'slot_key'              => 'required|in:flower,plume,sands,goblet,circlet',
            'rarity'                => 'required|integer|in:4,5',
            'level'                 => 'required|integer|min:0|max:20',
            'main_stat_key'         => 'required|string|max:50',
            'main_stat_value'       => 'required|string|max:50',
            'equipped_character_id' => 'nullable|exists:characters,id',
            'notes'                 => 'nullable|string|max:255',
            'sub_stats'             => 'nullable|array',
        ]);

        InventoryArtifact::create($validated);

        return redirect()->route('inventory.artifacts.index', ['account_id' => $validated['game_account_id']])
            ->with('success', 'Artifact berhasil ditambahkan ke inventori!');
    }

    /**
     * Update artifact di inventori
     */
    public function update(Request $request, InventoryArtifact $inventoryArtifact): RedirectResponse
    {
        $validated = $request->validate([
            'artifact_set_id'       => 'required|exists:artifact_sets,id',
            'slot_key'              => 'required|in:flower,plume,sands,goblet,circlet',
            'rarity'                => 'required|integer|in:4,5',
            'level'                 => 'required|integer|min:0|max:20',
            'main_stat_key'         => 'required|string|max:50',
            'main_stat_value'       => 'required|string|max:50',
            'equipped_character_id' => 'nullable|exists:characters,id',
            'notes'                 => 'nullable|string|max:255',
            'sub_stats'             => 'nullable|array',
        ]);

        $inventoryArtifact->update($validated);

        return redirect()->route('inventory.artifacts.index', ['account_id' => $inventoryArtifact->game_account_id])
            ->with('success', 'Data artifact berhasil diperbarui!');
    }

    /**
     * Hapus artifact dari inventori
     */
    public function destroy(InventoryArtifact $inventoryArtifact): RedirectResponse
    {
        $accountId = $inventoryArtifact->game_account_id;
        $name = $inventoryArtifact->artifactSet?->name ?? 'Artifact';
        $slot = $inventoryArtifact->slot_label;
        $inventoryArtifact->delete();

        return redirect()->route('inventory.artifacts.index', ['account_id' => $accountId])
            ->with('success', "{$name} ({$slot}) berhasil dihapus dari inventori!");
    }

    /**
     * Sinkronisasi data artifact yang dipakai karakter dari HoYoLAB microservice
     */
    public function syncFromHoyoLab(Request $request): RedirectResponse
    {
        $request->validate([
            'game_account_id'  => 'required|exists:game_accounts,id',
            'ltuid_v2'         => 'nullable|string',
            'ltoken_v2'        => 'nullable|string',
            'save_credentials' => 'nullable',
        ]);

        $account = GameAccount::findOrFail($request->input('game_account_id'));

        $ltuid = trim($request->input('ltuid_v2') ?: ($account->ltuid_v2 ?? ''));
        $ltoken = trim($request->input('ltoken_v2') ?: ($account->ltoken_v2 ?? ''));

        if (empty($ltuid) || empty($ltoken)) {
            return redirect()->back()
                ->with('error', 'Cookie ltuid_v2 dan ltoken_v2 dibutuhkan untuk sinkronisasi HoYoLAB.');
        }

        try {
            $battleChronicle = $this->hoyolab->getCharacters(
                ltuid_v2: $ltuid,
                ltoken_v2: $ltoken,
                uid: (int) $account->uid
            );
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal sync dari HoYoLAB: ' . $e->getMessage());
        }

        $characters = $battleChronicle['characters'] ?? [];
        if (empty($characters)) {
            return redirect()->back()
                ->with('error', 'Tidak ada data karakter/artefak ditemukan dari akun HoYoLAB.');
        }

        $posMap = [
            1 => 'flower',
            2 => 'plume',
            3 => 'sands',
            4 => 'goblet',
            5 => 'circlet',
        ];

        $defaultStats = [
            'flower'  => ['key' => 'hp', 'val' => '4,780'],
            'plume'   => ['key' => 'atk', 'val' => '311'],
            'sands'   => ['key' => 'atk_percent', 'val' => '46.6%'],
            'goblet'  => ['key' => 'elemental_dmg', 'val' => '46.6%'],
            'circlet' => ['key' => 'crit_rate', 'val' => '31.1%'],
        ];

        $syncedCount = 0;

        foreach ($characters as $charData) {
            if (empty($charData['artifacts'])) {
                continue;
            }

            $charName = $charData['name'];
            $character = Character::firstOrCreate(
                ['slug' => Str::slug($charName)],
                [
                    'name'        => $charName,
                    'element'     => ucfirst($charData['element'] ?? 'Pyro'),
                    'weapon_type' => 'Sword',
                    'rarity'      => $charData['rarity'] ?? 4,
                    'icon_url'    => $charData['image'] ?? null,
                ]
            );

            foreach ($charData['artifacts'] as $artData) {
                $pos = (int) ($artData['pos'] ?? 1);
                $slotKey = $posMap[$pos] ?? 'flower';
                $setName = trim($artData['set_name'] ?? '');

                if (empty($setName)) {
                    $setName = 'Gladiator\'s Finale'; // Fallback set
                }

                $artSet = ArtifactSet::firstOrCreate(
                    ['slug' => Str::slug($setName)],
                    [
                        'name'     => $setName,
                        'rarity'   => (int) ($artData['rarity'] ?? 5),
                        'icon_url' => !empty($artData['icon']) ? $artData['icon'] : null,
                    ]
                );

                $defStat = $defaultStats[$slotKey] ?? ['key' => 'hp', 'val' => '4,780'];
                $mainStatKey = $defStat['key'];
                $mainStatValue = !empty($artData['main_stat_value']) ? trim($artData['main_stat_value']) : $defStat['val'];

                $statName = trim($artData['main_stat_name'] ?? '');
                if (!empty($statName)) {
                    $isPercent = str_contains($mainStatValue, '%');
                    $lowerName = strtolower($statName);

                    if (str_contains($lowerName, 'hp')) {
                        $mainStatKey = $isPercent ? 'hp_percent' : 'hp';
                    } elseif (str_contains($lowerName, 'atk')) {
                        $mainStatKey = $isPercent ? 'atk_percent' : 'atk';
                    } elseif (str_contains($lowerName, 'def')) {
                        $mainStatKey = $isPercent ? 'def_percent' : 'def';
                    } elseif (str_contains($lowerName, 'energy recharge')) {
                        $mainStatKey = 'energy_recharge';
                    } elseif (str_contains($lowerName, 'elemental mastery')) {
                        $mainStatKey = 'elemental_mastery';
                    } elseif (str_contains($lowerName, 'crit rate')) {
                        $mainStatKey = 'crit_rate';
                    } elseif (str_contains($lowerName, 'crit dmg')) {
                        $mainStatKey = 'crit_dmg';
                    } elseif (str_contains($lowerName, 'healing bonus')) {
                        $mainStatKey = 'healing_bonus';
                    } elseif (str_contains($lowerName, 'pyro dmg')) {
                        $mainStatKey = 'pyro_dmg';
                    } elseif (str_contains($lowerName, 'hydro dmg')) {
                        $mainStatKey = 'hydro_dmg';
                    } elseif (str_contains($lowerName, 'dendro dmg')) {
                        $mainStatKey = 'dendro_dmg';
                    } elseif (str_contains($lowerName, 'electro dmg')) {
                        $mainStatKey = 'electro_dmg';
                    } elseif (str_contains($lowerName, 'anemo dmg')) {
                        $mainStatKey = 'anemo_dmg';
                    } elseif (str_contains($lowerName, 'cryo dmg')) {
                        $mainStatKey = 'cryo_dmg';
                    } elseif (str_contains($lowerName, 'geo dmg')) {
                        $mainStatKey = 'geo_dmg';
                    } elseif (str_contains($lowerName, 'physical dmg')) {
                        $mainStatKey = 'physical_dmg';
                    }
                }

                InventoryArtifact::updateOrCreate(
                    [
                        'game_account_id'       => $account->id,
                        'equipped_character_id' => $character->id,
                        'slot_key'              => $slotKey,
                    ],
                    [
                        'artifact_set_id'       => $artSet->id,
                        'rarity'                => (int) ($artData['rarity'] ?? 5),
                        'level'                 => (int) ($artData['level'] ?? 20),
                        'main_stat_key'         => $mainStatKey,
                        'main_stat_value'       => $mainStatValue,
                        'scanned_at'            => now(),
                    ]
                );

                $syncedCount++;
            }
        }

        // Simpan credentials jika dipilih
        if ($request->has('save_credentials')) {
            $account->update([
                'ltuid_v2'  => $ltuid,
                'ltoken_v2' => $ltoken,
            ]);
        }

        $account->update(['last_synced_at' => now()]);

        return redirect()->route('inventory.artifacts.index', ['account_id' => $account->id])
            ->with('success', "Berhasil mensinkronkan {$syncedCount} artifact dari HoYoLAB untuk akun {$account->nickname}!");
    }

    /**
     * Sinkronisasi data artifact dari Enka.Network API via UID
     */
    public function syncFromEnka(Request $request)
    {
        $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'uid'             => 'nullable|string|max:20',
        ]);

        $account = GameAccount::findOrFail($request->input('game_account_id'));
        $uid = $request->filled('uid') ? trim($request->input('uid')) : $account->uid;

        try {
            $result = $this->enka->syncArtifactsFromEnka($account, $uid);

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return redirect()->route('inventory.artifacts.index', ['account_id' => $account->id])
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', 'Gagal sync dari Enka.Network: ' . $e->getMessage());
        }
    }
}
