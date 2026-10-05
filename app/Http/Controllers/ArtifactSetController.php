<?php

namespace App\Http\Controllers;

use App\Models\ArtifactSet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArtifactSetController extends Controller
{
    /**
     * Tampilkan katalog master artifact set
     */
    public function index(Request $request): View
    {
        $query = ArtifactSet::query();

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter Rarity
        if ($request->filled('rarity') && $request->input('rarity') !== 'all') {
            $query->where('max_rarity', (int)$request->input('rarity'));
        }

        $sets = $query->orderBy('name')->paginate(18)->withQueryString();

        $stats = [
            'total'  => ArtifactSet::count(),
            'star5'  => ArtifactSet::where('max_rarity', 5)->count(),
            'star4'  => ArtifactSet::where('max_rarity', 4)->count(),
        ];

        return view('artifact.index', [
            'title'   => 'Master Artifact Set',
            'sets'    => $sets,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'rarity']),
        ]);
    }

    /**
     * Simpan master artifact set baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'set_id'           => 'nullable|integer|unique:artifact_sets,set_id',
            'name'             => 'required|string|max:100|unique:artifact_sets,name',
            'max_rarity'       => 'required|integer|in:4,5',
            'two_piece_bonus'  => 'nullable|string|max:500',
            'four_piece_bonus' => 'nullable|string|max:1000',
            'icon_url'         => 'nullable|url|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        ArtifactSet::create($validated);

        return redirect()->route('artifact.index')
            ->with('success', "Set Artifact {$validated['name']} berhasil ditambahkan!");
    }

    /**
     * Update master artifact set
     */
    public function update(Request $request, ArtifactSet $artifact): RedirectResponse
    {
        $validated = $request->validate([
            'set_id'           => 'nullable|integer|unique:artifact_sets,set_id,' . $artifact->id,
            'name'             => 'required|string|max:100|unique:artifact_sets,name,' . $artifact->id,
            'max_rarity'       => 'required|integer|in:4,5',
            'two_piece_bonus'  => 'nullable|string|max:500',
            'four_piece_bonus' => 'nullable|string|max:1000',
            'icon_url'         => 'nullable|url|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $artifact->update($validated);

        return redirect()->route('artifact.index')
            ->with('success', "Set Artifact {$artifact->name} berhasil diperbarui!");
    }

    /**
     * Hapus master artifact set
     */
    public function destroy(ArtifactSet $artifact): RedirectResponse
    {
        $name = $artifact->name;
        $artifact->delete();

        return redirect()->route('artifact.index')
            ->with('success', "Set Artifact {$name} berhasil dihapus!");
    }

    /**
     * Sinkronisasi seluruh database master artifact set dari Project Amber atau Enka
     */
    public function syncAll(Request $request, \App\Services\EnkaNetworkService $enkaService)
    {
        $source = $request->input('source', 'amber');

        try {
            $result = $enkaService->syncMasterArtifactSets($source);

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return redirect()->route('artifact.index')->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', 'Gagal sinkronisasi master artifact set: ' . $e->getMessage());
        }
    }

    /**
     * API JSON untuk autocomplete/scan
     */
    public function apiList(): \Illuminate\Http\JsonResponse
    {
        $sets = ArtifactSet::select('id', 'set_id', 'name', 'slug', 'max_rarity', 'two_piece_bonus', 'four_piece_bonus', 'icon_url')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'total'  => $sets->count(),
            'data'   => $sets,
        ]);
    }
}
