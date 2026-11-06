<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GameAccount;
use App\Models\InventoryCharacter;
use App\Models\PartyBenchmark;
use App\Models\SavedParty;
use App\Services\PartyBenchmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartyAnalyzerController extends Controller
{
    protected PartyBenchmarkService $benchmarkService;

    public function __construct(PartyBenchmarkService $benchmarkService)
    {
        $this->benchmarkService = $benchmarkService;
    }

    /**
     * Halaman utama Party Analyzer & Team Builder
     */
    public function index(Request $request): View
    {
        $gameAccounts = GameAccount::withCount('inventoryCharacters')->get();

        // Tentukan akun game yang dipilih (opsional)
        $selectedAccountId = $request->input('game_account_id');
        if ($selectedAccountId === 'all') {
            $selectedAccountId = null;
        } elseif ($selectedAccountId !== null) {
            $selectedAccountId = (int)$selectedAccountId;
        }

        $characters = $this->loadCharactersForAccount($selectedAccountId);

        // Ambil saved parties dengan benchmarks
        $savedParties = SavedParty::with(['gameAccount', 'benchmarks'])->latest()->get();

        // Eager-cache karakter untuk saved parties
        $this->cacheCharactersForSavedParties($savedParties);

        $formattedSavedParties = $savedParties->map(fn($p) => $this->formatPartyResource($p))->values();

        return view('party.index', [
            'gameAccounts'          => $gameAccounts,
            'selectedAccountId'     => $selectedAccountId,
            'characters'            => $characters,
            'savedParties'          => $savedParties,
            'formattedSavedParties' => $formattedSavedParties,
        ]);
    }

    /**
     * Endpoint API JSON untuk mengambil daftar karakter berdasarkan akun
     */
    public function charactersAjax(Request $request): JsonResponse
    {
        $accountId = $request->input('game_account_id');
        $accountId = ($accountId === 'all' || empty($accountId)) ? null : (int)$accountId;

        $characters = $this->loadCharactersForAccount($accountId);

        return response()->json([
            'success'    => true,
            'characters' => $characters,
        ]);
    }

    /**
     * Simpan party baru
     */
    public function saveParty(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'character_ids'   => 'required|array|min:1|max:4',
            'character_ids.*' => 'required|integer|exists:characters,id',
            'game_account_id' => 'nullable|exists:game_accounts,id',
            'notes'           => 'nullable|string|max:500',
        ]);

        $characterIds = array_values(array_map('intval', $validated['character_ids']));

        $savedParty = SavedParty::create([
            'name'            => $validated['name'],
            'character_ids'   => $characterIds,
            'game_account_id' => $validated['game_account_id'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'synergy_score'   => 0,
            'synergy_tier'    => 'A',
        ]);

        // Load cached characters
        $chars = Character::whereIn('id', $characterIds)->get();
        $savedParty->setRelation('characters_cached', $chars);
        $savedParty->load('benchmarks');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Party "' . $savedParty->name . '" berhasil disimpan!',
                'party'   => $this->formatPartyResource($savedParty),
            ]);
        }

        return redirect()->route('party.index')->with('success', 'Party berhasil disimpan!');
    }

    /**
     * Perbarui party yang ada
     */
    public function updateParty(Request $request, SavedParty $savedParty)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100',
            'character_ids'   => 'required|array|min:1|max:4',
            'character_ids.*' => 'required|integer|exists:characters,id',
            'game_account_id' => 'nullable|exists:game_accounts,id',
            'notes'           => 'nullable|string|max:500',
        ]);

        $characterIds = array_values(array_map('intval', $validated['character_ids']));

        $savedParty->update([
            'name'            => $validated['name'],
            'character_ids'   => $characterIds,
            'game_account_id' => $validated['game_account_id'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        // Load cached characters
        $chars = Character::whereIn('id', $characterIds)->get();
        $savedParty->setRelation('characters_cached', $chars);
        $savedParty->load('benchmarks');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Party "' . $savedParty->name . '" berhasil diperbarui!',
                'party'   => $this->formatPartyResource($savedParty),
            ]);
        }

        return redirect()->route('party.index')->with('success', 'Party berhasil diperbarui!');
    }

    /**
     * Hapus party yang tersimpan
     */
    public function deleteParty(Request $request, SavedParty $savedParty)
    {
        $partyName = $savedParty->name;
        $savedParty->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Party "' . $partyName . '" berhasil dihapus!',
            ]);
        }

        return redirect()->route('party.index')->with('success', 'Party berhasil dihapus.');
    }

    /**
     * API JSON untuk list parties
     */
    public function listAjax(Request $request): JsonResponse
    {
        $savedParties = SavedParty::with(['gameAccount', 'benchmarks'])->latest()->get();
        $this->cacheCharactersForSavedParties($savedParties);

        $formatted = $savedParties->map(fn($p) => $this->formatPartyResource($p));

        return response()->json([
            'success' => true,
            'parties' => $formatted,
        ]);
    }

    /**
     * Endpoint API JSON untuk mengekstrak screenshot hasil pengujian via Gemini Vision API
     */
    public function scanBenchmarkAjax(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:12288', // Maksimal 12MB
        ]);

        try {
            $file = $request->file('image');
            $extractedData = $this->benchmarkService->extractFromScreenshot($file);
            $evaluation = $this->benchmarkService->evaluateBenchmark($extractedData);

            // Simpan screenshot jika filesystem mengizinkan (aman di serverless/Vercel read-only filesystem)
            $path = null;
            try {
                $path = $file->store('benchmarks', 'public');
            } catch (\Throwable $storageError) {
                // Di lingkungan serverless (seperti Vercel / Lambda), filesystem bersifat read-only.
                // Ekstraksi Gemini tetap berhasil tanpa menyimpan file fisik ke disk.
                $path = null;
            }

            return response()->json([
                'success'         => true,
                'message'         => 'Ekstraksi screenshot berhasil!',
                'data'            => $extractedData,
                'evaluation'      => $evaluation,
                'screenshot_path' => $path,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses gambar: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Simpan data pengujian benchmark baru untuk suatu party
     */
    public function storeBenchmark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'saved_party_id'             => 'required|exists:saved_parties,id',
            'game_account_id'            => 'nullable|exists:game_accounts,id',
            'title'                      => 'nullable|string|max:150',
            'preset_name'                => 'nullable|string|max:100',
            'dps'                        => 'required|numeric|min:0',
            'total_time'                 => 'required|numeric|min:0',
            'total_damage'               => 'required|numeric|min:0',
            'max_hit'                    => 'required|numeric|min:0',
            'target_dummy'               => 'nullable|array',
            'modifiers'                  => 'nullable|string',
            'character_dmg_distribution' => 'nullable|array',
            'elemental_dmg_distribution' => 'nullable|array',
            'rotations'                  => 'nullable|array',
            'character_stats'            => 'nullable|array',
            'stage_guid'                 => 'nullable|string|max:50',
            'uid'                        => 'nullable|string|max:30',
            'screenshot_path'            => 'nullable|string',
        ]);

        $evaluation = $this->benchmarkService->evaluateBenchmark($validated);
        $validated['evaluation'] = $evaluation;
        if (empty($validated['title'])) {
            $validated['title'] = ($validated['preset_name'] ?? 'DPS Benchmark') . ' (' . now()->format('d/m/Y') . ')';
        }

        $benchmark = PartyBenchmark::create($validated);
        $party = SavedParty::with(['gameAccount', 'benchmarks'])->find($validated['saved_party_id']);
        $this->cacheCharactersForSavedParties(collect([$party]));

        return response()->json([
            'success'   => true,
            'message'   => 'Hasil pengujian berhasil disimpan ke party!',
            'benchmark' => $this->formatBenchmarkResource($benchmark),
            'party'     => $this->formatPartyResource($party),
        ]);
    }

    /**
     * Ambil detail hasil pengujian beserta seluruh riwayat pengujian party
     */
    public function showBenchmark(PartyBenchmark $partyBenchmark): JsonResponse
    {
        $partyBenchmark->load('savedParty.benchmarks');
        $allBenchmarks = $partyBenchmark->savedParty?->benchmarks()->latest()->get() ?? collect([$partyBenchmark]);

        return response()->json([
            'success'   => true,
            'benchmark' => $this->formatBenchmarkResource($partyBenchmark),
            'history'   => $allBenchmarks->map(function ($b) {
                return [
                    'id'               => $b->id,
                    'title'            => $b->title,
                    'preset_name'      => $b->preset_name,
                    'tier'             => $b->tier,
                    'tier_color'       => $b->tier_color,
                    'dps'              => $b->dps,
                    'formatted_dps'    => number_format($b->dps, 0, ',', '.'),
                    'total_damage'     => $b->total_damage,
                    'formatted_damage' => number_format($b->total_damage, 0, ',', '.'),
                    'created_at'       => $b->created_at?->format('d M Y, H:i') ?? '-',
                ];
            })->values(),
        ]);
    }

    /**
     * Hapus data pengujian benchmark
     */
    public function deleteBenchmark(PartyBenchmark $partyBenchmark): JsonResponse
    {
        $partyId = $partyBenchmark->saved_party_id;
        $partyBenchmark->delete();

        $party = SavedParty::with(['gameAccount', 'benchmarks'])->find($partyId);
        if ($party) {
            $this->cacheCharactersForSavedParties(collect([$party]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengujian berhasil dihapus.',
            'party'   => $party ? $this->formatPartyResource($party) : null,
        ]);
    }

    /**
     * Ekspor prompt markdown siap pakai untuk Gemini AI beserta riwayat pengujian
     */
    public function exportGeminiPrompt(PartyBenchmark $partyBenchmark): JsonResponse
    {
        $partyBenchmark->load('savedParty.benchmarks');
        if ($partyBenchmark->savedParty) {
            $this->cacheCharactersForSavedParties(collect([$partyBenchmark->savedParty]));
        }

        $allBenchmarks = $partyBenchmark->savedParty?->benchmarks()->latest()->get() ?? collect([$partyBenchmark]);
        $prompt = $this->benchmarkService->generateGeminiPrompt($partyBenchmark);

        return response()->json([
            'success'    => true,
            'title'      => "Prompt Gemini AI - " . ($partyBenchmark->savedParty?->name ?? 'Party Benchmark'),
            'party_name' => $partyBenchmark->savedParty?->name ?? 'Party Benchmark',
            'prompt'     => $prompt,
            'benchmark'  => [
                'id'            => $partyBenchmark->id,
                'title'         => $partyBenchmark->title,
                'preset_name'   => $partyBenchmark->preset_name,
                'tier'          => $partyBenchmark->tier,
                'tier_label'    => $partyBenchmark->tier_label,
                'tier_color'    => $partyBenchmark->tier_color,
                'dps'           => $partyBenchmark->dps,
                'formatted_dps' => number_format($partyBenchmark->dps, 0, ',', '.'),
                'created_at'    => $partyBenchmark->created_at?->format('d M Y, H:i') ?? '-',
            ],
            'history'    => $allBenchmarks->map(function ($b) {
                return [
                    'id'               => $b->id,
                    'title'            => $b->title,
                    'preset_name'      => $b->preset_name,
                    'tier'             => $b->tier,
                    'tier_color'       => $b->tier_color,
                    'dps'              => $b->dps,
                    'formatted_dps'    => number_format($b->dps, 0, ',', '.'),
                    'total_damage'     => $b->total_damage,
                    'formatted_damage' => number_format($b->total_damage, 0, ',', '.'),
                    'created_at'       => $b->created_at?->format('d M Y, H:i') ?? '-',
                ];
            })->values(),
        ]);
    }

    /**
     * Tanya-Jawab Interaktif dengan Asisten Theorycrafter Gemini AI
     */
    public function chatBenchmark(Request $request, PartyBenchmark $partyBenchmark): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
            'model'   => 'nullable|string|in:flash,pro',
        ]);

        try {
            $partyBenchmark->load('savedParty');
            if ($partyBenchmark->savedParty) {
                $this->cacheCharactersForSavedParties(collect([$partyBenchmark->savedParty]));
            }

            $result = $this->benchmarkService->chatWithTheorycrafter(
                $partyBenchmark,
                $validated['message'],
                $validated['history'] ?? [],
                $validated['model'] ?? 'flash'
            );

            return response()->json([
                'success'       => true,
                'reply'         => $result['reply'],
                'model_used'    => $result['model_used'],
                'model_choice'  => $result['model_choice'],
                'fallback_used' => $result['fallback_used'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Komparasi 2 Hasil Pengujian Benchmark (Matematika Instan & Analisis Mendalam Gemini AI)
     */
    public function compareBenchmarks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'benchmark_a_id' => 'required|integer|exists:party_benchmarks,id',
            'benchmark_b_id' => 'required|integer|exists:party_benchmarks,id',
            'with_ai'        => 'nullable|boolean',
            'model'          => 'nullable|string|in:flash,pro',
        ]);

        if ((int)$validated['benchmark_a_id'] === (int)$validated['benchmark_b_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih 2 hasil pengujian yang berbeda untuk dibandingkan.',
            ], 422);
        }

        try {
            $benchA = PartyBenchmark::with('savedParty.benchmarks')->findOrFail($validated['benchmark_a_id']);
            $benchB = PartyBenchmark::with('savedParty')->findOrFail($validated['benchmark_b_id']);

            $partiesToCache = collect([$benchA->savedParty, $benchB->savedParty])->filter();
            if ($partiesToCache->isNotEmpty()) {
                $this->cacheCharactersForSavedParties($partiesToCache);
            }

            $metrics = $this->benchmarkService->calculateComparisonMetrics($benchA, $benchB);
            $allBenchmarks = $benchA->savedParty?->benchmarks()->latest()->get() ?? collect([$benchB, $benchA]);

            $responsePayload = [
                'success'     => true,
                'party_name'  => $benchA->savedParty?->name ?? 'Party Benchmark',
                'benchmark_a' => $this->formatBenchmarkResource($benchA),
                'benchmark_b' => $this->formatBenchmarkResource($benchB),
                'metrics'     => $metrics,
                'history'     => $allBenchmarks->map(function ($b) {
                    return [
                        'id'               => $b->id,
                        'title'            => $b->title,
                        'preset_name'      => $b->preset_name,
                        'tier'             => $b->tier,
                        'tier_color'       => $b->tier_color,
                        'dps'              => $b->dps,
                        'formatted_dps'    => number_format($b->dps, 0, ',', '.'),
                        'total_damage'     => $b->total_damage,
                        'formatted_damage' => number_format($b->total_damage, 0, ',', '.'),
                        'created_at'       => $b->created_at?->format('d M Y, H:i') ?? '-',
                    ];
                })->values(),
            ];

            if (!empty($validated['with_ai'])) {
                $aiResult = $this->benchmarkService->compareBenchmarksWithAi(
                    $benchA,
                    $benchB,
                    $validated['model'] ?? 'flash'
                );
                $responsePayload['ai_analysis']   = $aiResult['analysis'];
                $responsePayload['model_used']    = $aiResult['model_used'];
                $responsePayload['model_choice']  = $aiResult['model_choice'];
                $responsePayload['fallback_used'] = $aiResult['fallback_used'];
            }

            return response()->json($responsePayload);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download dokumen Markdown hasil uji untuk Gemini / NotebookLM
     */
    public function downloadBenchmarkMarkdown(PartyBenchmark $partyBenchmark)
    {
        $partyBenchmark->load('savedParty');
        $md = $this->benchmarkService->generateGeminiPrompt($partyBenchmark);
        $fileName = 'gemini_party_analysis_' . ($partyBenchmark->savedParty?->slug ?? $partyBenchmark->id) . '_' . now()->format('Ymd_His') . '.md';

        return response($md, 200, [
            'Content-Type'        => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Download dokumen JSON hasil uji lengkap
     */
    public function downloadBenchmarkJson(PartyBenchmark $partyBenchmark)
    {
        $partyBenchmark->load('savedParty');
        $data = $this->formatBenchmarkResource($partyBenchmark);
        $fileName = 'party_benchmark_' . $partyBenchmark->id . '_' . now()->format('Ymd_His') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Helper load characters (dengan info inventory jika akun dipilih)
     */
    protected function loadCharactersForAccount(?int $accountId)
    {
        $allCharacters = Character::where('is_active', true)
            ->orderBy('rarity', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        if ($accountId) {
            $inventory = InventoryCharacter::where('game_account_id', $accountId)
                ->get()
                ->keyBy('character_id');

            return $allCharacters->map(function ($char) use ($inventory) {
                $inv = $inventory->get($char->id);
                return [
                    'id'            => $char->id,
                    'name'          => $char->name,
                    'slug'          => $char->slug,
                    'element'       => $char->element,
                    'element_color' => $char->element_color,
                    'weapon_type'   => $char->weapon_type,
                    'rarity'        => $char->rarity,
                    'icon_url'      => $char->icon_url,
                    'is_owned'      => $inv !== null,
                    'level'         => $inv?->level,
                    'constellation' => $inv?->constellation,
                ];
            });
        }

        return $allCharacters->map(function ($char) {
            return [
                'id'            => $char->id,
                'name'          => $char->name,
                'slug'          => $char->slug,
                'element'       => $char->element,
                'element_color' => $char->element_color,
                'weapon_type'   => $char->weapon_type,
                'rarity'        => $char->rarity,
                'icon_url'      => $char->icon_url,
                'is_owned'      => true,
                'level'         => null,
                'constellation' => null,
            ];
        });
    }

    /**
     * Cache model character pada setiap saved party untuk hindari N+1 query
     */
    protected function cacheCharactersForSavedParties($savedParties): void
    {
        if ($savedParties->isEmpty()) {
            return;
        }

        $allCharIds = $savedParties->pluck('character_ids')->flatten()->filter()->unique()->values();
        if ($allCharIds->isNotEmpty()) {
            $charsMap = Character::whereIn('id', $allCharIds)->get()->keyBy('id');
            $savedParties->each(function ($sp) use ($charsMap) {
                $chars = collect($sp->character_ids)->map(fn ($id) => $charsMap->get($id))->filter()->values();
                $sp->setRelation('characters_cached', $chars);
            });
        }
    }

    /**
     * Format saved party ke bentuk array yang kaya untuk respons JSON
     */
    protected function formatPartyResource(SavedParty $party): array
    {
        $characters = $party->characters;
        $benchmarks = $party->benchmarks ?? collect();
        $latestBenchmark = $benchmarks->first();

        return [
            'id'               => $party->id,
            'name'             => $party->name,
            'notes'            => $party->notes,
            'game_account_id'  => $party->game_account_id,
            'account_name'     => $party->gameAccount?->nickname,
            'character_ids'    => $party->character_ids,
            'characters'       => $characters->map(function ($c) {
                return [
                    'id'            => $c->id,
                    'name'          => $c->name,
                    'element'       => $c->element,
                    'element_color' => $c->element_color,
                    'weapon_type'   => $c->weapon_type,
                    'rarity'        => $c->rarity,
                    'icon_url'      => $c->icon_url,
                ];
            })->values(),
            'resonances'       => $party->elemental_resonances,
            'benchmarks_count' => $benchmarks->count(),
            'latest_benchmark' => $latestBenchmark ? $this->formatBenchmarkResource($latestBenchmark) : null,
            'benchmarks'       => $benchmarks->map(fn($b) => $this->formatBenchmarkResource($b))->values(),
            'created_at'       => $party->created_at?->diffForHumans() ?? 'Baru saja',
            'updated_at'       => $party->updated_at?->diffForHumans() ?? 'Baru saja',
        ];
    }

    /**
     * Format benchmark object ke array terstruktur
     */
    protected function formatBenchmarkResource(PartyBenchmark $b): array
    {
        return [
            'id'                         => $b->id,
            'saved_party_id'             => $b->saved_party_id,
            'title'                      => $b->title,
            'preset_name'                => $b->preset_name,
            'dps'                        => $b->dps,
            'formatted_dps'              => number_format($b->dps, 0, ',', '.'),
            'total_time'                 => $b->total_time,
            'total_damage'               => $b->total_damage,
            'formatted_damage'           => number_format($b->total_damage, 0, ',', '.'),
            'max_hit'                    => $b->max_hit,
            'formatted_max_hit'          => number_format($b->max_hit, 0, ',', '.'),
            'tier'                       => $b->tier,
            'tier_label'                 => $b->tier_label,
            'tier_color'                 => $b->tier_color,
            'target_dummy'               => $b->target_dummy,
            'modifiers'                  => $b->modifiers,
            'character_dmg_distribution' => $b->character_dmg_distribution,
            'elemental_dmg_distribution' => $b->elemental_dmg_distribution,
            'rotations'                  => $b->rotations,
            'character_stats'            => $b->character_stats,
            'stage_guid'                 => $b->stage_guid,
            'uid'                        => $b->uid,
            'evaluation'                 => $b->evaluation,
            'screenshot_url'             => $b->screenshot_path ? Storage::url($b->screenshot_path) : null,
            'created_at'                 => $b->created_at?->format('d M Y, H:i') ?? 'Baru saja',
        ];
    }
}
