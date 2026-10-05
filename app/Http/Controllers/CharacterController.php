<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CharacterController extends Controller
{
    /**
     * Tampilkan daftar master data karakter
     */
    public function index(Request $request): View
    {
        $query = Character::query();

        // Filter pencarian nama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Filter Elemen
        if ($request->filled('element') && $request->input('element') !== 'all') {
            $query->where('element', $request->input('element'));
        }

        // Filter Rarity
        if ($request->filled('rarity') && $request->input('rarity') !== 'all') {
            $query->where('rarity', (int)$request->input('rarity'));
        }

        // Filter Tipe Senjata
        if ($request->filled('weapon') && $request->input('weapon') !== 'all') {
            $query->where('weapon_type', $request->input('weapon'));
        }

        // Filter Region
        if ($request->filled('region') && $request->input('region') !== 'all') {
            $query->where('region', $request->input('region'));
        }

        $characters = $query
            ->orderByDesc('rarity')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $stats = [
            'total' => Character::count(),
            'star5' => Character::where('rarity', 5)->count(),
            'star4' => Character::where('rarity', 4)->count(),
        ];

        return view('character.index', [
            'title' => 'Master Karakter',
            'characters' => $characters,
            'stats' => $stats,
            'filters' => $request->only(['search', 'element', 'rarity', 'weapon', 'region']),
        ]);
    }

    /**
     * Simpan karakter baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:characters,name',
            'element'     => 'required|string|in:Pyro,Hydro,Anemo,Electro,Dendro,Cryo,Geo',
            'weapon_type' => 'required|string|in:Sword,Claymore,Polearm,Bow,Catalyst',
            'rarity'      => 'required|integer|in:4,5',
            'region'      => 'nullable|string|max:50',
            'icon_url'    => 'nullable|url|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Character::create($validated);

        return redirect()->route('character.index')
            ->with('success', "Karakter {$validated['name']} berhasil ditambahkan!");
    }

    /**
     * Update data karakter
     */
    public function update(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:characters,name,' . $character->id,
            'element'     => 'required|string|in:Pyro,Hydro,Anemo,Electro,Dendro,Cryo,Geo',
            'weapon_type' => 'required|string|in:Sword,Claymore,Polearm,Bow,Catalyst',
            'rarity'      => 'required|integer|in:4,5',
            'region'      => 'nullable|string|max:50',
            'icon_url'    => 'nullable|url|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $character->update($validated);

        return redirect()->route('character.index')
            ->with('success', "Karakter {$character->name} berhasil diperbarui!");
    }

    /**
     * Hapus karakter
     */
    public function destroy(Character $character): RedirectResponse
    {
        $name = $character->name;
        $character->delete();

        return redirect()->route('character.index')
            ->with('success', "Karakter {$name} berhasil dihapus!");
    }

    /**
     * Endpoint API JSON untuk integrasi OCR / Scanning & Auto-complete
     */
    public function apiList(): JsonResponse
    {
        $characters = Character::select('id', 'name', 'slug', 'element', 'weapon_type', 'rarity', 'region', 'icon_url')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'total'  => $characters->count(),
            'data'   => $characters,
        ]);
    }

    /**
     * Sinkronisasi seluruh database karakter dari Project Amber atau Enka Network
     */
    public function syncAll(Request $request, \App\Services\EnkaNetworkService $enkaService)
    {
        $source = $request->input('source', 'amber');

        try {
            $result = $enkaService->syncMasterCharacters($source);

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return redirect()->route('character.index')->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', 'Gagal sinkronisasi master karakter: ' . $e->getMessage());
        }
    }
}
