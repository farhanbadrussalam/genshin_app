<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\GameAccount;
use App\Models\InventoryMaterial;
use App\Models\material as Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryMaterialController extends Controller
{
    /**
     * Tampilkan daftar inventori material akun game.
     */
    public function index(Request $request): View
    {
        $gameAccounts = GameAccount::where('game', 'genshin_impact')
            ->orderBy('id', 'asc')
            ->get();

        $accountId = $request->get('account_id', session('active_game_account_id'));
        $activeAccount = $gameAccounts->firstWhere('id', $accountId) ?? $gameAccounts->first();

        if ($activeAccount) {
            session(['active_game_account_id' => $activeAccount->id]);
        }

        $families = Family::orderBy('name', 'asc')->get();

        $search = $request->get('search');
        $familyId = $request->get('family_id');
        $onlyOwned = $request->boolean('only_owned', false);

        // Ambil inventory materials untuk akun ini
        $invMap = [];
        if ($activeAccount) {
            $invRecords = InventoryMaterial::where('game_account_id', $activeAccount->id)->get();
            foreach ($invRecords as $inv) {
                $invMap[$inv->material_id] = $inv->amount;
            }
        }

        // Query Master Material
        $materialQuery = Material::query()->with('family');

        if (!empty($search)) {
            $materialQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('materialtype', 'like', "%{$search}%");
            });
        }

        if (!empty($familyId)) {
            $materialQuery->where('familie_id', $familyId);
        }

        if ($onlyOwned && $activeAccount) {
            $ownedMaterialIds = array_keys(array_filter($invMap, fn($amt) => $amt > 0));
            $materialQuery->whereIn('id', $ownedMaterialIds);
        }

        $materials = $materialQuery->orderBy('familie_id', 'asc')
            ->orderBy('rarity', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(36)
            ->withQueryString();

        return view('inventory.materials', [
            'title'         => 'Inventori Material',
            'gameAccounts'  => $gameAccounts,
            'activeAccount' => $activeAccount,
            'families'      => $families,
            'materials'     => $materials,
            'invMap'        => $invMap,
            'search'        => $search,
            'familyId'      => $familyId,
            'onlyOwned'     => $onlyOwned,
        ]);
    }

    /**
     * Update jumlah stok material akun tertentu (AJAX / Form)
     */
    public function updateAmount(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'material_id'     => 'required|exists:materials,id',
            'amount'          => 'required|integer|min:0',
        ]);

        $inv = InventoryMaterial::updateOrCreate(
            [
                'game_account_id' => $request->game_account_id,
                'material_id'     => $request->material_id,
            ],
            [
                'amount'          => max(0, (int) $request->amount),
            ]
        );

        // Sinkronkan juga ke master material amount untuk backward compatibility jika diperlukan
        $mat = Material::find($request->material_id);
        if ($mat) {
            $mat->update(['amount' => $inv->amount]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'material_id' => $inv->material_id,
                'amount' => $inv->amount,
            ]);
        }

        return back()->with('success', 'Jumlah material berhasil diperbarui.');
    }

    /**
     * Penyesuaian cepat +/- amount via AJAX
     */
    public function quickAdjust(Request $request): JsonResponse
    {
        $request->validate([
            'game_account_id' => 'required|exists:game_accounts,id',
            'material_id'     => 'required|exists:materials,id',
            'delta'           => 'required|integer',
        ]);

        $inv = InventoryMaterial::firstOrNew([
            'game_account_id' => $request->game_account_id,
            'material_id'     => $request->material_id,
        ], [
            'amount' => 0,
        ]);

        $newAmount = max(0, (int) $inv->amount + (int) $request->delta);
        $inv->amount = $newAmount;
        $inv->save();

        // Sync ke master material
        $mat = Material::find($request->material_id);
        if ($mat) {
            $mat->update(['amount' => $newAmount]);
        }

        return response()->json([
            'success' => true,
            'material_id' => $inv->material_id,
            'amount' => $inv->amount,
        ]);
    }
}
