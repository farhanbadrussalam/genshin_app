<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryWeapon;
use App\Models\Weapon;
use App\Services\EnkaNetworkService;
use App\Services\HoyoLabMicroservice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryWeaponController extends Controller
{
    public function __construct(
        protected HoyoLabMicroservice $hoyolab,
        protected EnkaNetworkService $enka
    ) {}

    /**
     * Tampilkan inventori senjata berdasarkan akun game yang dipilih
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

        $inventoryWeapons = collect();
        $stats = [
            'total'     => 0,
            'star5'     => 0,
            'max_level' => 0, // Level 90
            'r5'        => 0, // Refinement 5
        ];

        if ($activeAccount) {
            $query = InventoryWeapon::with(['weapon', 'equippedCharacter'])
                ->where('game_account_id', $activeAccount->id);

            // Filter Search Nama Senjata
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('weapon', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Filter Tipe Senjata
            if ($request->filled('type') && $request->input('type') !== 'all') {
                $type = $request->input('type');
                $query->whereHas('weapon', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            }

            // Filter Rarity
            if ($request->filled('rarity') && $request->input('rarity') !== 'all') {
                $rarity = (int)$request->input('rarity');
                $query->whereHas('weapon', function ($q) use ($rarity) {
                    $q->where('rarity', $rarity);
                });
            }

            // Filter Equip Status
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
                'level_asc' => $query->orderBy('level', 'asc'),
                'refine_desc' => $query->orderBy('refinement', 'desc'),
                'name_asc' => $query->join('weapons', 'inventory_weapons.weapon_id', '=', 'weapons.id')
                                    ->orderBy('weapons.name', 'asc')
                                    ->select('inventory_weapons.*'),
                default    => $query->orderBy('level', 'desc')->orderBy('ascension', 'desc'),
            };

            $inventoryWeapons = $query->paginate(24)->withQueryString();

            // Statistik
            $allWeapons = InventoryWeapon::with('weapon')
                ->where('game_account_id', $activeAccount->id)
                ->get();

            $stats['total'] = $allWeapons->count();
            $stats['star5'] = $allWeapons->where('weapon.rarity', 5)->count();
            $stats['max_level'] = $allWeapons->where('level', '>=', 90)->count();
            $stats['r5'] = $allWeapons->where('refinement', 5)->count();
        }

        // Master senjata untuk opsi tambah
        $availableWeapons = Weapon::orderBy('name')->get();

        // Karakter yang dimiliki akun ini (untuk dropdown equip)
        $accountCharacters = $activeAccount
            ? Character::whereHas('inventoryCharacters', function ($q) use ($activeAccount) {
                $q->where('game_account_id', $activeAccount->id);
            })->orderBy('name')->get()
            : collect();

        return view('inventory.weapons', [
            'title'             => 'Inventori Senjata',
            'accounts'          => $accounts,
            'activeAccount'     => $activeAccount,
            'inventoryWeapons'  => $inventoryWeapons,
            'availableWeapons'  => $availableWeapons,
            'accountCharacters' => $accountCharacters,
            'stats'             => $stats,
            'filters'           => $request->only(['account_id', 'search', 'type', 'rarity', 'equipped', 'sort']),
        ]);
    }

    /**
     * Tambah senjata ke inventori
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'game_account_id'       => 'required|exists:game_accounts,id',
            'weapon_id'             => 'required|exists:weapons,id',
            'level'                 => 'required|integer|min:1|max:90',
            'ascension'             => 'required|integer|min:0|max:6',
            'refinement'            => 'required|integer|min:1|max:5',
            'equipped_character_id' => 'nullable|exists:characters,id',
            'notes'                 => 'nullable|string|max:255',
        ]);

        InventoryWeapon::create($validated);

        return redirect()->route('inventory.weapons.index', ['account_id' => $validated['game_account_id']])
            ->with('success', 'Senjata berhasil ditambahkan ke inventori!');
    }

    /**
     * Update senjata di inventori
     */
    public function update(Request $request, InventoryWeapon $inventoryWeapon): RedirectResponse
    {
        $validated = $request->validate([
            'level'                 => 'required|integer|min:1|max:90',
            'ascension'             => 'required|integer|min:0|max:6',
            'refinement'            => 'required|integer|min:1|max:5',
            'equipped_character_id' => 'nullable|exists:characters,id',
            'notes'                 => 'nullable|string|max:255',
        ]);

        $inventoryWeapon->update($validated);

        return redirect()->route('inventory.weapons.index', ['account_id' => $inventoryWeapon->game_account_id])
            ->with('success', 'Data senjata berhasil diperbarui!');
    }

    /**
     * Hapus senjata dari inventori
     */
    public function destroy(InventoryWeapon $inventoryWeapon): RedirectResponse
    {
        $accountId = $inventoryWeapon->game_account_id;
        $name = $inventoryWeapon->weapon?->name ?? 'Senjata';
        $inventoryWeapon->delete();

        return redirect()->route('inventory.weapons.index', ['account_id' => $accountId])
            ->with('success', "{$name} berhasil dihapus dari inventori!");
    }

    /**
     * Sinkronisasi data senjata dari HoYoLAB microservice ke inventori akun game
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
                ->with('error', 'Tidak ada data karakter/senjata ditemukan dari akun HoYoLAB.');
        }

        $syncedCount = 0;

        foreach ($characters as $charData) {
            if (empty($charData['weapon']) || empty($charData['weapon']['name'])) {
                continue;
            }

            $wData = $charData['weapon'];
            $wName = $wData['name'];
            $wSlug = Str::slug($wName);

            // Cari atau buat karakter di database untuk mapping equipped
            $charName = $charData['name'];
            $character = Character::firstOrCreate(
                ['slug' => Str::slug($charName)],
                [
                    'name'        => $charName,
                    'element'     => ucfirst($charData['element'] ?? 'Pyro'),
                    'weapon_type' => $wData['type'] ?? 'Sword',
                    'rarity'      => $charData['rarity'] ?? 4,
                    'icon_url'    => $charData['image'] ?? null,
                ]
            );

            // Cari atau buat master Weapon
            $weapon = Weapon::where('slug', $wSlug)
                ->orWhere('name', $wName)
                ->first();

            $validType = in_array($wData['type'] ?? '', ['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'])
                ? $wData['type']
                : ($character->weapon_type ?? 'Sword');

            if (!$weapon) {
                $weapon = Weapon::create([
                    'name'     => $wName,
                    'slug'     => $wSlug,
                    'type'     => $validType,
                    'rarity'   => $wData['rarity'] ?? 4,
                    'icon_url' => !empty($wData['icon']) ? $wData['icon'] : null,
                ]);
            } else {
                $updates = [];
                if (empty($weapon->icon_url) && !empty($wData['icon'])) {
                    $updates['icon_url'] = $wData['icon'];
                }
                if (!in_array($weapon->type, ['Sword', 'Claymore', 'Polearm', 'Bow', 'Catalyst'])) {
                    $updates['type'] = $validType;
                }
                if (!empty($updates)) {
                    $weapon->update($updates);
                }
            }

            $wLevel = (int) ($wData['level'] ?? 1);
            $wAscension = match (true) {
                $wLevel > 80 => 6,
                $wLevel > 70 => 5,
                $wLevel > 60 => 4,
                $wLevel > 50 => 3,
                $wLevel > 40 => 2,
                $wLevel > 20 => 1,
                default      => 0,
            };

            // Simpan atau update ke inventory_weapons
            $existingEquipped = InventoryWeapon::where('game_account_id', $account->id)
                ->where('equipped_character_id', $character->id)
                ->first();

            if ($existingEquipped) {
                $existingEquipped->update([
                    'weapon_id'  => $weapon->id,
                    'level'      => $wLevel,
                    'ascension'  => $wAscension,
                    'refinement' => (int) ($wData['refinement'] ?? 1),
                    'scanned_at' => now(),
                ]);
            } else {
                InventoryWeapon::create([
                    'game_account_id'       => $account->id,
                    'weapon_id'             => $weapon->id,
                    'level'                 => $wLevel,
                    'ascension'             => $wAscension,
                    'refinement'            => (int) ($wData['refinement'] ?? 1),
                    'equipped_character_id' => $character->id,
                    'scanned_at'            => now(),
                ]);
            }

            $syncedCount++;
        }

        // Simpan credentials jika dipilih
        if ($request->has('save_credentials')) {
            $account->update([
                'ltuid_v2'  => $ltuid,
                'ltoken_v2' => $ltoken,
            ]);
        }

        $account->update(['last_synced_at' => now()]);

        return redirect()->route('inventory.weapons.index', ['account_id' => $account->id])
            ->with('success', "Berhasil mensinkronkan {$syncedCount} senjata dari HoYoLAB untuk akun {$account->nickname}!");
    }

    /**
     * Sinkronisasi data senjata dari Enka.Network API via UID
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
            $result = $this->enka->syncWeaponsFromEnka($account, $uid);

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return redirect()->route('inventory.weapons.index', ['account_id' => $account->id])
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', 'Gagal sync dari Enka.Network: ' . $e->getMessage());
        }
    }
}
