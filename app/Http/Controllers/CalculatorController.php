<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryCharacter;
use App\Models\InventoryMaterial;
use App\Models\InventoryWeapon;
use App\Models\material as Material;
use App\Models\subTask;
use App\Models\task as Task;
use App\Models\Weapon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    /**
     * Tampilkan halaman kalkulator upgrade
     */
    public function index(Request $request): View
    {
        $gameAccounts = GameAccount::where('game', 'genshin_impact')->orderBy('id', 'asc')->get();
        $accountId = $request->get('account_id', session('active_game_account_id'));
        $activeAccount = $gameAccounts->firstWhere('id', $accountId) ?? $gameAccounts->first();

        if ($activeAccount) {
            session(['active_game_account_id' => $activeAccount->id]);
        }

        // Ambil inventori karakter & senjata milik akun ini
        $inventoryCharacters = [];
        $inventoryWeapons = [];

        if ($activeAccount) {
            $inventoryCharacters = InventoryCharacter::with('character')
                ->where('game_account_id', $activeAccount->id)
                ->orderBy('level', 'desc')
                ->get();

            $inventoryWeapons = InventoryWeapon::with('weapon')
                ->where('game_account_id', $activeAccount->id)
                ->orderBy('level', 'desc')
                ->get();
        }

        // Fallback master karakter & senjata jika inventori akun belum lengkap
        $allCharacters = Character::orderBy('rarity', 'desc')->orderBy('name', 'asc')->get();
        $allWeapons = Weapon::orderBy('rarity', 'desc')->orderBy('name', 'asc')->get();

        return view('calculator.index', [
            'title'               => 'Kalkulator Upgrade',
            'gameAccounts'        => $gameAccounts,
            'activeAccount'       => $activeAccount,
            'inventoryCharacters' => $inventoryCharacters,
            'inventoryWeapons'    => $inventoryWeapons,
            'allCharacters'       => $allCharacters,
            'allWeapons'          => $allWeapons,
        ]);
    }

    /**
     * Hitung kebutuhan material upgrade
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'type'          => 'required|in:character,weapon',
            'name'          => 'required|string',
            'current_level' => 'required|integer|min:1|max:90',
            'target_level'  => 'required|integer|min:1|max:90|gte:current_level',
            'current_asc'   => 'nullable|integer|min:0|max:6',
            'target_asc'    => 'nullable|integer|min:0|max:6',
        ]);

        $type = $request->type;
        $name = $request->name;
        $curLvl = (int) $request->current_level;
        $tarLvl = (int) $request->target_level;

        $accountId = $request->get('account_id', session('active_game_account_id'));
        $activeAccount = GameAccount::find($accountId) ?? GameAccount::first();

        // Peta stok material di akun game
        $invMap = [];
        if ($activeAccount) {
            $invMaterials = InventoryMaterial::where('game_account_id', $activeAccount->id)->get();
            foreach ($invMaterials as $inv) {
                $invMap[$inv->material_id] = $inv->amount;
            }
        }

        $totalMaterials = []; // [itemName => ['count' => x, 'type' => 'asc'|'talent'|'exp']]

        if ($type === 'character') {
            $curAsc = (int) ($request->current_asc ?? $this->calcAscensionFromLevel($curLvl));
            $tarAsc = (int) ($request->target_asc ?? $this->calcAscensionFromLevel($tarLvl));

            // 1. Fetch character costs dari Genshin-db API
            $charUrl = "https://genshin-db-api.vercel.app/api/v5/characters?query=" . urlencode($name) . "&resultLanguage=Indonesia";
            $resChar = Http::timeout(8)->get($charUrl);

            if ($resChar->successful() && isset($resChar['costs'])) {
                $costs = $resChar['costs'];
                for ($a = $curAsc + 1; $a <= $tarAsc; $a++) {
                    $key = "ascend{$a}";
                    if (isset($costs[$key]) && is_array($costs[$key])) {
                        foreach ($costs[$key] as $c) {
                            $cName = $c['name'];
                            $cCount = (int) $c['count'];
                            if (!isset($totalMaterials[$cName])) {
                                $totalMaterials[$cName] = 0;
                            }
                            $totalMaterials[$cName] += $cCount;
                        }
                    }
                }
            }

            // 2. Talent costs jika ada
            $curTalents = $request->input('current_talents', [1, 1, 1]);
            $tarTalents = $request->input('target_talents', [1, 1, 1]);

            $needsTalent = false;
            foreach ($tarTalents as $idx => $tarT) {
                if ((int) $tarT > (int) ($curTalents[$idx] ?? 1)) {
                    $needsTalent = true;
                    break;
                }
            }

            if ($needsTalent) {
                $talentUrl = "https://genshin-db-api.vercel.app/api/v5/talents?query=" . urlencode($name) . "&resultLanguage=Indonesia";
                $resTalent = Http::timeout(8)->get($talentUrl);

                if ($resTalent->successful() && isset($resTalent['costs'])) {
                    $talentCosts = $resTalent['costs'];
                    for ($t = 0; $t < 3; $t++) {
                        $cT = (int) ($curTalents[$t] ?? 1);
                        $tT = (int) ($tarTalents[$t] ?? 1);

                        for ($lvl = $cT + 1; $lvl <= $tT; $lvl++) {
                            $lvlKey = "lvl{$lvl}";
                            if (isset($talentCosts[$lvlKey]) && is_array($talentCosts[$lvlKey])) {
                                foreach ($talentCosts[$lvlKey] as $tc) {
                                    $tcName = $tc['name'];
                                    $tcCount = (int) $tc['count'];
                                    if (!isset($totalMaterials[$tcName])) {
                                        $totalMaterials[$tcName] = 0;
                                    }
                                    $totalMaterials[$tcName] += $tcCount;
                                }
                            }
                        }
                    }
                }
            }

            // 3. Estimasi Hero's Wit & Level Up Mora
            $expBooksNeeded = (int) round(($tarLvl - $curLvl) * 4.65);
            if ($expBooksNeeded > 0) {
                $bookName = "Hero's Wit";
                $totalMaterials[$bookName] = ($totalMaterials[$bookName] ?? 0) + $expBooksNeeded;
            }
        } else {
            // Weapon Upgrade
            $curAsc = (int) ($request->current_asc ?? $this->calcAscensionFromLevel($curLvl));
            $tarAsc = (int) ($request->target_asc ?? $this->calcAscensionFromLevel($tarLvl));

            $wepUrl = "https://genshin-db-api.vercel.app/api/v5/weapons?query=" . urlencode($name) . "&resultLanguage=Indonesia";
            $resWep = Http::timeout(8)->get($wepUrl);

            if ($resWep->successful() && isset($resWep['costs'])) {
                $costs = $resWep['costs'];
                for ($a = $curAsc + 1; $a <= $tarAsc; $a++) {
                    $key = "ascend{$a}";
                    if (isset($costs[$key]) && is_array($costs[$key])) {
                        foreach ($costs[$key] as $c) {
                            $cName = $c['name'];
                            $cCount = (int) $c['count'];
                            if (!isset($totalMaterials[$cName])) {
                                $totalMaterials[$cName] = 0;
                            }
                            $totalMaterials[$cName] += $cCount;
                        }
                    }
                }
            }

            // Estimasi Mystic Enhancement Ore
            $oreNeeded = (int) round(($tarLvl - $curLvl) * 7.5);
            if ($oreNeeded > 0) {
                $oreName = "Mystic Enhancement Ore";
                $totalMaterials[$oreName] = ($totalMaterials[$oreName] ?? 0) + $oreNeeded;
            }
        }

        // Cocokkan data material dengan master tabel `materials`
        $resultItems = [];
        foreach ($totalMaterials as $matName => $requiredCount) {
            // Cari material di DB
            $dbMat = Material::where('name', $matName)
                ->orWhere('name', 'like', "%{$matName}%")
                ->first();

            $matId = $dbMat ? $dbMat->id : null;
            $owned = 0;

            if ($matId) {
                $owned = $invMap[$matId] ?? ($dbMat->amount ?? 0);
            }

            $remaining = max(0, $requiredCount - $owned);

            $resultItems[] = [
                'material_id' => $matId,
                'name'        => $matName,
                'image'       => $dbMat?->images ?: '',
                'rarity'      => $dbMat?->rarity ?: 3,
                'required'    => $requiredCount,
                'owned'       => $owned,
                'remaining'   => $remaining,
                'status_ok'   => $owned >= $requiredCount,
            ];
        }

        // Urutkan item: Mora & EXP di atas, sisanya berdasarkan rarity desc
        usort($resultItems, function ($a, $b) {
            if ($a['name'] === 'Mora') return -1;
            if ($b['name'] === 'Mora') return 1;
            return $b['rarity'] <=> $a['rarity'];
        });

        return response()->json([
            'success'   => true,
            'name'      => $name,
            'type'      => $type,
            'cur_lvl'   => $curLvl,
            'tar_lvl'   => $tarLvl,
            'materials' => $resultItems,
        ]);
    }

    /**
     * Konversi kalkulasi upgrade menjadi Task & Subtask
     */
    public function createTask(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'task_name'   => 'required|string|max:255',
            'image_url'   => 'nullable|string',
            'jenis'       => 'required|in:stat,weapon,talent',
            'prioritas'   => 'required|integer|min:1',
            'materials'   => 'required|array|min:1',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.amount'      => 'required|integer|min:1',
        ]);

        $accountId = $request->input('account_id', session('active_game_account_id'));
        $task = Task::create([
            'game_account_id' => $accountId,
            'nama_task' => $request->task_name,
            'images'    => $request->image_url ?: 'https://placehold.co/100x100/1e2337/gold?text=Task',
            'jenis'     => $request->jenis,
            'status'    => 'start',
            'prioritas' => $request->prioritas,
        ]);

        foreach ($request->materials as $matItem) {
            subTask::create([
                'task_id'     => $task->id,
                'material_id' => $matItem['material_id'],
                'amount'      => (int) $matItem['amount'],
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'task_id'  => $task->id,
                'redirect' => route('task.index', ['account_id' => $accountId]),
            ]);
        }

        return redirect()->route('task.index', ['account_id' => $accountId])->with('success', "Task \"{$task->nama_task}\" berhasil dibuat dari Kalkulator!");
    }

    private function calcAscensionFromLevel(int $lvl): int
    {
        if ($lvl <= 20) return 0;
        if ($lvl <= 40) return 1;
        if ($lvl <= 50) return 2;
        if ($lvl <= 60) return 3;
        if ($lvl <= 70) return 4;
        if ($lvl <= 80) return 5;
        return 6;
    }
}
