<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryCharacter;
use App\Models\InventoryWeapon;
use App\Models\Weapon;
use App\Services\EnkaNetworkService;
use App\Services\HoyoLabMicroservice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryCharacterController extends Controller
{
    public function __construct(
        private readonly HoyoLabMicroservice $hoyolab,
        private readonly EnkaNetworkService $enka
    ) {}

    /**
     * Tampilkan inventori karakter berdasarkan akun game yang dipilih
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::where('game', 'genshin_impact')
            ->orderBy('nickname')
            ->get();

        // Jika tidak ada akun genshin_impact, coba ambil semua akun
        if ($accounts->isEmpty()) {
            $accounts = GameAccount::orderBy('nickname')->get();
        }

        // Tentukan akun aktif
        $selectedAccountId = $request->input('account_id', $accounts->first()?->id);
        $activeAccount = $accounts->firstWhere('id', $selectedAccountId) ?? $accounts->first();

        $inventoryCharacters = collect();
        $stats = [
            'total'     => 0,
            'max_level' => 0, // Level 90
            'c6'        => 0, // Constellation 6
            'crowns'    => 0, // Talent 10
        ];

        if ($activeAccount) {
            $query = InventoryCharacter::with('character')
                ->where('game_account_id', $activeAccount->id);

            // Filter Search Nama Karakter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('character', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Filter Elemen
            if ($request->filled('element') && $request->input('element') !== 'all') {
                $element = $request->input('element');
                $query->whereHas('character', function ($q) use ($element) {
                    $q->where('element', $element);
                });
            }

            // Filter Rarity
            if ($request->filled('rarity') && $request->input('rarity') !== 'all') {
                $rarity = (int)$request->input('rarity');
                $query->whereHas('character', function ($q) use ($rarity) {
                    $q->where('rarity', $rarity);
                });
            }

            // Sorting
            $sort = $request->input('sort', 'level_desc');
            match ($sort) {
                'level_asc'   => $query->orderBy('level', 'asc'),
                'const_desc'  => $query->orderBy('constellation', 'desc'),
                'name_asc'    => $query->join('characters', 'inventory_characters.character_id', '=', 'characters.id')
                                       ->orderBy('characters.name', 'asc')
                                       ->select('inventory_characters.*'),
                default       => $query->orderBy('level', 'desc')->orderBy('ascension', 'desc'),
            };

            $inventoryCharacters = $query->paginate(24)->withQueryString();

            // Statistik akun aktif
            $allChars = InventoryCharacter::where('game_account_id', $activeAccount->id)->get();
            $stats['total'] = $allChars->count();
            $stats['max_level'] = $allChars->where('level', '>=', 90)->count();
            $stats['c6'] = $allChars->where('constellation', 6)->count();
            $stats['crowns'] = $allChars->filter(function ($c) {
                return $c->talent_attack >= 10 || $c->talent_skill >= 10 || $c->talent_burst >= 10;
            })->count();
        }

        // Daftar karakter yang belum dimiliki oleh akun ini (untuk dropdown modal tambah)
        $ownedCharacterIds = $activeAccount 
            ? InventoryCharacter::where('game_account_id', $activeAccount->id)->pluck('character_id')
            : collect();

        $availableCharacters = Character::whereNotIn('id', $ownedCharacterIds)
            ->orderBy('name')
            ->get();

        return view('inventory.characters', [
            'title'               => 'Inventori Karakter',
            'accounts'            => $accounts,
            'activeAccount'       => $activeAccount,
            'inventoryCharacters' => $inventoryCharacters,
            'availableCharacters' => $availableCharacters,
            'stats'               => $stats,
            'filters'             => $request->only(['account_id', 'search', 'element', 'rarity', 'sort']),
        ]);
    }

    /**
     * Simpan karakter ke inventori akun
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'character_id'    => 'required|exists:characters,id',
            'level'           => 'required|integer|min:1|max:90',
            'ascension'       => 'required|integer|min:0|max:6',
            'constellation'   => 'required|integer|min:0|max:6',
            'talent_attack'   => 'required|integer|min:1|max:10',
            'talent_skill'    => 'required|integer|min:1|max:10',
            'talent_burst'    => 'required|integer|min:1|max:10',
            'notes'           => 'nullable|string|max:500',
        ]);

        // Cek duplikasi
        $exists = InventoryCharacter::where('game_account_id', $validated['game_account_id'])
            ->where('character_id', $validated['character_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Karakter ini sudah ada di inventori akun tersebut!');
        }

        InventoryCharacter::create($validated);

        return redirect()->route('inventory.characters.index', ['account_id' => $validated['game_account_id']])
            ->with('success', 'Karakter berhasil ditambahkan ke inventori!');
    }

    /**
     * Update data karakter di inventori
     */
    public function update(Request $request, InventoryCharacter $inventoryCharacter): RedirectResponse
    {
        $validated = $request->validate([
            'level'         => 'required|integer|min:1|max:90',
            'ascension'     => 'required|integer|min:0|max:6',
            'constellation' => 'required|integer|min:0|max:6',
            'talent_attack' => 'required|integer|min:1|max:15',
            'talent_skill'  => 'required|integer|min:1|max:15',
            'talent_burst'  => 'required|integer|min:1|max:15',
            'notes'         => 'nullable|string|max:500',
        ]);

        $inventoryCharacter->update($validated);

        return redirect()->route('inventory.characters.index', ['account_id' => $inventoryCharacter->game_account_id])
            ->with('success', 'Data karakter berhasil diperbarui!');
    }

    /**
     * Hapus karakter dari inventori akun
     */
    public function destroy(InventoryCharacter $inventoryCharacter): RedirectResponse
    {
        $accountId = $inventoryCharacter->game_account_id;
        $charName = $inventoryCharacter->character?->name ?? 'Karakter';
        $inventoryCharacter->delete();

        return redirect()->route('inventory.characters.index', ['account_id' => $accountId])
            ->with('success', "{$charName} berhasil dihapus dari inventori!");
    }

    /**
     * Sinkronisasi data karakter dari HoYoLAB microservice ke inventori akun game
     */
    public function syncFromHoyoLab(Request $request): RedirectResponse
    {
        $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'ltuid_v2'        => 'nullable|string',
            'ltoken_v2'       => 'nullable|string',
            'save_credentials'=> 'nullable',
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
                ->with('error', 'Tidak ada karakter yang ditemukan dari akun HoYoLAB tersebut. Pastikan Battle Chronicle bersifat publik di setelan HoYoLAB.');
        }

        $syncedCount = 0;

        foreach ($characters as $charData) {
            $charName = $charData['name'];
            $slug = Str::slug($charName);

            // Cari karakter di master data berdasarkan slug atau name
            $character = Character::where('slug', $slug)
                ->orWhere('name', $charName)
                ->first();

            // Jika belum ada di master data, buat master baru
            if (!$character) {
                $character = Character::create([
                    'name'        => $charName,
                    'slug'        => $slug,
                    'element'     => ucfirst($charData['element'] ?? 'Pyro'),
                    'weapon_type' => $charData['weapon']['name'] ?? 'Sword',
                    'rarity'      => $charData['rarity'] ?? 4,
                    'icon_url'    => $charData['image'] ?? null,
                ]);
            }

            // Hitung perkiraan ascension phase berdasarkan level
            $level = (int) ($charData['level'] ?? 1);
            $ascension = match (true) {
                $level > 80 => 6,
                $level > 70 => 5,
                $level > 60 => 4,
                $level > 50 => 3,
                $level > 40 => 2,
                $level > 20 => 1,
                default     => 0,
            };

            // Simpan atau update ke tabel inventory_characters
            InventoryCharacter::updateOrCreate(
                [
                    'game_account_id' => $account->id,
                    'character_id'    => $character->id,
                ],
                [
                    'level'         => $level,
                    'ascension'     => $ascension,
                    'constellation' => (int) ($charData['constellation'] ?? 0),
                    'scanned_at'    => now(),
                ]
            );

            // Simpan atau update senjata yang dipakai karakter ke tabel inventory_weapons
            if (!empty($charData['weapon']) && !empty($charData['weapon']['name'])) {
                $wData = $charData['weapon'];
                $wName = $wData['name'];
                $wSlug = Str::slug($wName);

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

                // Cek apakah karakter ini sudah memiliki senjata yang di-equip di inventory_weapons
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
            }

            $syncedCount++;
        }

        // Simpan credentials jika user mencentang checkbox
        if ($request->has('save_credentials')) {
            $account->update([
                'ltuid_v2'  => $ltuid,
                'ltoken_v2' => $ltoken,
            ]);
        }

        $account->update(['last_synced_at' => now()]);

        return redirect()->route('inventory.characters.index', ['account_id' => $account->id])
            ->with('success', "Berhasil mensinkronkan {$syncedCount} karakter dari HoYoLAB untuk akun {$account->nickname}!");
    }

    /**
     * Sinkronisasi data karakter dari Enka.Network API via UID
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
            $result = $this->enka->syncCharactersFromEnka($account, $uid);

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return redirect()->route('inventory.characters.index', ['account_id' => $account->id])
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', 'Gagal sync dari Enka.Network: ' . $e->getMessage());
        }
    }
}
