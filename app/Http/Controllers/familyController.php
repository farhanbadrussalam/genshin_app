<?php

namespace App\Http\Controllers;

use App\Models\family;
use App\Services\MaterialSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class familyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $data['dataFamily'] = family::withCount('material')->orderBy('name', 'ASC')->get();
        $data['title'] = 'Family Material';
        return Response(view('family.index', $data));
    }

    /**
     * Sinkronisasi data master family & material dari API eksternal
     */
    public function syncAll(Request $request, MaterialSyncService $syncService)
    {
        try {
            $result = $syncService->syncAllMaterials();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('family.index')->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gagal mensinkronkan family material: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return $this->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nameFamily' => 'required|string|max:255'
        ]);

        family::create([
            'name' => $request->nameFamily
        ]);

        return redirect()->route('family.index')->with('success', 'Family berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(family $family): Response
    {
        return $this->index();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(family $family): Response
    {
        return $this->index();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, family $family): RedirectResponse
    {
        $request->validate([
            'nameFamily' => 'required|string|max:255'
        ]);

        $family->update([
            'name' => $request->nameFamily
        ]);

        return redirect()->route('family.index')->with('success', 'Family berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(family $family): RedirectResponse
    {
        $family->delete();
        return redirect()->route('family.index')->with('success', 'Family berhasil dihapus!');
    }
}