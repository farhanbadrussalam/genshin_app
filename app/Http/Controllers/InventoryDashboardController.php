<?php

namespace App\Http\Controllers;

use App\Models\GameAccount;
use App\Models\InventoryCharacter;
use App\Models\InventoryWeapon;
use App\Models\InventoryArtifact;
use App\Models\InventoryMaterial;
use App\Models\task;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class InventoryDashboardController extends Controller
{
    /**
     * Tampilan utama Dashboard Overview & Ringkasan Inventori Akun
     */
    public function index(Request $request): View
    {
        $accounts = GameAccount::orderBy('nickname')->get();

        $activeAccountId = $request->query('account_id')
            ? (int) $request->query('account_id')
            : ($accounts->first()?->id ?? null);

        $activeAccount = $activeAccountId
            ? $accounts->firstWhere('id', $activeAccountId)
            : null;

        if (!$activeAccount) {
            return view('inventory.dashboard', [
                'accounts'              => $accounts,
                'activeAccount'         => null,
                'stats'                 => null,
                'topCharactersShowcase' => collect(),
                'fiveStarWeapons'       => collect(),
            ]);
        }

        $accId = $activeAccount->id;

        // ── 1. STATS KARAKTER ───────────────────────────────────────────────
        $invCharacters = InventoryCharacter::with('character')
            ->where('game_account_id', $accId)
            ->get();

        $totalChars = $invCharacters->count();
        $char5Stars = $invCharacters->filter(fn($c) => ($c->character?->rarity ?? 0) === 5)->count();
        $char4Stars = $invCharacters->filter(fn($c) => ($c->character?->rarity ?? 0) === 4)->count();
        $charMaxLv  = $invCharacters->filter(fn($c) => $c->level >= 90)->count();
        $charC6     = $invCharacters->filter(fn($c) => $c->constellation >= 6)->count();

        // Distribusi elemen
        $elementCounts = [
            'Pyro'    => 0,
            'Hydro'   => 0,
            'Anemo'   => 0,
            'Electro' => 0,
            'Dendro'  => 0,
            'Cryo'    => 0,
            'Geo'     => 0,
        ];
        foreach ($invCharacters as $ic) {
            $el = ucfirst(strtolower($ic->character?->element ?? ''));
            if (isset($elementCounts[$el])) {
                $elementCounts[$el]++;
            }
        }

        // ── 2. STATS SENJATA ────────────────────────────────────────────────
        $invWeapons = InventoryWeapon::with(['weapon', 'equippedCharacter'])
            ->where('game_account_id', $accId)
            ->get();

        $totalWeapons   = $invWeapons->count();
        $weapon5Stars   = $invWeapons->filter(fn($w) => ($w->weapon?->rarity ?? 0) === 5)->count();
        $weapon4Stars   = $invWeapons->filter(fn($w) => ($w->weapon?->rarity ?? 0) === 4)->count();
        $weaponMaxLv    = $invWeapons->filter(fn($w) => $w->level >= 90)->count();
        $weaponR5       = $invWeapons->filter(fn($w) => $w->refinement >= 5)->count();
        $weaponEquipped = $invWeapons->filter(fn($w) => !is_null($w->equipped_character_id))->count();

        $weaponTypeCounts = [
            'Sword'    => 0,
            'Claymore' => 0,
            'Polearm'  => 0,
            'Bow'      => 0,
            'Catalyst' => 0,
        ];
        foreach ($invWeapons as $iw) {
            $wt = ucfirst(strtolower($iw->weapon?->type ?? ''));
            if (isset($weaponTypeCounts[$wt])) {
                $weaponTypeCounts[$wt]++;
            }
        }

        // ── 3. STATS ARTIFAK ────────────────────────────────────────────────
        $invArtifacts = InventoryArtifact::with(['artifactSet', 'equippedCharacter'])
            ->where('game_account_id', $accId)
            ->get();

        $totalArtifacts = $invArtifacts->count();
        $artMaxLv       = $invArtifacts->filter(fn($a) => $a->level >= 20)->count();
        $art5Stars      = $invArtifacts->filter(fn($a) => $a->rarity === 5)->count();
        $artEquipped    = $invArtifacts->filter(fn($a) => !is_null($a->equipped_character_id))->count();

        $slotCounts = [
            'flower'  => 0,
            'plume'   => 0,
            'sands'   => 0,
            'goblet'  => 0,
            'circlet' => 0,
        ];
        foreach ($invArtifacts as $ia) {
            if (isset($slotCounts[$ia->slot_key])) {
                $slotCounts[$ia->slot_key]++;
            }
        }

        // Top 5 Set Artefak
        $topSets = $invArtifacts->groupBy('artifact_set_id')
            ->map(function ($group) {
                return [
                    'set'   => $group->first()->artifactSet,
                    'count' => $group->count(),
                ];
            })
            ->filter(fn($item) => !is_null($item['set']))
            ->sortByDesc('count')
            ->take(5);

        // ── 4. STATS MATERIAL & TASK ─────────────────────────────────────────
        $invMaterials = InventoryMaterial::where('game_account_id', $accId)->get();
        $totalMaterialTypes    = $invMaterials->where('amount', '>', 0)->count();
        $totalMaterialQuantity = $invMaterials->sum('amount');

        $activeTasksCount = task::where('game_account_id', $accId)
            ->where('status', '!=', 'complete')
            ->count();

        // ── 5. SHOWCASE KARAKTER TERATAS ─────────────────────────────────────
        $topCharacters = $invCharacters->sortByDesc(function ($c) {
            $rarity = $c->character?->rarity ?? 4;
            return ($c->level * 1000) + ($rarity * 100) + ($c->constellation * 10) + $c->ascension;
        })->take(8);

        $weaponsByChar = $invWeapons->filter(fn($w) => $w->equipped_character_id)->keyBy('equipped_character_id');
        $artCountByChar = $invArtifacts->filter(fn($a) => $a->equipped_character_id)->groupBy('equipped_character_id');

        $topCharactersShowcase = $topCharacters->map(function ($ic) use ($weaponsByChar, $artCountByChar) {
            $charId = $ic->character_id;
            return [
                'inventory_character' => $ic,
                'character'           => $ic->character,
                'equipped_weapon'     => $weaponsByChar->get($charId),
                'artifacts_count'     => $artCountByChar->has($charId) ? $artCountByChar->get($charId)->count() : 0,
            ];
        });

        // ── 6. 5-STAR WEAPONS SHOWCASE ──────────────────────────────────────
        $fiveStarWeapons = $invWeapons->filter(fn($w) => ($w->weapon?->rarity ?? 0) === 5)
            ->sortByDesc('level')
            ->take(8);

        // ── 7. MATERIAL FARMING BUKA HARI INI (HANYA YANG ADA JADWAL TASK) ──
        $today = Carbon::now(config('app.timezone'))->locale('id');
        $indonesianDays = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu'];
        $todayLabelId = strtolower($indonesianDays[$today->dayOfWeekIso]);
        $todayLabelEn = strtolower($today->englishDayOfWeek);
        $isSunday = ($today->dayOfWeekIso === 7);

        // Ambil task aktif HANYA untuk akun game yang sedang aktif dipilih ($accId)
        $activeTasks = task::with(['sub_task.material'])
            ->where('game_account_id', $accId)
            ->where('status', '!=', 'complete')
            ->get();

        $taskNeededByMaterial = [];
        foreach ($activeTasks as $t) {
            foreach ($t->sub_task as $st) {
                if (!$st->material) continue;
                // Lewati sub-task yang sudah selesai dikerjakan / dicentang checklist
                if ($st->is_completed) continue;

                $matId = $st->material->id;
                $taskNeededByMaterial[$matId] ??= [
                    'tasks' => [],
                    'required' => 0,
                    'sub_tasks' => [],
                ];
                $taskNeededByMaterial[$matId]['tasks'][] = html_entity_decode($t->nama_task, ENT_QUOTES, 'UTF-8');
                $taskNeededByMaterial[$matId]['required'] += (int) $st->amount;
                $taskNeededByMaterial[$matId]['sub_tasks'][] = [
                    'id' => $st->id,
                    'task_id' => $t->id,
                    'task_name' => html_entity_decode($t->nama_task, ENT_QUOTES, 'UTF-8'),
                ];
            }
        }

        // Ambil stok material dari inventori activeAccount
        $inventoryByMaterial = InventoryMaterial::where('game_account_id', $accId)->pluck('amount', 'material_id');

        // Ambil material yang memiliki dropdomain (memiliki jadwal rotasi domain)
        $scheduledMaterials = \App\Models\material::whereNotNull('dropdomain')
            ->where('dropdomain', '!=', '')
            ->get();

        // Filter material terjadwal yang BUKA HARI INI
        $todayMaterials = $scheduledMaterials->filter(function ($mat) use ($isSunday, $todayLabelId, $todayLabelEn) {
            if ($isSunday) return true;
            $days = is_string($mat->daysofweek) ? json_decode($mat->daysofweek, true) : $mat->daysofweek;
            if (!is_array($days)) return false;
            foreach ($days as $d) {
                $dl = strtolower(trim($d));
                if (str_contains($dl, $todayLabelId) || str_contains($dl, $todayLabelEn)) {
                    return true;
                }
            }
            return false;
        });

        // Filter: HANYA tampilkan material & domain yang ADA DI TASK AKTIF user dan BUKA HARI INI
        // Sisanya (yang tidak ada jadwal atau bukan bagian task) tidak ditampilkan
        $todayDomains = $todayMaterials->groupBy('dropdomain')->map(function ($items, $domainName) use ($taskNeededByMaterial, $inventoryByMaterial) {
            $isMastery = str_contains($domainName, 'Mastery');
            $type = $isMastery ? 'talent' : 'weapon';
            $typeName = $isMastery ? 'Buku Talenta' : 'Material Senjata';

            // Filter HANYA material yang dibutuhkan oleh task aktif
            $materialsList = $items->filter(function ($mat) use ($taskNeededByMaterial) {
                return isset($taskNeededByMaterial[$mat->id]);
            })->map(function ($mat) use ($taskNeededByMaterial, $inventoryByMaterial) {
                $owned = $inventoryByMaterial->has($mat->id)
                    ? (int) $inventoryByMaterial->get($mat->id)
                    : (int) $mat->amount;
                $neededInfo = $taskNeededByMaterial[$mat->id];
                $required = (int) $neededInfo['required'];
                $missing = max(0, $required - $owned);
                $tasks = array_values(array_unique($neededInfo['tasks']));
                $subTasks = $neededInfo['sub_tasks'] ?? [];

                return [
                    'material' => $mat,
                    'owned' => $owned,
                    'required' => $required,
                    'missing' => $missing,
                    'is_needed_by_task' => true,
                    'tasks' => $tasks,
                    'sub_tasks' => $subTasks,
                ];
            })->sortByDesc('material.rarity')->values();

            return [
                'domain' => $domainName,
                'type' => $type,
                'type_name' => $typeName,
                'materials' => $materialsList,
                'has_needed_tasks' => $materialsList->isNotEmpty(),
            ];
        })->filter(function ($dom) {
            // Hanya domain yang dibutuhkan task aktif yang memiliki jadwal buka hari ini
            return $dom['has_needed_tasks'];
        })->values();

        $todayDomainsCount = $todayDomains->count();
        $todayTaskDomainsCount = $todayDomainsCount;
        $todayDateFormatted = $today->translatedFormat('l, d F Y');
        $todayDayName = $today->translatedFormat('l');

        return view('inventory.dashboard', [
            'todayDomains'          => $todayDomains,
            'todayDomainsCount'     => $todayDomainsCount,
            'todayTaskDomainsCount' => $todayTaskDomainsCount,
            'todayDateFormatted'    => $todayDateFormatted,
            'todayDayName'          => $todayDayName,
            'accounts'              => $accounts,
            'activeAccount'         => $activeAccount,
            'stats'                 => [
                'total_characters'     => $totalChars,
                'char_5_stars'         => $char5Stars,
                'char_4_stars'         => $char4Stars,
                'char_max_level'       => $charMaxLv,
                'char_c6'              => $charC6,
                'element_counts'       => $elementCounts,

                'total_weapons'        => $totalWeapons,
                'weapon_5_stars'       => $weapon5Stars,
                'weapon_4_stars'       => $weapon4Stars,
                'weapon_max_level'     => $weaponMaxLv,
                'weapon_r5'            => $weaponR5,
                'weapon_equipped'      => $weaponEquipped,
                'weapon_type_counts'   => $weaponTypeCounts,

                'total_artifacts'      => $totalArtifacts,
                'art_max_level'        => $artMaxLv,
                'art_5_stars'          => $art5Stars,
                'art_equipped'         => $artEquipped,
                'slot_counts'          => $slotCounts,
                'top_sets'             => $topSets,

                'total_material_types' => $totalMaterialTypes,
                'total_material_qty'   => $totalMaterialQuantity,
                'active_tasks_count'   => $activeTasksCount,
            ],
            'topCharactersShowcase' => $topCharactersShowcase,
            'fiveStarWeapons'       => $fiveStarWeapons,
        ]);
    }

    /**
     * ⚡ 1 TOMBOL SYNC SEMUA INVENTORI (Karakter, Senjata, Artefak)
     */
    public function syncAll(
        Request $request,
        \App\Services\EnkaNetworkService $enkaService,
        \App\Services\HoyoLabMicroservice $hoyoLabService
    ) {
        $validated = $request->validate([
            'account_id'   => 'required|exists:game_accounts,id',
            'source'       => 'nullable|string|in:enka,hoyolab',
            'override_uid' => 'nullable|string|max:20',
            'ltuid_v2'     => 'nullable|string',
            'ltoken_v2'    => 'nullable|string',
            'save_credentials' => 'nullable',
        ]);

        $account = GameAccount::findOrFail($validated['account_id']);
        $source  = $validated['source'] ?? 'enka';
        $uid     = !empty($validated['override_uid']) ? trim($validated['override_uid']) : $account->uid;

        try {
            if ($source === 'enka') {
                $result = $enkaService->syncAllInventoryFromEnka($account, $uid);
            } else {
                // Sumber HoYoLAB via microservice
                $reqLtuid = trim($validated['ltuid_v2'] ?? '');
                $reqLtoken = trim($validated['ltoken_v2'] ?? '');
                
                $ltuid = !empty($reqLtuid) ? $reqLtuid : $account->ltuid_v2;
                $ltoken = !empty($reqLtoken) ? $reqLtoken : $account->ltoken_v2;

                if (empty($ltuid) || empty($ltoken)) {
                    $result = [
                        'success' => false,
                        'message' => 'Akun belum memiliki kredensial Cookie HoYoLAB yang lengkap.',
                    ];
                } else {
                    if (!empty($validated['save_credentials'])) {
                        $account->update([
                            'ltuid_v2' => $ltuid,
                            'ltoken_v2' => $ltoken,
                        ]);
                    }

                    $cookies = [
                        'ltuid_v2'  => $ltuid,
                        'ltmid_v2'  => $account->ltmid_v2 ?: $ltuid,
                        'ltoken_v2' => $ltoken,
                        'cookie_token_v2' => $account->cookie_token_v2,
                        'account_id_v2'   => $account->account_id_v2,
                    ];
                    $hoyoData = $hoyoLabService->syncAll($cookies, (int) $account->uid, $account->server);

                    $syncedChars = count($hoyoData['characters'] ?? []);
                    $syncedWeapons = count($hoyoData['weapons'] ?? []);
                    $syncedArts = count($hoyoData['artifacts'] ?? []);

                    $account->update(['last_synced_at' => now()]);

                    $result = [
                        'success'           => true,
                        'message'           => "Berhasil mensinkronkan {$syncedChars} karakter, {$syncedWeapons} senjata, dan {$syncedArts} artifact dari HoYoLAB!",
                        'synced_characters' => $syncedChars,
                        'synced_weapons'    => $syncedWeapons,
                        'synced_artifacts'  => $syncedArts,
                    ];
                }
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return redirect()
                ->route('inventory.dashboard', ['account_id' => $account->id])
                ->with($result['success'] ? 'success' : 'error', $result['message']);

        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal sinkronisasi inventori: ' . $e->getMessage());
        }
    }
}

