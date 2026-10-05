<?php

namespace App\Http\Controllers;

use App\Models\Weapon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WeaponController extends Controller
{
    /**
     * Tampilkan daftar master data senjata
     */
    public function index(Request $request): View
    {
        $query = Weapon::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('rarity') && $request->input('rarity') !== 'all') {
            $query->where('rarity', (int)$request->input('rarity'));
        }

        $weapons = $query
            ->orderByDesc('rarity')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $stats = [
            'total' => Weapon::count(),
            'star5' => Weapon::where('rarity', 5)->count(),
            'star4' => Weapon::where('rarity', 4)->count(),
        ];

        return view('weapon.index', [
            'title'   => 'Master Senjata',
            'weapons' => $weapons,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'type', 'rarity']),
        ]);
    }

    /**
     * Simpan master senjata baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100|unique:weapons,name',
            'type'           => 'required|string|in:Sword,Claymore,Polearm,Bow,Catalyst',
            'rarity'         => 'required|integer|min:1|max:5',
            'base_atk'       => 'required|integer|min:1|max:1000',
            'sub_stat_type'  => 'nullable|string|max:50',
            'sub_stat_value' => 'nullable|string|max:20',
            'passive_name'   => 'nullable|string|max:100',
            'passive_desc'   => 'nullable|string',
            'icon_url'       => 'nullable|url|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Weapon::create($validated);

        return redirect()->route('weapon.index')
            ->with('success', "Senjata {$validated['name']} berhasil ditambahkan!");
    }

    /**
     * Update master data senjata
     */
    public function update(Request $request, Weapon $weapon): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100|unique:weapons,name,' . $weapon->id,
            'type'           => 'required|string|in:Sword,Claymore,Polearm,Bow,Catalyst',
            'rarity'         => 'required|integer|min:1|max:5',
            'base_atk'       => 'required|integer|min:1|max:1000',
            'sub_stat_type'  => 'nullable|string|max:50',
            'sub_stat_value' => 'nullable|string|max:20',
            'passive_name'   => 'nullable|string|max:100',
            'passive_desc'   => 'nullable|string',
            'icon_url'       => 'nullable|url|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $weapon->update($validated);

        return redirect()->route('weapon.index')
            ->with('success', "Senjata {$weapon->name} berhasil diperbarui!");
    }

    /**
     * Hapus data senjata
     */
    public function destroy(Weapon $weapon): RedirectResponse
    {
        $name = $weapon->name;
        $weapon->delete();

        return redirect()->route('weapon.index')
            ->with('success', "Senjata {$name} berhasil dihapus!");
    }

    /**
     * API JSON untuk autocomplete/scan
     */
    public function apiList(): JsonResponse
    {
        $weapons = Weapon::select('id', 'name', 'slug', 'type', 'rarity', 'base_atk', 'sub_stat_type', 'sub_stat_value', 'icon_url')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'total'  => $weapons->count(),
            'data'   => $weapons,
        ]);
    }

    /**
     * Sinkronisasi seluruh database senjata dari Enka / GitHub Store
     */
    public function syncAll(Request $request, \App\Services\EnkaNetworkService $enkaService)
    {
        try {
            $result = $enkaService->syncMasterWeapons();

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return redirect()->route('weapon.index')->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', 'Gagal sinkronisasi master senjata: ' . $e->getMessage());
        }
    }
}
